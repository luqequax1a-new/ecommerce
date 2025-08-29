<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Enterprise-Level Product Variant Service
 * 
 * Features:
 * - Canonical representation with attributes_hash
 * - Deterministic SKU generation with collision handling
 * - N+1 query prevention with caching
 * - Default variant uniqueness
 * - Combination limits and validation
 * - Turkish character normalization
 */
class ProductVariantService
{
    // Cache for attribute/value lookups to prevent N+1
    private array $attributeCache = [];
    private array $valueCache = [];
    
    // Limits for enterprise use
    private const MAX_COMBINATIONS = 400;
    private const SKU_MAX_LENGTH = 100;
    
    /**
     * Generate variant combinations for a product with canonical representation
     */
    public function generateVariantCombinations(Product $product, array $selectedAttributes): array
    {
        if (empty($selectedAttributes)) {
            return [];
        }

        // Pre-load attributes and values to prevent N+1
        $this->preloadAttributeData($selectedAttributes);

        // Get attribute values for each selected attribute
        $attributeValues = [];
        foreach ($selectedAttributes as $attributeId) {
            $attribute = $this->getCachedAttribute($attributeId);
            if ($attribute && !empty($attribute['values'])) {
                $attributeValues[$attributeId] = $attribute['values'];
            }
        }

        if (empty($attributeValues)) {
            return [];
        }

        // Generate all possible combinations
        $combinations = $this->generateCombinations($attributeValues);
        
        // Check combination limit
        if (count($combinations) > self::MAX_COMBINATIONS) {
            throw new \InvalidArgumentException(
                sprintf('Kombinasyon sayısı limitini aşıyor (maksimum %d, oluşturulan %d)', 
                       self::MAX_COMBINATIONS, count($combinations))
            );
        }
        
        // Format combinations for display with canonical representation
        $formattedCombinations = [];
        foreach ($combinations as $index => $combination) {
            $attributesHash = $this->generateAttributesHash($combination);
            $sku = $this->generateDeterministicSku($product, $combination);
            
            $formattedCombinations[] = [
                'combination_id' => $index,
                'attributes' => $combination,
                'attributes_hash' => $attributesHash,
                'sku' => $sku,
                'base_sku' => $this->getBaseSku($product),
                'price' => $product->price ?? 0,
                'stock_quantity' => 0,
                'is_default' => $index === 0,
                'is_active' => true,
                'sort_order' => $index,
                'attribute_display' => $this->formatAttributeDisplay($combination)
            ];
        }

        return $formattedCombinations;
    }

