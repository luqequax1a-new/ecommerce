<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class SEOService
{
    /**
     * Generate SEO preview data for any entity
     */
    public function generatePreview($entity, array $data = []): array
    {
        $entityType = $this->getEntityType($entity);
        
        return [
            'title' => $this->generateTitle($entity, $data),
            'description' => $this->generateDescription($entity, $data),
            'url' => $this->generateCanonicalUrl($entity),
            'keywords' => $this->generateKeywords($entity, $data),
            'og_image' => $this->getOpenGraphImage($entity),
            'schema_markup' => $this->generateSchemaMarkup($entity),
            'analysis' => $this->performSEOAnalysis($entity, $data),
        ];
    }

    /**
     * Generate optimized title for entity
     */
    public function generateTitle($entity, array $data = []): string
    {
        $customTitle = $data['meta_title'] ?? $entity->meta_title ?? null;
        
        if ($customTitle) {
            return $this->optimizeTitle($customTitle);
        }
        
        $entityType = $this->getEntityType($entity);
        $siteName = config('app.name');
        
        switch ($entityType) {
            case 'product':
                $title = $entity->name;
                if ($entity->brand) {
                    $title = $entity->brand->name . ' ' . $title;
                }
                if ($entity->category) {
                    $title .= ' - ' . $entity->category->name;
                }
                $title .= ' | ' . $siteName;
                break;
                
            case 'category':
                $title = $entity->name . ' Ürünleri';
                if ($entity->parent) {
                    $title = $entity->parent->name . ' ' . $title;
                }
                $title .= ' | ' . $siteName;
                break;
                
            case 'brand':
                $title = $entity->name . ' Ürünleri ve Fiyatları | ' . $siteName;
                break;
                
            default:
                $title = $entity->name . ' | ' . $siteName;
        }
        
        return $this->optimizeTitle($title);
    }

    /**
     * Generate optimized description for entity
     */
    public function generateDescription($entity, array $data = []): string
    {
        $customDescription = $data['meta_description'] ?? $entity->meta_description ?? null;
        
        if ($customDescription) {
            return $this->optimizeDescription($customDescription);
        }
        
        $entityType = $this->getEntityType($entity);
        
        switch ($entityType) {
            case 'product':
                $description = $entity->short_description ?: 
                              ($entity->description ? Str::limit(strip_tags($entity->description), 120) : '');
                
                if (!$description) {
                    $description = $entity->name;
                    if ($entity->brand) {
                        $description = $entity->brand->name . ' ' . $description;
                    }
                    $description .= ' en uygun fiyatlar ile. Hızlı kargo, güvenilir alışveriş.';
                }
                break;
                
            case 'category':
                $description = $entity->description ? 
                              Str::limit(strip_tags($entity->description), 120) : 
                              ($entity->name . ' kategorisinde en kaliteli ürünler en uygun fiyatlarla. Ücretsiz kargo fırsatı.');
                break;
                
            case 'brand':
                $description = $entity->description ? 
                              Str::limit(strip_tags($entity->description), 120) : 
                              ($entity->name . ' markasının tüm ürünleri ve en güncel fiyatları. Orijinal ürünler, hızlı teslimat.');
                break;
                
            default:
                $description = $entity->description ? 
                              Str::limit(strip_tags($entity->description), 120) : 
                              $entity->name;
        }
        
        return $this->optimizeDescription($description);
    }

    /**
     * Generate keywords for entity
     */
    public function generateKeywords($entity, array $data = []): string
    {
        $customKeywords = $data['meta_keywords'] ?? $entity->meta_keywords ?? null;
        
        if ($customKeywords) {
            return $customKeywords;
        }
        
        $keywords = [];
        $entityType = $this->getEntityType($entity);
        
        switch ($entityType) {
            case 'product':
                $keywords[] = $entity->name;
                if ($entity->brand) {
                    $keywords[] = $entity->brand->name;
                }
                if ($entity->category) {
                    $keywords[] = $entity->category->name;
                }
                $keywords = array_merge($keywords, ['ürün', 'satış', 'fiyat', 'alışveriş']);
                break;
                
            case 'category':
                $keywords[] = $entity->name;
                $keywords[] = $entity->name . ' ürünleri';
                if ($entity->parent) {
                    $keywords[] = $entity->parent->name;
                }
                break;
                
            case 'brand':
                $keywords[] = $entity->name;
                $keywords[] = $entity->name . ' ürünleri';
                $keywords[] = $entity->name . ' fiyatları';
                break;
        }
        
        return implode(', ', array_unique($keywords));
    }

    /**
     * Generate canonical URL for entity
     */
    public function generateCanonicalUrl($entity): string
    {
        $entityType = $this->getEntityType($entity);
        $baseUrl = rtrim(config('app.url'), '/');
        
        switch ($entityType) {
            case 'product':
                return $baseUrl . '/urun/' . $entity->slug;
                
            case 'category':
                $path = $this->generateCategoryPath($entity);
                return $baseUrl . '/kategori/' . $path;
                
            case 'brand':
                return $baseUrl . '/marka/' . $entity->slug;
                
            default:
                return $baseUrl . '/' . $entity->slug;
        }
    }

    /**
     * Get Open Graph image for entity
     */
    public function getOpenGraphImage($entity): string
    {
        $entityType = $this->getEntityType($entity);
        $defaultImage = asset('images/og-default.jpg');
        
        switch ($entityType) {
            case 'product':
                if ($entity->orderedImages && $entity->orderedImages->count() > 0) {
                    return asset('storage/' . $entity->orderedImages->first()->path);
                }
                break;
                
            case 'category':
                if ($entity->image_path) {
                    return asset('storage/' . $entity->image_path);
                }
                break;
                
            case 'brand':
                if ($entity->logo_path) {
                    return asset('storage/' . $entity->logo_path);
                }
                break;
        }
        
        return $defaultImage;
    }

    /**
     * Generate Schema.org markup for entity
     */
    public function generateSchemaMarkup($entity): array
    {
        $entityType = $this->getEntityType($entity);
        $baseSchema = [
            '@context' => 'https://schema.org',
            'url' => $this->generateCanonicalUrl($entity),
            'name' => $entity->name,
        ];
        
        switch ($entityType) {
            case 'product':
                return array_merge($baseSchema, [
                    '@type' => 'Product',
                    'description' => strip_tags($entity->description ?? $entity->short_description ?? ''),
                    'sku' => $entity->sku,
                    'brand' => $entity->brand ? [
                        '@type' => 'Brand',
                        'name' => $entity->brand->name
                    ] : null,
                    'category' => $entity->category ? $entity->category->name : null,
                    'image' => $this->getOpenGraphImage($entity),
                    'offers' => $this->generateProductOffers($entity),
                ]);
                
            case 'category':
                return array_merge($baseSchema, [
                    '@type' => 'CollectionPage',
                    'description' => strip_tags($entity->description ?? ''),
                    'numberOfItems' => $entity->products()->active()->count(),
                ]);
                
            case 'brand':
                return array_merge($baseSchema, [
                    '@type' => 'Brand',
                    'description' => strip_tags($entity->description ?? ''),
                    'logo' => $this->getOpenGraphImage($entity),
                ]);
                
            default:
                return array_merge($baseSchema, [
                    '@type' => 'WebPage',
                    'description' => strip_tags($entity->description ?? ''),
                ]);
        }
    }

    /**
     * Perform comprehensive SEO analysis
     */
    public function performSEOAnalysis($entity, array $data = []): array
    {
        $title = $this->generateTitle($entity, $data);
        $description = $this->generateDescription($entity, $data);
        $keywords = $this->generateKeywords($entity, $data);
        
        $analysis = [
            'title' => [
                'value' => $title,
                'length' => strlen($title),
                'optimal' => strlen($title) >= 30 && strlen($title) <= 60,
                'status' => $this->getTitleStatus(strlen($title)),
                'recommendations' => $this->getTitleRecommendations(strlen($title)),
            ],
            'description' => [
                'value' => $description,
                'length' => strlen($description),
                'optimal' => strlen($description) >= 120 && strlen($description) <= 160,
                'status' => $this->getDescriptionStatus(strlen($description)),
                'recommendations' => $this->getDescriptionRecommendations(strlen($description)),
            ],
            'keywords' => [
                'value' => $keywords,
                'count' => count(explode(',', $keywords)),
                'optimal' => count(explode(',', $keywords)) <= 10,
                'status' => count(explode(',', $keywords)) <= 10 ? 'good' : 'warning',
            ],
            'url' => [
                'value' => $this->generateCanonicalUrl($entity),
                'slug_length' => strlen($entity->slug),
                'slug_optimal' => strlen($entity->slug) <= 50,
                'slug_valid' => SlugService::validate($entity->slug),
            ],
            'readability' => $this->analyzeReadability($entity),
            'technical' => $this->analyzeTechnicalSEO($entity),
        ];
        
        // Overall score calculation
        $analysis['overall_score'] = $this->calculateSEOScore($analysis);
        
        return $analysis;
    }

    /**
     * Generate sitemap data for entity
     */
    public function generateSitemapData($entity): array
    {
        return [
            'loc' => $this->generateCanonicalUrl($entity),
            'lastmod' => $entity->updated_at->toW3cString(),
            'changefreq' => $this->getChangeFrequency($entity),
            'priority' => $this->getPriority($entity),
        ];
    }

    /**
     * Get robots meta directive for entity
     */
    public function getRobotsDirective($entity): string
    {
        if (isset($entity->robots) && $entity->robots) {
            return $entity->robots;
        }
        
        // Default based on entity status
        if (method_exists($entity, 'isActive') && !$entity->isActive()) {
            return 'noindex,nofollow';
        }
        
        return 'index,follow';
    }

    // Private helper methods

    private function getEntityType($entity): string
    {
        return strtolower(class_basename($entity));
    }

    private function optimizeTitle(string $title): string
    {
        // Trim to optimal length while preserving word boundaries
        if (strlen($title) > 60) {
            $title = Str::limit($title, 57, '...');
        }
        
        return $title;
    }

    private function optimizeDescription(string $description): string
    {
        // Clean HTML tags and optimize length
        $description = strip_tags($description);
        
        if (strlen($description) > 160) {
            $description = Str::limit($description, 157, '...');
        }
        
        return $description;
    }

    private function generateCategoryPath($category): string
    {
        return SlugService::generateCategoryPath($category);
    }

    private function generateProductOffers($product): array
    {
        if ($product->variants->count() > 0) {
            $minPrice = $product->variants->min('price');
            $maxPrice = $product->variants->max('price');
            
            return [
                '@type' => 'AggregateOffer',
                'lowPrice' => $minPrice,
                'highPrice' => $maxPrice,
                'priceCurrency' => 'TRY',
                'availability' => $product->variants->sum('stock_quantity') > 0 ? 
                                'https://schema.org/InStock' : 
                                'https://schema.org/OutOfStock',
            ];
        }
        
        return [];
    }

    private function getTitleStatus(int $length): string
    {
        if ($length < 30) return 'warning';
        if ($length > 60) return 'error';
        return 'good';
    }

    private function getDescriptionStatus(int $length): string
    {
        if ($length < 120) return 'warning';
        if ($length > 160) return 'error';
        return 'good';
    }

    private function getTitleRecommendations(int $length): array
    {
        $recommendations = [];
        
        if ($length < 30) {
            $recommendations[] = 'Başlık çok kısa. En az 30 karakter olması önerilir.';
        } elseif ($length > 60) {
            $recommendations[] = 'Başlık çok uzun. Maksimum 60 karakter olması önerilir.';
        } else {
            $recommendations[] = 'Başlık uzunluğu optimal.';
        }
        
        return $recommendations;
    }

    private function getDescriptionRecommendations(int $length): array
    {
        $recommendations = [];
        
        if ($length < 120) {
            $recommendations[] = 'Açıklama çok kısa. En az 120 karakter olması önerilir.';
        } elseif ($length > 160) {
            $recommendations[] = 'Açıklama çok uzun. Maksimum 160 karakter olması önerilir.';
        } else {
            $recommendations[] = 'Açıklama uzunluğu optimal.';
        }
        
        return $recommendations;
    }

    private function analyzeReadability($entity): array
    {
        $content = strip_tags($entity->description ?? '');
        
        return [
            'word_count' => str_word_count($content),
            'sentence_count' => substr_count($content, '.') + substr_count($content, '!') + substr_count($content, '?'),
            'avg_words_per_sentence' => str_word_count($content) / max(1, substr_count($content, '.') + substr_count($content, '!') + substr_count($content, '?')),
            'readability_score' => $this->calculateReadabilityScore($content),
        ];
    }

    private function analyzeTechnicalSEO($entity): array
    {
        return [
            'has_meta_title' => !empty($entity->meta_title),
            'has_meta_description' => !empty($entity->meta_description),
            'has_canonical_url' => !empty($entity->canonical_url),
            'slug_format_valid' => SlugService::validate($entity->slug ?? ''),
            'has_image_alt_text' => $this->checkImageAltText($entity),
        ];
    }

    private function calculateReadabilityScore(string $content): float
    {
        // Simple readability calculation (Flesch-like)
        $words = str_word_count($content);
        $sentences = max(1, substr_count($content, '.') + substr_count($content, '!') + substr_count($content, '?'));
        $syllables = $this->countSyllables($content);
        
        // Simplified Flesch formula
        $score = 206.835 - (1.015 * ($words / $sentences)) - (84.6 * ($syllables / $words));
        
        return max(0, min(100, $score));
    }

    private function countSyllables(string $text): int
    {
        // Simplified syllable counting for Turkish
        $vowels = ['a', 'e', 'i', 'ı', 'o', 'ö', 'u', 'ü'];
        $syllables = 0;
        $prevWasVowel = false;
        
        foreach (str_split(strtolower($text)) as $char) {
            $isVowel = in_array($char, $vowels);
            if ($isVowel && !$prevWasVowel) {
                $syllables++;
            }
            $prevWasVowel = $isVowel;
        }
        
        return max(1, $syllables);
    }

    private function checkImageAltText($entity): bool
    {
        if (method_exists($entity, 'images') && $entity->images) {
            return $entity->images->every(function ($image) {
                return !empty($image->alt_text);
            });
        }
        
        return true; // No images to check
    }

    private function calculateSEOScore(array $analysis): int
    {
        $score = 0;
        $maxScore = 100;
        
        // Title (25 points)
        if ($analysis['title']['optimal']) $score += 25;
        elseif ($analysis['title']['status'] === 'warning') $score += 15;
        
        // Description (25 points)
        if ($analysis['description']['optimal']) $score += 25;
        elseif ($analysis['description']['status'] === 'warning') $score += 15;
        
        // Keywords (15 points)
        if ($analysis['keywords']['optimal']) $score += 15;
        else $score += 8;
        
        // URL (15 points)
        if ($analysis['url']['slug_optimal'] && $analysis['url']['slug_valid']) $score += 15;
        else $score += 8;
        
        // Technical SEO (20 points)
        $technical = $analysis['technical'];
        $technicalScore = 0;
        if ($technical['has_meta_title']) $technicalScore += 5;
        if ($technical['has_meta_description']) $technicalScore += 5;
        if ($technical['has_canonical_url']) $technicalScore += 5;
        if ($technical['slug_format_valid']) $technicalScore += 5;
        $score += $technicalScore;
        
        return min($maxScore, $score);
    }

    private function getChangeFrequency($entity): string
    {
        $entityType = $this->getEntityType($entity);
        
        switch ($entityType) {
            case 'product':
                return 'weekly';
            case 'category':
                return 'monthly';
            case 'brand':
                return 'monthly';
            default:
                return 'monthly';
        }
    }

    private function getPriority($entity): float
    {
        $entityType = $this->getEntityType($entity);
        
        switch ($entityType) {
            case 'product':
                // Higher priority for featured products
                return isset($entity->is_featured) && $entity->is_featured ? 0.9 : 0.7;
            case 'category':
                // Higher priority for parent categories
                return $entity->parent_id ? 0.6 : 0.8;
            case 'brand':
                return 0.7;
            default:
                return 0.5;
        }
    }
}