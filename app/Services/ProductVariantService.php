<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ProductVariantService
{
    /**
     * Generate variant combinations for a product
     */
    public function generateVariantCombinations(Product $product, array $selectedAttributes): array
    {
        if (empty($selectedAttributes)) {
            return [];
        }

        // Get attribute values for each selected attribute
        $attributeValues = [];
        foreach ($selectedAttributes as $attributeId) {
            $attribute = ProductAttribute::with('activeValues')->find($attributeId);
            if ($attribute && $attribute->activeValues->count() > 0) {
                $attributeValues[$attributeId] = $attribute->activeValues->pluck('value', 'id')->toArray();
            }
        }

        if (empty($attributeValues)) {
            return [];
        }

        // Generate all possible combinations
        $combinations = $this->generateCombinations($attributeValues);
        
        // Format combinations for display
        $formattedCombinations = [];
        foreach ($combinations as $index => $combination) {
            $sku = $this->generateVariantSku($product, $combination, $index);
            
            $formattedCombinations[] = [
                'combination_id' => $index,
                'attributes' => $combination,
                'sku' => $sku,
                'price' => $product->price ?? 0,
                'stock_quantity' => 0,
                'is_default' => $index === 0,
                'attribute_display' => $this->formatAttributeDisplay($combination)
            ];
        }

        return $formattedCombinations;
    }

    /**
     * Create variants from combinations
     */
    public function createVariantsFromCombinations(Product $product, array $combinations): array
    {
        $created = [];
        $errors = [];

        DB::transaction(function () use ($product, $combinations, &$created, &$errors) {
            foreach ($combinations as $combinationData) {
                try {
                    // Validate SKU uniqueness
                    if (ProductVariant::where('sku', $combinationData['sku'])->exists()) {
                        $errors[] = "SKU '{$combinationData['sku']}' already exists";
                        continue;
                    }

                    // Create variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $combinationData['sku'],
                        'price' => $combinationData['price'] ?? $product->price ?? 0,
                        'stock_quantity' => $combinationData['stock_quantity'] ?? 0,
                        'attributes' => $this->formatAttributesForStorage($combinationData['attributes'])
                    ]);

                    $created[] = $variant;
                } catch (\Exception $e) {
                    $errors[] = "Error creating variant: " . $e->getMessage();
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
            'active_variants' => $variants->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => $variants->where('stock_quantity', '<=', 0)->count(),
            'total_stock_value' => $variants->sum(function ($variant) {
                return $variant->stock_quantity * $variant->price;
            }),
            'average_price' => $variants->avg('price'),
            'min_price' => $variants->min('price'),
            'max_price' => $variants->max('price'),
            'total_stock_quantity' => $variants->sum('stock_quantity')
        ];
    }
}