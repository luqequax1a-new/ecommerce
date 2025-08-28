@extends('layouts.admin')

@section('title', 'Edit Product: ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Product: {{ $product->name }}</h1>
        <div class="mt-2">
            <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Products</a>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- SEO Information -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   maxlength="60">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to use product name</p>
                        </div>

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                      maxlength="160">{{ old('meta_description', $product->meta_description) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Leave empty to use description excerpt</p>
                        </div>

                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="keyword1, keyword2, keyword3">
                        </div>
                    </div>
                </div>

                <!-- Existing Images -->
                @if($product->images->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Images</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="existing-images">
                        @foreach($product->images as $image)
                            <div class="relative group" data-image-id="{{ $image->id }}">
                                <img src="{{ $image->medium_url }}" alt="{{ $image->alt_text }}" 
                                     class="w-full aspect-square object-cover rounded-lg border">
                                
                                <!-- Image Controls -->
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center space-x-2">
                                    <button type="button" onclick="setCoverImage({{ $image->id }})" 
                                            class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 {{ $image->is_cover ? 'bg-green-600' : '' }}">
                                        {{ $image->is_cover ? 'Cover' : 'Set Cover' }}
                                    </button>
                                    <button type="button" onclick="deleteImage({{ $image->id }})" 
                                            class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700">
                                        Delete
                                    </button>
                                </div>
                                
                                <!-- Alt Text Input -->
                                <input type="text" name="image_alts[{{ $image->id }}]" value="{{ $image->alt_text }}" 
                                       placeholder="Alt text" 
                                       class="mt-2 w-full px-2 py-1 text-xs border border-gray-300 rounded">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Upload New Images -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Images</h2>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors" id="image-upload-area">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4">
                            <label for="images" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    Click to upload images or drag and drop
                                </span>
                                <span class="mt-1 block text-xs text-gray-500">
                                    PNG, JPG, GIF up to 10MB each
                                </span>
                                <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                            </label>
                        </div>
                    </div>
                    
                    <!-- Image Preview Area -->
                    <div id="image-previews" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status & Categories -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="category_id" name="category_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select id="brand_id" name="brand_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Brand</option>
                                @foreach(\App\Models\Brand::all() as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                    
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            Update Product
                        </button>
                        
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank"
                           class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition-colors text-center block">
                            View Frontend
                        </a>
                        
                        <a href="{{ route('admin.products.show', $product) }}"
                           class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors text-center block">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Slug generation
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});

// Image upload preview
document.getElementById('images').addEventListener('change', function() {
    const previewContainer = document.getElementById('image-previews');
    previewContainer.innerHTML = '';
    
    Array.from(this.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full aspect-square object-cover rounded-lg border">
                    <button type="button" onclick="this.parentElement.remove()" 
                            class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700">
                        ×
                    </button>
                    <input type="text" name="new_image_alts[]" placeholder="Alt text" 
                           class="mt-2 w-full px-2 py-1 text-xs border border-gray-300 rounded">
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Image management functions
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`/admin/products/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-image-id="${imageId}"]`).remove();
            } else {
                alert('Error deleting image');
            }
        })
        .catch(() => alert('Error deleting image'));
    }
}

function setCoverImage(imageId) {
    fetch(`/admin/products/images/${imageId}/cover`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all buttons
            document.querySelectorAll('[data-image-id] button').forEach(btn => {
                if (btn.textContent.includes('Cover')) {
                    btn.textContent = 'Set Cover';
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-blue-600');
                }
            });
            // Update the clicked button
            const button = document.querySelector(`[data-image-id="${imageId}"] button`);
            button.textContent = 'Cover';
            button.classList.remove('bg-blue-600');
            button.classList.add('bg-green-600');
        } else {
            alert('Error setting cover image');
        }
    })
    .catch(() => alert('Error setting cover image'));
}

// Drag and drop for image upload
const uploadArea = document.getElementById('image-upload-area');
const fileInput = document.getElementById('images');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    uploadArea.classList.add('border-blue-500', 'bg-blue-50');
}

function unhighlight() {
    uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    fileInput.dispatchEvent(new Event('change'));
}
</script>
@endpush
@endsection