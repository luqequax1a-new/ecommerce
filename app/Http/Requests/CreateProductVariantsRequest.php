<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\ProductAttribute;

class CreateProductVariantsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming authorization is handled at controller level
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
            'selected_attributes' => [
                'required',
                'array',
                'min:1',
                'max:10' // Reasonable limit for attribute selection
            ],
            'selected_attributes.*' => [
                'required',
                'integer',
                Rule::exists('product_attributes', 'id')
            ],
            'combinations' => [
                'sometimes',
                'array',
                'max:400' // Enterprise limit for combinations
            ],
            'combinations.*.attributes' => [
                'required',
                'array'
            ],
            'combinations.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99' // DECIMAL(12,2) constraint
            ],
            'combinations.*.stock_quantity' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.999' // DECIMAL(12,3) constraint
            ],
            'combinations.*.sku' => [
                'sometimes',
                'string',
                'max:100',
                'unique:product_variants,sku'
            ],
            'combinations.*.is_default' => [
                'sometimes',
                'boolean'
            ],
            'combinations.*.is_active' => [
                'sometimes',
                'boolean'
            ],
            'combinations.*.barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:50'
            ],
            'combinations.*.ean' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9]{8,14}$/' // EAN format validation
            ],
            'combinations.*.image_path' => [
                'sometimes',
                'nullable',
                'string',
                'max:500'
            ],
            'combinations.*.sort_order' => [
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
            'selected_attributes.required' => 'En az bir özellik seçilmelidir.',
            'selected_attributes.array' => 'Özellikler dizi formatında olmalıdır.',
            'selected_attributes.min' => 'En az bir özellik seçilmelidir.',
            'selected_attributes.max' => 'En fazla 10 özellik seçilebilir.',
            'selected_attributes.*.exists' => 'Seçilen özellik bulunamadı.',
            'combinations.max' => 'En fazla 400 varyant kombinasyonu oluşturulabilir.',
            'combinations.*.price.required' => 'Varyant fiyatı gereklidir.',
            'combinations.*.price.numeric' => 'Varyant fiyatı sayısal olmalıdır.',
            'combinations.*.price.min' => 'Varyant fiyatı sıfırdan küçük olamaz.',
            'combinations.*.price.max' => 'Varyant fiyatı çok yüksek (maksimum ₺999.999,99).',
            'combinations.*.stock_quantity.required' => 'Stok miktarı gereklidir.',
            'combinations.*.stock_quantity.numeric' => 'Stok miktarı sayısal olmalıdır.',
            'combinations.*.stock_quantity.min' => 'Stok miktarı sıfırdan küçük olamaz.',
            'combinations.*.stock_quantity.max' => 'Stok miktarı çok yüksek (maksimum 999.999.999,999).',
            'combinations.*.sku.unique' => 'Bu SKU zaten kullanılıyor.',
            'combinations.*.sku.max' => 'SKU en fazla 100 karakter olabilir.',
            'combinations.*.barcode.max' => 'Barkod en fazla 50 karakter olabilir.',
            'combinations.*.ean.max' => 'EAN en fazla 20 karakter olabilir.',
            'combinations.*.ean.regex' => 'EAN formatı geçersiz (8-14 haneli sayı olmalı).',
            'combinations.*.image_path.max' => 'Görsel yolu en fazla 500 karakter olabilir.',
            'combinations.*.sort_order.min' => 'Sıralama değeri sıfırdan küçük olamaz.',
            'combinations.*.sort_order.max' => 'Sıralama değeri çok yüksek (maksimum 9999).'
        ];
    }

    /**
     * Get custom attributes for validation error messages
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'ürün',
            'selected_attributes' => 'seçilen özellikler',
            'combinations' => 'varyant kombinasyonları',
            'combinations.*.price' => 'varyant fiyatı',
            'combinations.*.stock_quantity' => 'stok miktarı',
            'combinations.*.sku' => 'SKU',
            'combinations.*.barcode' => 'barkod',
            'combinations.*.ean' => 'EAN',
            'combinations.*.image_path' => 'görsel yolu',
            'combinations.*.sort_order' => 'sıralama'
        ];
    }

    /**
     * Configure the validator instance with enterprise business rules
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateCombinationCount($validator);
            $this->validateDefaultVariantUniqueness($validator);
            $this->validateAttributeSelection($validator);
        });
    }

    /**
     * Validate combination count and provide warning for large numbers
     */
    private function validateCombinationCount($validator): void
    {
        $selectedAttributes = $this->input('selected_attributes', []);
        
        if (empty($selectedAttributes)) {
            return;
        }

        // Calculate potential combinations
        $totalCombinations = 1;
        foreach ($selectedAttributes as $attributeId) {
            $attribute = ProductAttribute::with('activeValues')->find($attributeId);
            if ($attribute) {
                $valueCount = $attribute->activeValues->count();
                if ($valueCount > 0) {
                    $totalCombinations *= $valueCount;
                }
            }
        }

        // Add warning for large combination counts
        if ($totalCombinations > 400) {
            $validator->errors()->add('selected_attributes', 
                sprintf('Seçilen özellikler %d kombinasyon oluşturacak (limit: 400). Lütfen özellik seçimini gözden geçirin.', 
                       $totalCombinations));
        } elseif ($totalCombinations > 100) {
            // Add info message for moderately large counts
            $validator->errors()->add('_info', 
                sprintf('Seçilen özellikler %d kombinasyon oluşturacak. Büyük varyant sayıları yönetimi zorlaştırabilir.', 
                       $totalCombinations));
        }
    }

    /**
     * Validate that only one default variant is selected
     */
    private function validateDefaultVariantUniqueness($validator): void
    {
        $combinations = $this->input('combinations', []);
        $defaultCount = 0;

        foreach ($combinations as $index => $combination) {
            if (!empty($combination['is_default'])) {
                $defaultCount++;
            }
        }

        if ($defaultCount > 1) {
            $validator->errors()->add('combinations', 
                'Birden fazla varyant varsayılan olarak seçilemez. Sadece bir varyant varsayılan olabilir.');
        }
    }

    /**
     * Validate attribute selection business rules
     */
    private function validateAttributeSelection($validator): void
    {
        $selectedAttributes = $this->input('selected_attributes', []);
        
        foreach ($selectedAttributes as $attributeId) {
            $attribute = ProductAttribute::with('activeValues')->find($attributeId);
            
            if (!$attribute) {
                continue;
            }

            // Check if attribute has values
            if ($attribute->activeValues->count() === 0) {
                $validator->errors()->add("selected_attributes.{$attributeId}", 
                    "'{$attribute->name}' özelliğinin aktif değerleri bulunmuyor.");
            }

            // Check if attribute is variation type
            if ($attribute->type !== 'variation') {
                $validator->errors()->add("selected_attributes.{$attributeId}", 
                    "'{$attribute->name}' özelliği varyasyon tipi değil.");
            }
        }
    }

    /**
     * Prepare data for validation with whitelist protection
     */
    protected function prepareForValidation(): void
    {
        // Clean up combinations data - remove any non-whitelisted fields
        if ($this->has('combinations')) {
            $allowedCombinationFields = [
                'attributes', 'price', 'stock_quantity', 'sku', 'is_default', 
                'is_active', 'barcode', 'ean', 'image_path', 'sort_order'
            ];

            $cleanedCombinations = [];
            foreach ($this->input('combinations', []) as $combination) {
                $cleanedCombinations[] = array_intersect_key(
                    $combination, 
                    array_flip($allowedCombinationFields)
                );
            }

            $this->merge(['combinations' => $cleanedCombinations]);
        }
    }
}