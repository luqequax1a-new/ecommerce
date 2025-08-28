<?php

namespace App\Traits;

use App\Services\SlugService;
use App\Models\UrlRewrite;

trait HasSEO
{
    /**
     * Boot the trait
     */
    protected static function bootHasSEO()
    {
        // Automatically generate slug when creating
        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->name)) {
                $model->generateSlug();
            }
            
            // Generate canonical URL
            if (empty($model->canonical_url)) {
                $model->generateCanonicalUrl();
            }
        });

        // Handle slug changes when updating
        static::updating(function ($model) {
            $originalSlug = $model->getOriginal('slug');
            
            // Auto-update slug if name changed and auto_update_slug is true
            if ($model->auto_update_slug && $model->isDirty('name')) {
                $newSlug = $model->generateSlug();
                
                // Create URL rewrite if slug actually changed
                if ($originalSlug && $originalSlug !== $newSlug) {
                    SlugService::handleSlugChange(
                        $model,
                        $originalSlug,
                        $newSlug,
                        $model->getSEOEntityType()
                    );
                }
            }
            
            // Update canonical URL if slug changed
            if ($model->isDirty('slug')) {
                $model->generateCanonicalUrl();
            }
        });
    }

    /**
     * Generate unique slug for this model
     */
    public function generateSlug(): string
    {
        $scopeConditions = $this->getSlugScopeConditions();
        
        $this->slug = SlugService::generateUnique(
            $this->name,
            static::class,
            $this->id,
            $scopeConditions
        );
        
        return $this->slug;
    }

    /**
     * Generate canonical URL for this model
     */
    public function generateCanonicalUrl(): string
    {
        $this->canonical_url = SlugService::generateCanonicalUrl(
            $this,
            $this->getSEOEntityType()
        );
        
        return $this->canonical_url;
    }

    /**
     * Get the entity type for SEO purposes
     * Override in models to specify entity type
     */
    public function getSEOEntityType(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Get scope conditions for slug uniqueness
     * Override in models that need scoped uniqueness (e.g., categories with parent_id)
     */
    protected function getSlugScopeConditions(): array
    {
        return [];
    }

    /**
     * Get URL rewrites for this model
     */
    public function urlRewrites()
    {
        return UrlRewrite::getEntityRewrites(
            $this->getSEOEntityType(),
            $this->id
        );
    }

    /**
     * Check if slug is auto-updatable
     */
    public function isSlugAutoUpdatable(): bool
    {
        return $this->auto_update_slug ?? true;
    }

    /**
     * Manually set slug (useful for admin forms)
     */
    public function setSlug(string $slug): void
    {
        $oldSlug = $this->slug;
        $this->slug = $slug;
        
        if ($oldSlug && $oldSlug !== $slug && $this->exists) {
            SlugService::handleSlugChange(
                $this,
                $oldSlug,
                $slug,
                $this->getSEOEntityType()
            );
        }
        
        $this->generateCanonicalUrl();
    }

    /**
     * Get SEO meta data as array
     */
    public function getSEOMeta(): array
    {
        return [
            'title' => $this->meta_title ?: $this->name,
            'description' => $this->meta_description,
            'keywords' => $this->meta_keywords,
            'canonical' => $this->canonical_url,
            'robots' => $this->robots ?: 'index,follow',
        ];
    }

    /**
     * Check if meta title is within recommended length
     */
    public function isMetaTitleOptimal(): bool
    {
        $title = $this->meta_title ?: $this->name;
        return strlen($title) <= 60;
    }

    /**
     * Check if meta description is within recommended length
     */
    public function isMetaDescriptionOptimal(): bool
    {
        return $this->meta_description && strlen($this->meta_description) <= 160;
    }

    /**
     * Get SEO analysis for admin interface
     */
    public function getSEOAnalysis(): array
    {
        return [
            'title_length' => strlen($this->meta_title ?: $this->name),
            'title_optimal' => $this->isMetaTitleOptimal(),
            'description_length' => strlen($this->meta_description ?: ''),
            'description_optimal' => $this->isMetaDescriptionOptimal(),
            'has_canonical' => !empty($this->canonical_url),
            'slug_valid' => SlugService::validate($this->slug),
        ];
    }
}