    /**
     * Create variants from combinations with enterprise features
     */
    public function createVariantsFromCombinations(Product $product, array $combinations): array
    {
        $created = [];
        $errors = [];

        DB::transaction(function () use ($product, $combinations, &$created, &$errors) {
            foreach ($combinations as $combinationData) {
                try {
                    // Generate canonical hash
                    $attributesHash = $this->generateAttributesHash($combinationData['attributes']);
                    
                    // Check for duplicate combination using hash
                    if (ProductVariant::where('product_id', $product->id)
                                     ->where('attributes_hash', $attributesHash)
                                     ->exists()) {
                        $errors[] = "Kombinasyon zaten mevcut: {$combinationData['attribute_display']}";
                        continue;
                    }

                    // Validate and generate SKU
                    $sku = $this->generateDeterministicSku($product, $combinationData['attributes']);
                    $sku = $this->handleSkuCollision($sku);
                    
                    // Prepare variant data with enterprise fields
                    $variantData = [
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'base_sku' => $this->getBaseSku($product),
                        'price' => $combinationData['price'] ?? $product->price ?? 0,
                        'stock_quantity' => $combinationData['stock_quantity'] ?? 0,
                        'attributes' => $this->formatAttributesForStorage($combinationData['attributes']),
                        'attributes_hash' => $attributesHash,
                        'is_active' => $combinationData['is_active'] ?? true,
                        'is_default' => $combinationData['is_default'] ?? false,
                        'sort_order' => $combinationData['sort_order'] ?? 0,
                        'barcode' => $combinationData['barcode'] ?? null,
                        'ean' => $combinationData['ean'] ?? null,
                        'image_path' => $combinationData['image_path'] ?? null,
                    ];
                    
                    // Handle default variant uniqueness
                    if ($variantData['is_default']) {
                        $this->ensureDefaultUniqueness($product, null);
                    }

                    // Create variant
                    $variant = ProductVariant::create($variantData);
                    $created[] = $variant;
                    
                } catch (\Exception $e) {
                    $errors[] = "Varyant oluşturulurken hata: " . $e->getMessage();
                    Log::error('Variant creation error', [
                        'product_id' => $product->id,
                        'combination' => $combinationData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [
            'created' => $created,
            'errors' => $errors,
            'success_count' => count($created),
            'error_count' => count($errors)
        ];
    }

    /**
     * Update existing variants with new data
     */
    public function updateVariants(Product $product, array $variantUpdates): array
    {
        $updated = [];
        $errors = [];

        DB::transaction(function () use ($product, $variantUpdates, &$updated, &$errors) {
            foreach ($variantUpdates as $variantId => $updateData) {
                try {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->where('id', $variantId)
                        ->first();

                    if (!$variant) {
                        $errors[] = "Variant with ID {$variantId} not found";
                        continue;
                    }

                    // Validate SKU uniqueness (excluding current variant)
                    if (isset($updateData['sku']) && 
                        ProductVariant::where('sku', $updateData['sku'])
                                     ->where('id', '!=', $variantId)
                                     ->exists()) {
                        $errors[] = "SKU '{$updateData['sku']}' already exists";
                        continue;
                    }

                    $variant->update($updateData);
                    $updated[] = $variant;
                } catch (\Exception $e) {
                    $errors[] = "Error updating variant {$variantId}: " . $e->getMessage();
                    Log::error('Variant update error', [
                        'variant_id' => $variantId,
                        'update_data' => $updateData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [
            'updated' => $updated,
            'errors' => $errors,
            'success_count' => count($updated),
            'error_count' => count($errors)
        ];
    }

    /**
     * Delete variants by IDs
     */
    public function deleteVariants(Product $product, array $variantIds): array
    {
        $deleted = [];
        $errors = [];

        DB::transaction(function () use ($product, $variantIds, &$deleted, &$errors) {
            foreach ($variantIds as $variantId) {
                try {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->where('id', $variantId)
                        ->first();

                    if (!$variant) {
                        $errors[] = "Variant with ID {$variantId} not found";
                        continue;
                    }

                    // Check if variant has orders (prevent deletion if it does)
                    // This would be implemented when order system is ready
                    
                    $variant->delete();
                    $deleted[] = $variantId;
                } catch (\Exception $e) {
                    $errors[] = "Error deleting variant {$variantId}: " . $e->getMessage();
                    Log::error('Variant deletion error', [
                        'variant_id' => $variantId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [
            'deleted' => $deleted,
            'errors' => $errors,
            'success_count' => count($deleted),
            'error_count' => count($errors)
        ];
    }

    /**
     * Generate all possible combinations from attribute values
     */
    private function generateCombinations(array $attributeValues): array
    {
        $combinations = [];
        $keys = array_keys($attributeValues);
        
        if (empty($keys)) {
            return [];
        }

        // Start with first attribute
        $firstKey = $keys[0];
        foreach ($attributeValues[$firstKey] as $valueId => $value) {
            $combinations[] = [$firstKey => ['id' => $valueId, 'value' => $value]];
        }

        // Add other attributes
        for ($i = 1; $i < count($keys); $i++) {
            $newCombinations = [];
            $currentKey = $keys[$i];
            
            foreach ($combinations as $combination) {
                foreach ($attributeValues[$currentKey] as $valueId => $value) {
                    $newCombination = $combination;
                    $newCombination[$currentKey] = ['id' => $valueId, 'value' => $value];
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * Generate unique SKU for a variant
     */
    private function generateVariantSku(Product $product, array $combination, int $index): string
    {
        // Base SKU from product slug
        $baseSku = strtoupper(str_replace('-', '', $product->slug));
        
        // Add attribute abbreviations
        $attributeParts = [];
        foreach ($combination as $attributeId => $valueData) {
            $attribute = ProductAttribute::find($attributeId);
            if ($attribute) {
                $attrCode = strtoupper(substr($attribute->slug, 0, 2));
                $valueCode = strtoupper(substr(SlugService::generate($valueData['value']), 0, 3));
                $attributeParts[] = $attrCode . $valueCode;
            }
        }
        
        $sku = $baseSku . '-' . implode('-', $attributeParts);
        
        // Ensure uniqueness
        $originalSku = $sku;
        $counter = 1;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }
        
        return $sku;
    }

    /**
     * Format attribute display for UI
     */
    private function formatAttributeDisplay(array $combination): string
    {
        $parts = [];
        foreach ($combination as $attributeId => $valueData) {
            $attribute = ProductAttribute::find($attributeId);
            if ($attribute) {
                $parts[] = $attribute->name . ': ' . $valueData['value'];
            }
        }
        return implode(', ', $parts);
    }

    /**
     * Format attributes for database storage
     */
    private function formatAttributesForStorage(array $combination): array
    {
        $formatted = [];
        foreach ($combination as $attributeId => $valueData) {
            $attribute = ProductAttribute::find($attributeId);
            if ($attribute) {
                $formatted[$attribute->slug] = $valueData['value'];
            }
        }
        return $formatted;
    }

    /**
     * Get available attributes for variant creation
     */
    public function getAvailableAttributes(): Collection
    {
        return ProductAttribute::active()
            ->variation()
            ->with('activeValues')
            ->ordered()
            ->get()
            ->filter(function ($attribute) {
                return $attribute->activeValues->count() > 0;
            });
    }

    /**
     * Validate variant combination uniqueness within product
     */
    public function validateCombinationUniqueness(Product $product, array $combination, ?int $excludeVariantId = null): bool
    {
        $query = ProductVariant::where('product_id', $product->id);
        
        if ($excludeVariantId) {
            $query->where('id', '!=', $excludeVariantId);
        }
        
        foreach ($query->get() as $existingVariant) {
            if ($this->combinationsMatch($combination, $existingVariant->attributes)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if two combinations match
     */
    private function combinationsMatch(array $combination1, array $combination2): bool
    {
        if (count($combination1) !== count($combination2)) {
            return false;
        }
        
        foreach ($combination1 as $key => $value) {
            if (!isset($combination2[$key]) || $combination2[$key] !== $value) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get variant statistics for a product
     */
    public function getVariantStatistics(Product $product): array
    {
        $variants = $product->variants;
        
        return [
            'total_variants' => $variants->count(),
            'active_variants' => $variants->where('is_active', true)->count(),
            'default_variant' => $variants->where('is_default', true)->first(),
            'out_of_stock' => $variants->where('stock_quantity', '<=', 0)->count(),
            'total_stock_value' => $variants->sum(function ($variant) {
                return $variant->stock_quantity * $variant->price;
            }),
            'average_price' => $variants->avg('price'),
            'min_price' => $variants->min('price'),
            'max_price' => $variants->max('price'),
            'total_stock_quantity' => $variants->sum('stock_quantity'),
            'has_barcodes' => $variants->whereNotNull('barcode')->count(),
            'has_images' => $variants->whereNotNull('image_path')->count(),
        ];
    }

    // ================== ENTERPRISE-LEVEL METHODS ==================

    /**
     * Generate deterministic attributes hash for canonical representation
     */
    public function generateAttributesHash(array $attributes): string
    {
        // Sort attributes by key to ensure deterministic hash
        ksort($attributes);
        
        $hashParts = [];
        foreach ($attributes as $attrId => $valueData) {
            $valueId = is_array($valueData) ? $valueData['id'] : $valueData;
            $hashParts[] = $attrId . ':' . $valueId;
        }
        
        return implode('|', $hashParts);
    }

    /**
     * Generate deterministic SKU with Turkish character support
     */
    public function generateDeterministicSku(Product $product, array $attributes): string
    {
        $baseSku = $this->getBaseSku($product);
        
        // Sort attributes by key for deterministic generation
        ksort($attributes);
        
        $skuParts = [$baseSku];
        
        foreach ($attributes as $attrId => $valueData) {
            $attribute = $this->getCachedAttribute($attrId);
            $valueId = is_array($valueData) ? $valueData['id'] : $valueData;
            $value = is_array($valueData) ? $valueData['value'] : $this->getCachedAttributeValue($valueId);
            
            if ($attribute && $value) {
                // Generate attribute code (first 2 chars of slug)
                $attrCode = strtoupper(substr($this->normalizeTurkish($attribute['slug']), 0, 2));
                
                // Generate value code (first 3 chars of normalized value)
                $valueCode = strtoupper(substr($this->normalizeTurkish($value), 0, 3));
                
                $skuParts[] = $attrCode . $valueCode;
            }
        }
        
        $sku = implode('-', $skuParts);
        
        // Ensure SKU doesn't exceed maximum length
        if (strlen($sku) > self::SKU_MAX_LENGTH) {
            $sku = substr($sku, 0, self::SKU_MAX_LENGTH - 3) . '...';
        }
        
        return $sku;
    }

    /**
     * Handle SKU collision with deterministic suffixes
     */
    public function handleSkuCollision(string $baseSku): string
    {
        $sku = $baseSku;
        $counter = 1;
        
        while (ProductVariant::where('sku', $sku)->exists()) {
            $suffix = '-' . $counter;
            
            // Ensure we don't exceed max length
            if (strlen($baseSku . $suffix) > self::SKU_MAX_LENGTH) {
                $maxBaseLength = self::SKU_MAX_LENGTH - strlen($suffix);
                $sku = substr($baseSku, 0, $maxBaseLength) . $suffix;
            } else {
                $sku = $baseSku . $suffix;
            }
            
            $counter++;
            
            // Prevent infinite loops
            if ($counter > 999) {
                throw new \RuntimeException('SKU çakışması çözülemedi: ' . $baseSku);
            }
        }
        
        return $sku;
    }

    /**
     * Set default variant with transaction-safe uniqueness
     */
    public function setDefaultVariant(Product $product, int $variantId): bool
    {
        return DB::transaction(function () use ($product, $variantId) {
            // Verify variant exists and belongs to product
            $variant = ProductVariant::where('product_id', $product->id)
                                   ->where('id', $variantId)
                                   ->first();
            
            if (!$variant) {
                throw new \InvalidArgumentException('Varyant bulunamadı');
            }
            
            // Remove default from all other variants
            ProductVariant::where('product_id', $product->id)
                         ->where('id', '!=', $variantId)
                         ->update(['is_default' => false]);
            
            // Set this variant as default
            $variant->update(['is_default' => true]);
            
            return true;
        });
    }

    /**
     * Ensure default variant uniqueness within product
     */
    private function ensureDefaultUniqueness(Product $product, ?int $excludeVariantId = null): void
    {
        $query = ProductVariant::where('product_id', $product->id)
                              ->where('is_default', true);
        
        if ($excludeVariantId) {
            $query->where('id', '!=', $excludeVariantId);
        }
        
        $query->update(['is_default' => false]);
    }

    /**
     * Get base SKU from product with Turkish normalization
     */
    private function getBaseSku(Product $product): string
    {
        if (!empty($product->base_sku)) {
            return strtoupper($this->normalizeTurkish($product->base_sku));
        }
        
        // Generate from slug if no base_sku
        return strtoupper(str_replace('-', '', $this->normalizeTurkish($product->slug)));
    }

    /**
     * Normalize Turkish characters for SKU generation
     */
    private function normalizeTurkish(string $text): string
    {
        $turkish = ['Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', 'ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
        $english = ['C', 'G', 'I', 'O', 'S', 'U', 'c', 'g', 'i', 'o', 's', 'u'];
        
        $normalized = str_replace($turkish, $english, $text);
        
        // Remove non-alphanumeric characters except hyphens
        return preg_replace('/[^a-zA-Z0-9-]/', '', $normalized);
    }

    /**
     * Preload attribute and value data to prevent N+1 queries
     */
    private function preloadAttributeData(array $attributeIds): void
    {
        // Load attributes with their values in single query
        $attributes = ProductAttribute::with(['activeValues' => function ($query) {
            $query->orderBy('sort_order')->orderBy('value');
        }])
        ->whereIn('id', $attributeIds)
        ->get();
        
        foreach ($attributes as $attribute) {
            $this->attributeCache[$attribute->id] = [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'values' => $attribute->activeValues->mapWithKeys(function ($value) {
                    $this->valueCache[$value->id] = $value->value;
                    return [$value->id => $value->value];
                })->toArray()
            ];
        }
    }

    /**
     * Get cached attribute data
     */
    private function getCachedAttribute(int $attributeId): ?array
    {
        if (!isset($this->attributeCache[$attributeId])) {
            $attribute = ProductAttribute::with('activeValues')->find($attributeId);
            if ($attribute) {
                $this->attributeCache[$attributeId] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'values' => $attribute->activeValues->pluck('value', 'id')->toArray()
                ];
            }
        }
        
        return $this->attributeCache[$attributeId] ?? null;
    }

    /**
     * Get cached attribute value
     */
    private function getCachedAttributeValue(int $valueId): ?string
    {
        if (!isset($this->valueCache[$valueId])) {
            $value = ProductAttributeValue::find($valueId);
            if ($value) {
                $this->valueCache[$valueId] = $value->value;
            }
        }
        
        return $this->valueCache[$valueId] ?? null;
    }

    /**
     * Validate combination limits and business rules
     */
    public function validateCombinations(array $combinations): array
    {
        $errors = [];
        
        if (count($combinations) > self::MAX_COMBINATIONS) {
            $errors[] = sprintf(
                'Kombinasyon sayısı limitini aşıyor (maksimum %d, oluşturulan %d)',
                self::MAX_COMBINATIONS,
                count($combinations)
            );
        }
        
        $defaultCount = 0;
        foreach ($combinations as $combination) {
            if (!empty($combination['is_default'])) {
                $defaultCount++;
            }
        }
        
        if ($defaultCount > 1) {
            $errors[] = 'Birden fazla varsayılan varyant seçilemez';
        }
        
        return $errors;
    }

    /**
     * Update variants with enterprise validation
     */
    public function updateVariantsEnterprise(Product $product, array $variantUpdates): array
    {
        $updated = [];
        $errors = [];
        
        // Whitelist allowed fields for security
        $allowedFields = [
            'sku', 'price', 'stock_quantity', 'attributes', 'is_default', 
            'barcode', 'ean', 'image_path', 'is_active', 'sort_order'
        ];

        DB::transaction(function () use ($product, $variantUpdates, $allowedFields, &$updated, &$errors) {
            foreach ($variantUpdates as $variantId => $updateData) {
                try {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->where('id', $variantId)
                        ->first();

                    if (!$variant) {
                        $errors[] = "Varyant bulunamadı: {$variantId}";
                        continue;
                    }

                    // Filter allowed fields
                    $filteredData = array_intersect_key($updateData, array_flip($allowedFields));
                    
                    // Handle SKU changes with collision detection
                    if (isset($filteredData['sku'])) {
                        $newSku = $filteredData['sku'];
                        if (ProductVariant::where('sku', $newSku)
                                         ->where('id', '!=', $variantId)
                                         ->exists()) {
                            $errors[] = "SKU zaten mevcut: {$newSku}";
                            continue;
                        }
                    }
                    
                    // Handle default variant changes
                    if (isset($filteredData['is_default']) && $filteredData['is_default']) {
                        $this->ensureDefaultUniqueness($product, $variantId);
                    }
                    
                    // Update attributes hash if attributes changed
                    if (isset($filteredData['attributes'])) {
                        $filteredData['attributes_hash'] = $this->generateAttributesHashFromStorage($filteredData['attributes']);
                    }

                    $variant->update($filteredData);
                    $updated[] = $variant;
                    
                } catch (\Exception $e) {
                    $errors[] = "Varyant güncellenirken hata {$variantId}: " . $e->getMessage();
                    Log::error('Variant update error', [
                        'variant_id' => $variantId,
                        'update_data' => $updateData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [
            'updated' => $updated,
            'errors' => $errors,
            'success_count' => count($updated),
            'error_count' => count($errors)
        ];
    }

    /**
     * Generate attributes hash from stored attributes
     */
    private function generateAttributesHashFromStorage(array $storedAttributes): string
    {
        // Convert stored format back to hash format
        $hashParts = [];
        foreach ($storedAttributes as $attrSlug => $value) {
            // Find attribute ID by slug
            $attribute = ProductAttribute::where('slug', $attrSlug)->first();
            if ($attribute) {
                $attrValue = ProductAttributeValue::where('value', $value)->first();
                if ($attrValue) {
                    $hashParts[] = $attribute->id . ':' . $attrValue->id;
                }
            }
        }
        
        sort($hashParts); // Ensure deterministic order
        return implode('|', $hashParts);
    }
}