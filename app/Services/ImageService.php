<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    /**
     * Prestashop tarzı resize profilleri
     */
    const RESIZE_PROFILES = [
        'thumbnail' => [80, 80, true],     // [width, height, crop]
        'small' => [200, 200, true],
        'medium' => [400, 400, true],
        'large' => [800, 800, false],      // crop = false, proportional resize
        'xlarge' => [1200, 1200, false],
    ];

    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Ürün için görsel yükle ve resize profillerini oluştur
     */
    public function uploadProductImage(
        Product $product, 
        UploadedFile $file, 
        ?string $altText = null, 
        bool $isCover = false,
        int $sortOrder = 0,
        array $variantIds = [],
        string $imageType = 'product',
        ?string $description = null
    ): ProductImage {
        // Dosya validasyonu
        $this->validateImageFile($file);

        // Unique filename oluştur
        $originalName = $file->getClientOriginalName();
        $filename = $this->generateUniqueFilename($product->id, $originalName);
        
        // Yükleme dizini
        $directory = "products/{$product->id}";
        
        // Orijinal dosyayı kaydet
        $originalPath = $file->storeAs($directory, $filename, 'public');
        
        // Görsel bilgilerini al
        $imageInfo = $this->getImageInfo(Storage::disk('public')->path($originalPath));
        
        // Veritabanına kaydet
        $productImage = ProductImage::create([
            'product_id' => $product->id,
            'variant_ids' => !empty($variantIds) ? $variantIds : null,
            'original_filename' => $originalName,
            'path' => $originalPath,
            'alt_text' => $altText,
            'description' => $description,
            'sort_order' => $sortOrder,
            'is_cover' => $isCover,
            'is_variant_specific' => !empty($variantIds),
            'image_type' => $imageType,
            'width' => $imageInfo['width'],
            'height' => $imageInfo['height'],
            'file_size' => $imageInfo['file_size'],
            'mime_type' => $imageInfo['mime_type']
        ]);

        // Resize profillerini oluştur
        $this->generateResizeProfiles($originalPath);

        // Eğer cover image ise, diğerlerini cover olmaktan çıkar
        if ($isCover) {
            $this->unsetOtherCoverImages($product, $productImage->id);
        }

        return $productImage;
    }

    /**
     * Tüm resize profillerini oluştur
     */
    public function generateResizeProfiles(string $originalPath): array
    {
        $generatedFiles = [];
        $fullPath = Storage::disk('public')->path($originalPath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Original file not found: {$fullPath}");
        }

        $pathInfo = pathinfo($originalPath);
        
        foreach (self::RESIZE_PROFILES as $sizeName => $config) {
            [$width, $height, $crop] = $config;
            
            try {
                $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
                $resizedFullPath = Storage::disk('public')->path($resizedPath);
                
                $image = $this->imageManager->read($fullPath);
                
                if ($crop) {
                    // Crop ve resize
                    $image->cover($width, $height);
                } else {
                    // Proportional resize
                    $image->scale(width: $width, height: $height);
                }
                
                // Kaliteyi ayarla (JPEG için)
                $image->save($resizedFullPath, quality: 85);
                
                $generatedFiles[$sizeName] = $resizedPath;
            } catch (\Exception $e) {
                \Log::error("Resize failed for {$sizeName}: " . $e->getMessage());
            }
        }
        
        return $generatedFiles;
    }

    /**
     * Ürünün tüm görsellerini yeniden oluştur (Prestashop "Regenerate Images" özelliği)
     */
    public function regenerateProductImages(Product $product): array
    {
        $results = [];
        
        foreach ($product->images as $image) {
            try {
                $generated = $this->generateResizeProfiles($image->path);
                $results[$image->id] = [
                    'success' => true,
                    'generated' => $generated
                ];
            } catch (\Exception $e) {
                $results[$image->id] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Tüm ürünlerin görsellerini yeniden oluştur
     */
    public function regenerateAllImages(): array
    {
        $results = [];
        $products = Product::with('images')->get();
        
        foreach ($products as $product) {
            $results[$product->id] = $this->regenerateProductImages($product);
        }
        
        return $results;
    }

    /**
     * Ürün görselini sil
     */
    public function deleteProductImage(ProductImage $image): bool
    {
        try {
            // Orijinal dosyayı sil
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            
            // Resize edilmiş dosyaları sil
            $this->deleteResizedImages($image->path);
            
            // Veritabanından sil
            $image->delete();
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Image deletion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resize edilmiş görselleri sil
     */
    protected function deleteResizedImages(string $originalPath): void
    {
        $pathInfo = pathinfo($originalPath);
        
        foreach (array_keys(self::RESIZE_PROFILES) as $sizeName) {
            $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
            
            if (Storage::disk('public')->exists($resizedPath)) {
                Storage::disk('public')->delete($resizedPath);
            }
        }
    }

    /**
     * Dosya validasyonu
     */
    protected function validateImageFile(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, GIF and WebP are allowed.');
        }

        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size too large. Maximum 10MB allowed.');
        }
    }

    /**
     * Unique filename oluştur
     */
    protected function generateUniqueFilename(int $productId, string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return $productId . '_' . time() . '_' . uniqid() . '.' . strtolower($extension);
    }

    /**
     * Görsel bilgilerini al
     */
    protected function getImageInfo(string $filePath): array
    {
        $imageSize = getimagesize($filePath);
        
        return [
            'width' => $imageSize[0] ?? null,
            'height' => $imageSize[1] ?? null,
            'file_size' => filesize($filePath),
            'mime_type' => $imageSize['mime'] ?? null
        ];
    }

    /**
     * Diğer cover image'ları kaldır
     */
    protected function unsetOtherCoverImages(Product $product, int $excludeId): void
    {
        ProductImage::where('product_id', $product->id)
            ->where('id', '!=', $excludeId)
            ->update(['is_cover' => false]);
    }

    /**
     * Görsel sırasını güncelle
     */
    public function updateImageOrder(array $imageIds): bool
    {
        try {
            foreach ($imageIds as $order => $imageId) {
                ProductImage::where('id', $imageId)->update(['sort_order' => $order]);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("Image order update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cover image'ı değiştir
     */
    public function setCoverImage(ProductImage $image): bool
    {
        try {
            // Önce tüm cover'ları kaldır
            ProductImage::where('product_id', $image->product_id)
                ->update(['is_cover' => false]);
            
            // Yeni cover'ı set et
            $image->update(['is_cover' => true]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Cover image update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk image upload with drag-drop support
     */
    public function bulkUploadImages(
        Product $product,
        array $files,
        array $metadata = []
    ): array {
        $results = [];
        $errors = [];
        
        foreach ($files as $index => $file) {
            try {
                $meta = $metadata[$index] ?? [];
                
                $image = $this->uploadProductImage(
                    $product,
                    $file,
                    $meta['alt_text'] ?? null,
                    $meta['is_cover'] ?? false,
                    $meta['sort_order'] ?? $index,
                    $meta['variant_ids'] ?? [],
                    $meta['image_type'] ?? 'product',
                    $meta['description'] ?? null
                );
                
                $results[] = $image;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => $results,
            'errors' => $errors,
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Associate images with variants
     */
    public function associateImagesWithVariants(
        array $imageIds,
        array $variantIds,
        bool $isVariantSpecific = true
    ): bool {
        try {
            ProductImage::whereIn('id', $imageIds)
                ->update([
                    'variant_ids' => $variantIds,
                    'is_variant_specific' => $isVariantSpecific
                ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Variant association failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get images for specific variant
     */
    public function getVariantImages(Product $product, ?int $variantId = null): array
    {
        $query = ProductImage::where('product_id', $product->id)
            ->ordered();
            
        if ($variantId) {
            $query->forVariant($variantId);
        }
        
        return $query->get()->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
                'thumbnail' => $image->getResizedUrl('thumbnail'),
                'medium' => $image->getResizedUrl('medium'),
                'alt_text' => $image->alt_text,
                'description' => $image->description,
                'is_cover' => $image->is_cover,
                'is_variant_specific' => $image->is_variant_specific,
                'variant_ids' => $image->variant_ids,
                'dimensions' => $image->dimensions,
                'file_size' => $image->human_file_size,
                'sort_order' => $image->sort_order
            ];
        })->toArray();
    }

    /**
     * Update image metadata
     */
    public function updateImageMetadata(
        ProductImage $image,
        array $metadata
    ): bool {
        try {
            $allowedFields = [
                'alt_text', 'description', 'sort_order',
                'is_cover', 'variant_ids', 'is_variant_specific', 'image_type'
            ];
            
            $updateData = array_intersect_key($metadata, array_flip($allowedFields));
            
            // Handle cover image logic
            if (isset($updateData['is_cover']) && $updateData['is_cover']) {
                $this->unsetOtherCoverImages($image->product, $image->id);
            }
            
            $image->update($updateData);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Image metadata update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk delete images
     */
    public function bulkDeleteImages(array $imageIds): array
    {
        $results = [];
        $errors = [];
        
        foreach ($imageIds as $imageId) {
            try {
                $image = ProductImage::find($imageId);
                if ($image && $this->deleteProductImage($image)) {
                    $results[] = $imageId;
                } else {
                    $errors[] = "Image {$imageId} not found or deletion failed";
                }
            } catch (\Exception $e) {
                $errors[] = "Error deleting image {$imageId}: " . $e->getMessage();
            }
        }
        
        return [
            'deleted' => $results,
            'errors' => $errors,
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Get image gallery statistics
     */
    public function getGalleryStatistics(Product $product): array
    {
        $images = $product->images;
        
        return [
            'total_images' => $images->count(),
            'cover_images' => $images->where('is_cover', true)->count(),
            'variant_specific_images' => $images->where('is_variant_specific', true)->count(),
            'general_images' => $images->where('is_variant_specific', false)->count(),
            'total_file_size' => $images->sum('file_size'),
            'total_file_size_mb' => round($images->sum('file_size') / 1024 / 1024, 2),
            'image_types' => $images->groupBy('image_type')->map->count()->toArray(),
            'images_with_alt_text' => $images->whereNotNull('alt_text')->count(),
            'images_with_description' => $images->whereNotNull('description')->count()
        ];
    }

    /**
     * Optimize images (compress and regenerate)
     */
    public function optimizeProductImages(Product $product): array
    {
        $results = [];
        
        foreach ($product->images as $image) {
            try {
                // Regenerate with optimized quality
                $generated = $this->generateResizeProfiles($image->path, 80); // Lower quality for optimization
                $results[$image->id] = [
                    'success' => true,
                    'generated' => $generated,
                    'original_size' => $image->file_size,
                    'optimized' => true
                ];
            } catch (\Exception $e) {
                $results[$image->id] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Enhanced resize profile generation with quality control
     */
    public function generateResizeProfiles(string $originalPath, int $quality = 85): array
    {
        $generatedFiles = [];
        $fullPath = Storage::disk('public')->path($originalPath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Original file not found: {$fullPath}");
        }

        $pathInfo = pathinfo($originalPath);
        
        foreach (self::RESIZE_PROFILES as $sizeName => $config) {
            [$width, $height, $crop] = $config;
            
            try {
                $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $sizeName . '.' . $pathInfo['extension'];
                $resizedFullPath = Storage::disk('public')->path($resizedPath);
                
                $image = $this->imageManager->read($fullPath);
                
                if ($crop) {
                    // Crop ve resize
                    $image->cover($width, $height);
                } else {
                    // Proportional resize
                    $image->scale(width: $width, height: $height);
                }
                
                // Kaliteyi ayarla
                $image->save($resizedFullPath, quality: $quality);
                
                $generatedFiles[$sizeName] = $resizedPath;
            } catch (\Exception $e) {
                \Log::error("Resize failed for {$sizeName}: " . $e->getMessage());
            }
        }
        
        return $generatedFiles;
    }
}