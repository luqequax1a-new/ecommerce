<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\UrlRewrite;

class SlugService
{
    /**
     * Turkish character transliteration map
     */
    protected static array $turkishMap = [
        'ç' => 'c', 'Ç' => 'C',
        'ğ' => 'g', 'Ğ' => 'G', 
        'ı' => 'i', 'I' => 'I',
        'İ' => 'I', 'i' => 'i',
        'ö' => 'o', 'Ö' => 'O',
        'ş' => 's', 'Ş' => 'S',
        'ü' => 'u', 'Ü' => 'U',
    ];

    /**
     * Generate slug from Turkish text with transliteration
     */
    public static function generate(string $text): string
    {
        // Turkish transliteration
        $transliterated = str_replace(
            array_keys(static::$turkishMap),
            array_values(static::$turkishMap),
            $text
        );

        // Convert to ASCII and create slug
        $slug = Str::slug($transliterated, '-', 'tr');
        
        // Clean up: remove multiple dashes, trim dashes from start/end
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return strtolower($slug);
    }

    /**
     * Generate unique slug for given model and scope
     */
    public static function generateUnique(
        string $text,
        string $modelClass,
        ?int $excludeId = null,
        ?array $scopeConditions = []
    ): string {
        $baseSlug = static::generate($text);
        $slug = $baseSlug;
        $counter = 1;
        
        do {
            $query = $modelClass::where('slug', $slug);
            
            // Exclude current record if updating
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            // Apply scope conditions (e.g., parent_id for categories)
            foreach ($scopeConditions as $field => $value) {
                $query->where($field, $value);
            }
            
            $exists = $query->exists();
            
            if ($exists) {
                $counter++;
                $slug = $baseSlug . '-' . $counter;
            }
        } while ($exists);
        
        return $slug;
    }

    /**
     * Generate category path (parent/child/slug format)
     */
    public static function generateCategoryPath($category): string
    {
        $path = [];
        $current = $category;
        
        // Build path from current to root
        while ($current) {
            array_unshift($path, $current->slug);
            $current = $current->parent;
        }
        
        return implode('/', $path);
    }

    /**
     * Create URL rewrite when slug changes
     */
    public static function handleSlugChange(
        $model,
        string $oldSlug,
        string $newSlug,
        string $entityType
    ): void {
        if ($oldSlug === $newSlug) {
            return;
        }

        // Generate old and new paths based on entity type
        $oldPath = static::generateEntityPath($entityType, $oldSlug, $model);
        $newPath = static::generateEntityPath($entityType, $newSlug, $model);

        // Create rewrite record
        UrlRewrite::createRewrite(
            $entityType,
            $model->id,
            $oldPath,
            $newPath,
            301
        );
    }

    /**
     * Generate full entity path for URLs
     */
    protected static function generateEntityPath(string $entityType, string $slug, $model): string
    {
        switch ($entityType) {
            case 'category':
                // For categories, use full parent path
                return '/kategori/' . static::generateCategoryPath($model);
            case 'brand':
                return '/marka/' . $slug;
            case 'product':
                return '/urun/' . $slug;
            default:
                return '/' . $slug;
        }
    }

    /**
     * Generate canonical URL for entity
     */
    public static function generateCanonicalUrl($model, string $entityType): string
    {
        $baseUrl = config('app.url');
        
        switch ($entityType) {
            case 'category':
                $path = static::generateCategoryPath($model);
                return $baseUrl . '/kategori/' . $path;
            case 'brand':
                return $baseUrl . '/marka/' . $model->slug;
            case 'product':
                return $baseUrl . '/urun/' . $model->slug;
            default:
                return $baseUrl;
        }
    }

    /**
     * Validate slug format
     */
    public static function validate(string $slug): bool
    {
        // Slug should only contain lowercase letters, numbers, and dashes
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1 
            && !str_starts_with($slug, '-') 
            && !str_ends_with($slug, '-')
            && !str_contains($slug, '--');
    }
}