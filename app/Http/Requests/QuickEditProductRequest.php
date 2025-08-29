<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class QuickEditProductRequest extends FormRequest
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
        $productId = $this->route('product')->id ?? null;
        
        return [
            'field' => [
                'required',
                'string',
                Rule::in([
                    'is_active', 'featured', 'category_id', 'brand_id', 
                    'price', 'stock_quantity', 'sort_order', 'meta_title', 'slug'
                ])
            ],
            'value' => [
                'required',
                function ($attribute, $value, $fail) use ($productId) {
                    $field = $this->input('field');
                    $fieldRules = $this->getFieldSpecificRules($field, $productId);
                    
                    $validator = validator(['value' => $value], ['value' => $fieldRules]);
                    
                    if ($validator->fails()) {
                        $fail($validator->errors()->first('value'));
                    }
                }
            ]
        ];
    }

    /**
     * Get custom validation messages in Turkish
     */
    public function messages(): array
    {
        return [
            'field.required' => 'Alan adı gereklidir.',
            'field.in' => 'Geçersiz alan adı.',
            'value.required' => 'Değer gereklidir.',
        ];
    }

    /**
     * Get field-specific validation rules
     */
    private function getFieldSpecificRules(string $field, ?int $productId): array
    {
        $rules = [
            'is_active' => [
                'boolean'
            ],
            'featured' => [
                'boolean'
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id'
            ],
            'brand_id' => [
                'nullable', 
                'integer',
                'exists:brands,id'
            ],
            'price' => [
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'stock_quantity' => [
                'numeric',
                'min:0',
                'max:999999999.999'
            ],
            'sort_order' => [
                'integer',
                'min:0',
                'max:9999'
            ],
            'meta_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'slug' => [
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $productId ? Rule::unique('products', 'slug')->ignore($productId) : 'unique:products,slug'
            ]
        ];

        return $rules[$field] ?? ['string'];
    }

    /**
     * Get custom attribute names for validation messages
     */
    public function attributes(): array
    {
        return [
            'field' => 'alan',
            'value' => 'değer'
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean and format the value based on field type
        $field = $this->input('field');
        $value = $this->input('value');

        if ($field && $value !== null) {
            $formattedValue = $this->formatValueForValidation($field, $value);
            $this->merge(['value' => $formattedValue]);
        }
    }

    /**
     * Format value for validation based on field type
     */
    private function formatValueForValidation(string $field, $value)
    {
        switch ($field) {
            case 'is_active':
            case 'featured':
                // Handle various boolean representations
                if (is_string($value)) {
                    $value = trim(strtolower($value));
                    return in_array($value, ['1', 'true', 'aktif', 'evet', 'on']);
                }
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
            case 'price':
            case 'stock_quantity':
                // Handle Turkish number format (₺12.345,67)
                if (is_string($value)) {
                    $value = str_replace(['₺', ' '], '', $value); // Remove currency and spaces
                    $value = str_replace('.', '', $value); // Remove thousand separators
                    $value = str_replace(',', '.', $value); // Replace decimal comma with dot
                }
                return $value;
            
            case 'sort_order':
            case 'category_id':
            case 'brand_id':
                return $value === '' ? null : $value;
            
            case 'slug':
                // Auto-generate slug if needed
                return $value ? \Illuminate\Support\Str::slug($value) : $value;
            
            default:
                return $value;
        }
    }

    /**
     * Get the validated data with proper formatting
     */
    public function getFormattedData(): array
    {
        $validated = $this->validated();
        $field = $validated['field'];
        $value = $validated['value'];

        return [
            'field' => $field,
            'value' => $this->formatValueForDatabase($field, $value)
        ];
    }

    /**
     * Format value for database storage
     */
    private function formatValueForDatabase(string $field, $value)
    {
        switch ($field) {
            case 'is_active':
            case 'featured':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
            case 'price':
            case 'stock_quantity':
                return (float) $value;
            
            case 'sort_order':
            case 'category_id':
            case 'brand_id':
                return $value === null || $value === '' ? null : (int) $value;
            
            case 'slug':
                return \Illuminate\Support\Str::slug($value);
            
            default:
                return $value;
        }
    }
}