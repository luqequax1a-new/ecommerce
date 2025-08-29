<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of the attributes
     */
    public function index()
    {
        $attributes = ProductAttribute::with(['values' => function($query) {
            $query->active()->ordered()->limit(5);
        }])
        ->withCount('values')
        ->ordered()
        ->paginate(20);

        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        $attribute = new ProductAttribute();
        return view('admin.attributes.create', compact('attribute'));
    }

    /**
     * Store a newly created attribute
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_attributes',
            'type' => 'required|in:text,color,image',
            'is_required' => 'boolean',
            'is_variation' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request->only([
                'name', 'slug', 'type', 'is_required', 
                'is_variation', 'sort_order', 'is_active'
            ]);

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = SlugService::generateUnique(
                    $data['name'],
                    ProductAttribute::class
                );
            }

            // Set defaults
            $data['is_required'] = $request->boolean('is_required', false);
            $data['is_variation'] = $request->boolean('is_variation', true);
            $data['is_active'] = $request->boolean('is_active', true);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $attribute = ProductAttribute::create($data);

            return redirect()
                ->route('admin.attributes.edit', $attribute)
                ->with('success', 'Özellik başarıyla oluşturuldu.');
        });
    }

    /**
     * Show the form for editing the specified attribute
     */
    public function edit(ProductAttribute $attribute)
    {
        $attribute->load(['values' => function($query) {
            $query->ordered();
        }]);

        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_attributes')->ignore($attribute->id)],
            'type' => 'required|in:text,color,image',
            'is_required' => 'boolean',
            'is_variation' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        return DB::transaction(function () use ($request, $attribute) {
            $data = $request->only([
                'name', 'slug', 'type', 'is_required', 
                'is_variation', 'sort_order', 'is_active'
            ]);

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = SlugService::generateUnique(
                    $data['name'],
                    ProductAttribute::class,
                    $attribute->id
                );
            }

            // Set defaults
            $data['is_required'] = $request->boolean('is_required', false);
            $data['is_variation'] = $request->boolean('is_variation', true);
            $data['is_active'] = $request->boolean('is_active', true);
            $data['sort_order'] = $data['sort_order'] ?? $attribute->sort_order;

            $attribute->update($data);

            return redirect()
                ->route('admin.attributes.edit', $attribute)
                ->with('success', 'Özellik başarıyla güncellendi.');
        });
    }

    /**
     * Remove the specified attribute
     */
    public function destroy(ProductAttribute $attribute)
    {
        return DB::transaction(function () use ($attribute) {
            // Check if attribute is used in any product variants
            $variantCount = DB::table('product_variants')
                ->whereRaw("JSON_EXTRACT(attributes, '$.{$attribute->slug}') IS NOT NULL")
                ->count();

            if ($variantCount > 0) {
                return back()->withErrors([
                    'error' => "Bu özellik {$variantCount} ürün varyantında kullanıldığı için silinemez."
                ]);
            }

            // Delete associated images
            foreach ($attribute->values as $value) {
                if ($value->image_path) {
                    Storage::disk('public')->delete($value->image_path);
                }
            }

            $attribute->delete();

            return redirect()
                ->route('admin.attributes.index')
                ->with('success', 'Özellik başarıyla silindi.');
        });
    }

    /**
     * Store a new attribute value
     */
    public function storeValue(Request $request, ProductAttribute $attribute)
    {
        $rules = [
            'value' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ];

        // Add type-specific validation
        if ($attribute->type === 'color') {
            $rules['color_code'] = 'required|regex:/^#[a-fA-F0-9]{6}$/';
        } elseif ($attribute->type === 'image') {
            $rules['image'] = 'required|image|mimes:jpeg,png,gif,webp|max:2048';
        }

        $request->validate($rules);

        return DB::transaction(function () use ($request, $attribute) {
            $data = [
                'product_attribute_id' => $attribute->id,
                'value' => $request->value,
                'slug' => $request->slug ?: SlugService::generate($request->value),
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->boolean('is_active', true)
            ];

            // Handle type-specific data
            if ($attribute->type === 'color') {
                $data['color_code'] = $request->color_code;
            } elseif ($attribute->type === 'image' && $request->hasFile('image')) {
                $path = $request->file('image')->store('attributes/images', 'public');
                $data['image_path'] = $path;
            }

            // Ensure slug uniqueness within attribute
            $originalSlug = $data['slug'];
            $counter = 1;
            while (ProductAttributeValue::where('product_attribute_id', $attribute->id)
                                       ->where('slug', $data['slug'])
                                       ->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            ProductAttributeValue::create($data);

            return redirect()
                ->route('admin.attributes.edit', $attribute)
                ->with('success', 'Özellik değeri başarıyla eklendi.');
        });
    }

    /**
     * Update an attribute value
     */
    public function updateValue(Request $request, ProductAttribute $attribute, ProductAttributeValue $value)
    {
        $rules = [
            'value' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ];

        // Add type-specific validation
        if ($attribute->type === 'color') {
            $rules['color_code'] = 'required|regex:/^#[a-fA-F0-9]{6}$/';
        } elseif ($attribute->type === 'image') {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,gif,webp|max:2048';
        }

        $request->validate($rules);

        return DB::transaction(function () use ($request, $attribute, $value) {
            $data = [
                'value' => $request->value,
                'slug' => $request->slug ?: SlugService::generate($request->value),
                'sort_order' => $request->sort_order ?? $value->sort_order,
                'is_active' => $request->boolean('is_active', true)
            ];

            // Handle type-specific data
            if ($attribute->type === 'color') {
                $data['color_code'] = $request->color_code;
            } elseif ($attribute->type === 'image' && $request->hasFile('image')) {
                // Delete old image
                if ($value->image_path) {
                    Storage::disk('public')->delete($value->image_path);
                }
                // Store new image
                $path = $request->file('image')->store('attributes/images', 'public');
                $data['image_path'] = $path;
            }

            // Ensure slug uniqueness within attribute
            $originalSlug = $data['slug'];
            $counter = 1;
            while (ProductAttributeValue::where('product_attribute_id', $attribute->id)
                                       ->where('slug', $data['slug'])
                                       ->where('id', '!=', $value->id)
                                       ->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $value->update($data);

            return redirect()
                ->route('admin.attributes.edit', $attribute)
                ->with('success', 'Özellik değeri başarıyla güncellendi.');
        });
    }

    /**
     * Delete an attribute value
     */
    public function destroyValue(ProductAttribute $attribute, ProductAttributeValue $value)
    {
        return DB::transaction(function () use ($value) {
            // Check if value is used in any product variants
            $variantCount = DB::table('product_variants')
                ->whereRaw("JSON_EXTRACT(attributes, '$.{$value->attribute->slug}') = ?", [$value->value])
                ->count();

            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Bu değer {$variantCount} ürün varyantında kullanıldığı için silinemez."
                ]);
            }

            // Delete associated image
            if ($value->image_path) {
                Storage::disk('public')->delete($value->image_path);
            }

            $value->delete();

            return response()->json([
                'success' => true,
                'message' => 'Özellik değeri başarıyla silindi.'
            ]);
        });
    }

    /**
     * Get attribute values for AJAX requests
     */
    public function getValues(ProductAttribute $attribute)
    {
        $values = $attribute->activeValues()
            ->ordered()
            ->get()
            ->map(function ($value) {
                return [
                    'id' => $value->id,
                    'value' => $value->value,
                    'display_value' => $value->display_value,
                    'color_code' => $value->color_code,
                    'image_url' => $value->image_url
                ];
            });

        return response()->json($values);
    }

    /**
     * Update attribute order via AJAX
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'attribute_ids' => 'required|array',
            'attribute_ids.*' => 'integer|exists:product_attributes,id'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->attribute_ids as $index => $attributeId) {
                ProductAttribute::where('id', $attributeId)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Özellik sırası güncellendi.'
        ]);
    }

    /**
     * Update attribute value order via AJAX
     */
    public function updateValueOrder(Request $request, ProductAttribute $attribute)
    {
        $request->validate([
            'value_ids' => 'required|array',
            'value_ids.*' => 'integer|exists:product_attribute_values,id'
        ]);

        DB::transaction(function () use ($request, $attribute) {
            foreach ($request->value_ids as $index => $valueId) {
                ProductAttributeValue::where('id', $valueId)
                    ->where('product_attribute_id', $attribute->id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Değer sırası güncellendi.'
        ]);
    }
}