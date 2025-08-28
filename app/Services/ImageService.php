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
        int $sortOrder = 0
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
            'original_filename' => $originalName,
            'path' => $originalPath,
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
            'is_cover' => $isCover,
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
}