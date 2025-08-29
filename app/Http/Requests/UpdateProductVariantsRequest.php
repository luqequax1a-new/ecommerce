<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductVariant;

class UpdateProductVariantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled at controller level
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'variants' => [
                'required',
                'array',
                'min:1'
            ],
            'variants.*' => [
                'required',
                'array'
            ],
            'variants.*.id' => [
                'required',
                'integer',
                Rule::exists('product_variants', 'id')
            ],
            'variants.*.price' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:999999.99' // DECIMAL(12,2) constraint
            ],
            'variants.*.stock_quantity' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:999999999.999' // DECIMAL(12,3) constraint
            ],
            'variants.*.sku' => [
                'sometimes',
                'string',
                'max:100'
            ],
            'variants.*.is_default' => [
                'sometimes',
                'boolean'
            ],
            'variants.*.is_active' => [
                'sometimes',
                'boolean'
            ],
            'variants.*.barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:50'
            ],
            'variants.*.ean' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]{8,14}$/' // EAN format validation
            ],
            'variants.*.image_path' => [
                'sometimes',
                'nullable',
                'string',
                'max:500'
            ],
            'variants.*.sort_order' => [
                'sometimes',
                'integer',
                'min:0',
                'max:9999'
            ]
        ];
    }

    /**
     * Get custom validation messages in Turkish
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Ürün ID gereklidir.',
            'product_id.exists' => 'Seçilen ürün bulunamadı.',
            'variants.required' => 'Güncellenecek varyantlar gereklidir.',
            'variants.array' => 'Varyantlar dizi formatında olmalıdır.',
            'variants.min' => 'En az bir varyant güncellenmelidir.',
            'variants.*.id.required' => 'Varyant ID gereklidir.',
            'variants.*.id.exists' => 'Seçilen varyant bulunamadı.',
            'variants.*.price.numeric' => 'Varyant fiyatı sayısal olmalıdır.',
            'variants.*.price.min' => 'Varyant fiyatı sıfırdan küçük olamaz.',
            'variants.*.price.max' => 'Varyant fiyatı çok yüksek (maksimum ₺999.999,99).',
            'variants.*.stock_quantity.numeric' => 'Stok miktarı sayısal olmalıdır.',
            'variants.*.stock_quantity.min' => 'Stok miktarı sıfırdan küçük olamaz.',
            'variants.*.stock_quantity.max' => 'Stok miktarı çok yüksek (maksimum 999.999.999,999).',
            'variants.*.sku.unique' => 'Bu SKU zaten kullanılıyor.',
            'variants.*.sku.max' => 'SKU en fazla 100 karakter olabilir.',
            'variants.*.barcode.max' => 'Barkod en fazla 50 karakter olabilir.',
            'variants.*.ean.max' => 'EAN en fazla 20 karakter olabilir.',
            'variants.*.ean.regex' => 'EAN formatı geçersiz (8-14 haneli sayı olmalı).',
            'variants.*.image_path.max' => 'Görsel yolu en fazla 500 karakter olabilir.',
            'variants.*.sort_order.min' => 'Sıralama değeri sıfırdan küçük olamaz.',
            'variants.*.sort_order.max' => 'Sıralama değeri çok yüksek (maksimum 9999).'
        ];
    }

    /**
     * Get custom attributes for validation error messages
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'ürün',
            'variants' => 'varyantlar',
            'variants.*.price' => 'varyant fiyatı',
            'variants.*.stock_quantity' => 'stok miktarı',
            'variants.*.sku' => 'SKU',
            'variants.*.barcode' => 'barkod',
            'variants.*.ean' => 'EAN',
            'variants.*.image_path' => 'görsel yolu',
            'variants.*.sort_order' => 'sıralama'
        ];
    }

    /**
     * Configure the validator instance with enterprise business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVariantOwnership($validator);
            $this->validateDefaultVariantUniqueness($validator);
            $this->validateSkuUniqueness($validator);
        });
    }

    /**
     * Validate that all variants belong to the specified product
     */
    private function validateVariantOwnership($validator): void
    {
        $productId = $this->input('product_id');
        $variants = $this->input('variants', []);

        foreach ($variants as $index => $variant) {
            if (empty($variant['id'])) {
                continue;
            }

            $variantModel = ProductVariant::find($variant['id']);
            if ($variantModel && $variantModel->product_id != $productId) {
                $validator->errors()->add("variants.{$index}.id", 
                    'Bu varyant seçilen ürüne ait değil.');
            }
        }
    }

    /**
     * Validate that only one default variant is selected
     */
    private function validateDefaultVariantUniqueness($validator): void
    {
        $variants = $this->input('variants', []);
        $defaultCount = 0;
        $defaultVariantIds = [];

        foreach ($variants as $index => $variant) {
            if (!empty($variant['is_default'])) {
                $defaultCount++;
                $defaultVariantIds[] = $variant['id'] ?? null;
            }
        }

        if ($defaultCount > 1) {
            $validator->errors()->add('variants', 
                'Birden fazla varyant varsayılan olarak seçilemez. Sadece bir varyant varsayılan olabilir.');
        }
    }

    /**
     * Validate SKU uniqueness across variants
     */
    private function validateSkuUniqueness($validator): void
    {
        $variants = $this->input('variants', []);
        $skus = [];

        foreach ($variants as $index => $variant) {
            if (empty($variant['sku'])) {
                continue;
            }

            $sku = $variant['sku'];
            $variantId = $variant['id'] ?? null;

            // Check for duplicate SKUs within this request
            if (in_array($sku, $skus)) {
                $validator->errors()->add("variants.{$index}.sku", 
                    'Bu SKU bu güncelleme içinde birden fazla kez kullanılıyor.');
                continue;
            }

            $skus[] = $sku;

            // Check for existing SKU in database (excluding current variant)
            $existingVariant = ProductVariant::where('sku', $sku)
                                            ->when($variantId, function ($query, $variantId) {
                                                return $query->where('id', '!=', $variantId);
                                            })
                                            ->first();

            if ($existingVariant) {
                $validator->errors()->add("variants.{$index}.sku", 
                    'Bu SKU zaten kullanılıyor.');
            }
        }
    }

    /**
     * Prepare data for validation with whitelist protection
     */
    protected function prepareForValidation(): void
    {
        // Clean up variants data - remove any non-whitelisted fields
        if ($this->has('variants')) {
            $allowedVariantFields = [
                'id', 'price', 'stock_quantity', 'sku', 'is_default', 
                'is_active', 'barcode', 'ean', 'image_path', 'sort_order'
            ];

            $cleanedVariants = [];
            foreach ($this->input('variants', []) as $variant) {
                $cleanedVariants[] = array_intersect_key(
                    $variant, 
                    array_flip($allowedVariantFields)
                );
            }

            $this->merge(['variants' => $cleanedVariants]);
        }
    }

    /**
     * Get the validated data for update operations
     */
    public function getUpdateData(): array
    {
        $validated = $this->validated();
        $updateData = [];

        foreach ($validated['variants'] as $variant) {
            $variantId = $variant['id'];
            unset($variant['id']); // Remove ID from update data
            
            // Only include fields that are actually being updated
            $updateFields = array_filter($variant, function ($value) {
                return $value !== null && $value !== '';
            });

            if (!empty($updateFields)) {
                $updateData[$variantId] = $updateFields;
            }
        }

        return $updateData;
    }
}