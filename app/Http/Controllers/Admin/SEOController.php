<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SEOService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class SEOController extends Controller
{
    protected $seoService;

    public function __construct(SEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Generate SEO preview for any entity with live analysis
     * POST /admin/seo/preview (JSON)
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'entity_type' => 'required|string|in:product,category,brand',
                'entity_id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'slug' => 'nullable|string|max:255',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'focus_keyword' => 'nullable|string|max:100',
            ]);

            // Get entity if ID provided, otherwise create a mock entity for preview
            $entity = null;
            if ($request->entity_id) {
                $entity = $this->getEntity($request->entity_type, $request->entity_id);
                if (!$entity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Varlık bulunamadı'
                    ], 404);
                }
            } else {
                // Create mock entity for new items
                $entity = $this->createMockEntity($request->entity_type, $request->all());
            }

            // Use provided data for preview (don't save to database)
            $previewData = [
                'name' => $request->name,
                'description' => $request->description,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'slug' => $request->slug,
                'focus_keyword' => $request->focus_keyword,
            ];

            // Generate preview with enhanced analysis
            $preview = $this->seoService->generatePreview($entity, $previewData);
            $analysis = $this->seoService->performSEOAnalysis($entity, $previewData);
            
            // Enhanced analysis with focus keyword
            if ($request->focus_keyword) {
                $analysis = $this->enhanceAnalysisWithFocusKeyword($analysis, $previewData, $request->focus_keyword);
            }

            // Generate Google-like preview card
            $previewCard = $this->generatePreviewCard($entity, $previewData);

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $previewCard['title'],
                    'description' => $previewCard['description'],
                    'url' => $previewCard['url'],
                    'analysis' => [
                        'overall_score' => $analysis['overall_score'],
                        'title' => $analysis['title'],
                        'description' => $analysis['description'],
                        'url' => $analysis['url'],
                        'keywords' => $analysis['keywords'],
                        'technical' => $analysis['technical'],
                        'focus_keyword' => $analysis['focus_keyword'] ?? null,
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('SEO Preview Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'SEO önizleme oluşturulurken bir hata oluştu'
            ], 500);
        }
    }

    /**
     * Get SEO analysis for entity
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:product,category,brand',
            'entity_id' => 'required|integer',
        ]);

        $entity = $this->getEntity($request->entity_type, $request->entity_id);
        
        if (!$entity) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found'
            ], 404);
        }

        $analysis = $this->seoService->performSEOAnalysis($entity);

        return response()->json([
            'success' => true,
            'analysis' => $analysis
        ]);
    }

    /**
     * Generate canonical URL for entity
     */
    public function canonicalUrl(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:product,category,brand',
            'entity_id' => 'required|integer',
        ]);

        $entity = $this->getEntity($request->entity_type, $request->entity_id);
        
        if (!$entity) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found'
            ], 404);
        }

        $canonicalUrl = $this->seoService->generateCanonicalUrl($entity);

        return response()->json([
            'success' => true,
            'canonical_url' => $canonicalUrl
        ]);
    }

    /**
     * Generate schema markup for entity
     */
    public function schemaMarkup(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:product,category,brand',
            'entity_id' => 'required|integer',
        ]);

        $entity = $this->getEntity($request->entity_type, $request->entity_id);
        
        if (!$entity) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found'
            ], 404);
        }

        $schemaMarkup = $this->seoService->generateSchemaMarkup($entity);

        return response()->json([
            'success' => true,
            'schema_markup' => $schemaMarkup
        ]);
    }

    /**
     * Get sitemap data for all entities
     */
    public function sitemapData()
    {
        $sitemapData = [];

        // Products
        Product::active()->chunk(100, function ($products) use (&$sitemapData) {
            foreach ($products as $product) {
                $sitemapData[] = array_merge(
                    $this->seoService->generateSitemapData($product),
                    ['type' => 'product']
                );
            }
        });

        // Categories
        Category::active()->chunk(100, function ($categories) use (&$sitemapData) {
            foreach ($categories as $category) {
                $sitemapData[] = array_merge(
                    $this->seoService->generateSitemapData($category),
                    ['type' => 'category']
                );
            }
        });

        // Brands
        Brand::active()->chunk(100, function ($brands) use (&$sitemapData) {
            foreach ($brands as $brand) {
                $sitemapData[] = array_merge(
                    $this->seoService->generateSitemapData($brand),
                    ['type' => 'brand']
                );
            }
        });

        return response()->json([
            'success' => true,
            'sitemap_data' => $sitemapData,
            'total_urls' => count($sitemapData)
        ]);
    }

    /**
     * Generate XML sitemap
     */
    public function generateSitemap()
    {
        $sitemapData = [];

        // Get all sitemap data
        $response = $this->sitemapData();
        $data = $response->getData();
        $sitemapData = $data->sitemap_data;

        // Generate XML
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        
        foreach ($sitemapData as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url['loc']);
            $xml->writeElement('lastmod', $url['lastmod']);
            $xml->writeElement('changefreq', $url['changefreq']);
            $xml->writeElement('priority', $url['priority']);
            $xml->endElement(); // url
        }
        
        $xml->endElement(); // urlset
        $xml->endDocument();

        return response($xml->outputMemory())
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Validate and optimize content for SEO
     */
    public function optimizeContent(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'target_keywords' => 'nullable|string',
        ]);

        $content = $request->content;
        $keywords = $request->target_keywords ? explode(',', $request->target_keywords) : [];

        $optimization = [
            'original_length' => strlen($content),
            'word_count' => str_word_count($content),
            'keyword_density' => $this->calculateKeywordDensity($content, $keywords),
            'readability_score' => $this->calculateReadability($content),
            'suggestions' => $this->generateContentSuggestions($content, $keywords),
        ];

        return response()->json([
            'success' => true,
            'optimization' => $optimization
        ]);
    }

    /**
     * Get SEO recommendations for entity
     */
    public function recommendations(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:product,category,brand',
            'entity_id' => 'required|integer',
        ]);

        $entity = $this->getEntity($request->entity_type, $request->entity_id);
        
        if (!$entity) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found'
            ], 404);
        }

        $analysis = $this->seoService->performSEOAnalysis($entity);
        $recommendations = $this->generateRecommendations($analysis, $entity);

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations
        ]);
    }

    // Private helper methods

    private function getEntity(string $type, int $id)
    {
        switch ($type) {
            case 'product':
                return Product::find($id);
            case 'category':
                return Category::find($id);
            case 'brand':
                return Brand::find($id);
            default:
                return null;
        }
    }

    /**
     * Create a mock entity for preview when no ID is provided
     */
    private function createMockEntity(string $type, array $data)
    {
        $className = 'App\\Models\\' . ucfirst($type);
        $entity = new $className();
        
        // Set basic properties
        $entity->name = $data['name'] ?? 'Örnek ' . ucfirst($type);
        $entity->slug = $data['slug'] ?? \Str::slug($entity->name);
        $entity->description = $data['description'] ?? '';
        $entity->meta_title = $data['meta_title'] ?? '';
        $entity->meta_description = $data['meta_description'] ?? '';
        $entity->meta_keywords = $data['meta_keywords'] ?? '';
        
        // Set type-specific properties
        switch ($type) {
            case 'product':
                $entity->sku = 'PREVIEW-SKU';
                $entity->price = 0;
                $entity->is_active = true;
                break;
            case 'category':
                $entity->parent_id = null;
                $entity->is_active = true;
                break;
            case 'brand':
                $entity->is_active = true;
                break;
        }
        
        return $entity;
    }

    /**
     * Generate Google-like preview card
     */
    private function generatePreviewCard($entity, array $data): array
    {
        $title = $data['meta_title'] ?: $this->seoService->generateTitle($entity, $data);
        $description = $data['meta_description'] ?: $this->seoService->generateDescription($entity, $data);
        $url = $this->seoService->generateCanonicalUrl($entity);
        
        // Truncate for Google display
        $displayTitle = strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;
        $displayDescription = strlen($description) > 160 ? substr($description, 0, 157) . '...' : $description;
        
        return [
            'title' => $displayTitle,
            'description' => $displayDescription,
            'url' => $url,
            'display_url' => parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH),
        ];
    }

    /**
     * Enhance analysis with focus keyword insights
     */
    private function enhanceAnalysisWithFocusKeyword(array $analysis, array $data, string $focusKeyword): array
    {
        $title = $data['meta_title'] ?? '';
        $description = $data['meta_description'] ?? '';
        $content = $data['description'] ?? '';
        
        $focusKeywordLower = strtolower(trim($focusKeyword));
        
        // Check keyword presence
        $inTitle = stripos($title, $focusKeywordLower) !== false;
        $inDescription = stripos($description, $focusKeywordLower) !== false;
        $inContent = stripos($content, $focusKeywordLower) !== false;
        
        // Calculate keyword density in content
        $wordCount = str_word_count($content);
        $keywordCount = substr_count(strtolower($content), $focusKeywordLower);
        $density = $wordCount > 0 ? round(($keywordCount / $wordCount) * 100, 2) : 0;
        
        // Determine focus keyword score
        $focusScore = 0;
        if ($inTitle) $focusScore += 30;
        if ($inDescription) $focusScore += 25;
        if ($inContent && $density >= 0.5 && $density <= 2.5) $focusScore += 25;
        if ($keywordCount >= 1) $focusScore += 20;
        
        $recommendations = [];
        if (!$inTitle) $recommendations[] = 'Ana anahtar kelimeyi başlığa ekleyin';
        if (!$inDescription) $recommendations[] = 'Ana anahtar kelimeyi açıklamaya ekleyin';
        if ($density < 0.5) $recommendations[] = 'Anahtar kelime yoğunluğunu artırın (%0.5-2.5 önerilir)';
        if ($density > 2.5) $recommendations[] = 'Anahtar kelime yoğunluğunu azaltın (fazla kullanım)';
        
        $analysis['focus_keyword'] = [
            'keyword' => $focusKeyword,
            'in_title' => $inTitle,
            'in_description' => $inDescription,
            'in_content' => $inContent,
            'density' => $density,
            'count' => $keywordCount,
            'score' => $focusScore,
            'optimal' => $focusScore >= 75,
            'status' => $focusScore >= 75 ? 'good' : ($focusScore >= 50 ? 'warning' : 'error'),
            'recommendations' => $recommendations,
        ];
        
        // Adjust overall score based on focus keyword
        if (isset($analysis['overall_score'])) {
            $focusWeight = 0.2; // 20% weight for focus keyword
            $adjustedScore = ($analysis['overall_score'] * 0.8) + ($focusScore * $focusWeight);
            $analysis['overall_score'] = min(100, round($adjustedScore));
        }
        
        return $analysis;
    }

    private function calculateKeywordDensity(string $content, array $keywords): array
    {
        $densities = [];
        $wordCount = str_word_count(strtolower($content));
        
        foreach ($keywords as $keyword) {
            $keyword = trim(strtolower($keyword));
            $occurrences = substr_count(strtolower($content), $keyword);
            $densities[$keyword] = [
                'occurrences' => $occurrences,
                'density' => $wordCount > 0 ? round(($occurrences / $wordCount) * 100, 2) : 0
            ];
        }
        
        return $densities;
    }

    private function calculateReadability(string $content): float
    {
        $words = str_word_count($content);
        $sentences = max(1, substr_count($content, '.') + substr_count($content, '!') + substr_count($content, '?'));
        
        // Simplified readability score
        $avgWordsPerSentence = $words / $sentences;
        
        if ($avgWordsPerSentence <= 14) return 90; // Very easy
        if ($avgWordsPerSentence <= 18) return 80; // Easy
        if ($avgWordsPerSentence <= 22) return 70; // Fairly easy
        if ($avgWordsPerSentence <= 26) return 60; // Standard
        if ($avgWordsPerSentence <= 30) return 50; // Fairly difficult
        
        return 40; // Difficult
    }

    private function generateContentSuggestions(string $content, array $keywords): array
    {
        $suggestions = [];
        
        // Length suggestions
        $wordCount = str_word_count($content);
        if ($wordCount < 300) {
            $suggestions[] = 'İçerik çok kısa. En az 300 kelime olması SEO için daha iyidir.';
        }
        
        // Keyword suggestions
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (!stripos($content, $keyword)) {
                $suggestions[] = "'{$keyword}' anahtar kelimesi içerikte hiç geçmiyor.";
            }
        }
        
        // Structure suggestions
        if (!preg_match('/<h[1-6]/', $content)) {
            $suggestions[] = 'İçeriğe başlık etiketleri (H1, H2, vb.) eklemeyi düşünün.';
        }
        
        return $suggestions;
    }

    private function generateRecommendations(array $analysis, $entity): array
    {
        $recommendations = [];
        
        // Title recommendations
        if (!$analysis['title']['optimal']) {
            $recommendations[] = [
                'type' => 'title',
                'priority' => 'high',
                'message' => $analysis['title']['recommendations'][0] ?? 'Başlığı optimize edin',
                'current_length' => $analysis['title']['length'],
                'target_range' => '30-60 karakter'
            ];
        }
        
        // Description recommendations
        if (!$analysis['description']['optimal']) {
            $recommendations[] = [
                'type' => 'description',
                'priority' => 'high',
                'message' => $analysis['description']['recommendations'][0] ?? 'Açıklamayı optimize edin',
                'current_length' => $analysis['description']['length'],
                'target_range' => '120-160 karakter'
            ];
        }
        
        // Technical SEO recommendations
        $technical = $analysis['technical'];
        if (!$technical['has_meta_title']) {
            $recommendations[] = [
                'type' => 'meta_title',
                'priority' => 'medium',
                'message' => 'Meta title eksik. SEO için önemli bir faktördür.',
            ];
        }
        
        if (!$technical['has_meta_description']) {
            $recommendations[] = [
                'type' => 'meta_description',
                'priority' => 'medium',
                'message' => 'Meta description eksik. Arama sonuçlarında görünecek açıklama.',
            ];
        }
        
        // Image recommendations
        if (!$technical['has_image_alt_text']) {
            $recommendations[] = [
                'type' => 'image_alt',
                'priority' => 'medium',
                'message' => 'Görsel alt text\'leri eksik. Görme engelliler için önemli.',
            ];
        }
        
        return $recommendations;
    }
}