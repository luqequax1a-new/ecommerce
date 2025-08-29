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
     * Generate SEO preview for any entity
     */
    public function preview(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:product,category,brand',
            'entity_id' => 'required|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'slug' => 'nullable|string|max:255',
        ]);

        $entity = $this->getEntity($request->entity_type, $request->entity_id);
        
        if (!$entity) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found'
            ], 404);
        }

        // Use provided data for preview (don't save to database)
        $previewData = [
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'slug' => $request->slug,
        ];

        $preview = $this->seoService->generatePreview($entity, $previewData);

        return response()->json([
            'success' => true,
            'preview' => $preview
        ]);
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