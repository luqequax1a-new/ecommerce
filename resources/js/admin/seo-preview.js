/**
 * Enhanced SEO Preview Component
 * Provides live SEO preview with Google-like card and scoring system
 * Features: debounce (500ms), AbortController, ARIA live regions, XSS escaping, toast error handling
 */
class SEOPreview {
    constructor(options = {}) {
        this.options = {
            debounceDelay: 500,
            endpoint: '/admin/seo/preview',
            selectors: {
                entityType: '#entity_type',
                entityId: '#entity_id',
                name: '#name',
                description: '#description',
                slug: '#slug',
                metaTitle: '#meta_title',
                metaDescription: '#meta_description',
                metaKeywords: '#meta_keywords',
                focusKeyword: '#focus_keyword',
                previewContainer: '#seo-preview-container',
                scoreContainer: '#seo-score-container',
                recommendationsContainer: '#seo-recommendations-container'
            },
            ...options
        };

        this.debounceTimer = null;
        this.currentRequest = null;
        this.isInitialized = false;

        this.init();
    }

    /**
     * Initialize the SEO preview component
     */
    init() {
        if (this.isInitialized) return;

        this.createPreviewContainer();
        this.bindEvents();
        this.loadInitialPreview();
        
        this.isInitialized = true;
        console.log('SEO Preview initialized successfully');
    }

    /**
     * Create the preview container with ARIA live regions
     */
    createPreviewContainer() {
        const container = document.querySelector(this.options.selectors.previewContainer);
        if (!container) {
            console.error('SEO Preview container not found');
            return;
        }

        container.innerHTML = `
            <div class="seo-preview-wrapper">
                <!-- Google-like Preview Card -->
                <div class="google-preview-card" aria-live="polite" aria-label="SEO Önizleme Kartı">
                    <div class="preview-url" id="preview-url"></div>
                    <div class="preview-title" id="preview-title"></div>
                    <div class="preview-description" id="preview-description"></div>
                </div>

                <!-- SEO Score -->
                <div class="seo-score-section" aria-live="polite" aria-label="SEO Puanı">
                    <div class="score-header">
                        <h4>SEO Puanı</h4>
                        <div class="score-value" id="seo-score-value">0</div>
                    </div>
                    <div class="score-bar">
                        <div class="score-progress" id="seo-score-progress" style="width: 0%"></div>
                    </div>
                    <div class="score-description" id="seo-score-description">Analiz ediliyor...</div>
                </div>

                <!-- Analysis Details -->
                <div class="seo-analysis-details">
                    <div class="analysis-item" id="analysis-title">
                        <div class="analysis-label">Başlık</div>
                        <div class="analysis-status" id="title-status"></div>
                        <div class="analysis-details" id="title-details"></div>
                    </div>
                    
                    <div class="analysis-item" id="analysis-description">
                        <div class="analysis-label">Açıklama</div>
                        <div class="analysis-status" id="description-status"></div>
                        <div class="analysis-details" id="description-details"></div>
                    </div>
                    
                    <div class="analysis-item" id="analysis-url">
                        <div class="analysis-label">URL</div>
                        <div class="analysis-status" id="url-status"></div>
                        <div class="analysis-details" id="url-details"></div>
                    </div>
                    
                    <div class="analysis-item" id="analysis-keywords">
                        <div class="analysis-label">Anahtar Kelimeler</div>
                        <div class="analysis-status" id="keywords-status"></div>
                        <div class="analysis-details" id="keywords-details"></div>
                    </div>
                    
                    <div class="analysis-item" id="analysis-focus-keyword" style="display: none;">
                        <div class="analysis-label">Ana Anahtar Kelime</div>
                        <div class="analysis-status" id="focus-keyword-status"></div>
                        <div class="analysis-details" id="focus-keyword-details"></div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="seo-recommendations" aria-live="polite" aria-label="SEO Önerileri">
                    <h4>Öneriler</h4>
                    <ul id="seo-recommendations-list"></ul>
                </div>

                <!-- Loading State -->
                <div class="seo-loading" id="seo-loading" style="display: none;">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">SEO analizi yapılıyor...</div>
                </div>
            </div>
        `;

        this.addStyles();
    }

    /**
     * Add CSS styles for the preview component
     */
    addStyles() {
        if (document.getElementById('seo-preview-styles')) return;

        const style = document.createElement('style');
        style.id = 'seo-preview-styles';
        style.textContent = `
            .seo-preview-wrapper {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }

            .google-preview-card {
                background: #f8f9fa;
                border: 1px solid #dadce0;
                border-radius: 8px;
                padding: 16px;
                margin-bottom: 20px;
                font-family: arial, sans-serif;
            }

            .preview-url {
                color: #1a0dab;
                font-size: 14px;
                line-height: 20px;
                margin-bottom: 2px;
            }

            .preview-title {
                color: #1a0dab;
                font-size: 20px;
                line-height: 26px;
                font-weight: normal;
                text-decoration: underline;
                margin-bottom: 2px;
                cursor: pointer;
            }

            .preview-description {
                color: #4d5156;
                font-size: 14px;
                line-height: 22px;
            }

            .seo-score-section {
                margin-bottom: 20px;
            }

            .score-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }

            .score-header h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
            }

            .score-value {
                font-size: 18px;
                font-weight: bold;
                color: #059669;
            }

            .score-bar {
                height: 8px;
                background: #e5e7eb;
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 8px;
            }

            .score-progress {
                height: 100%;
                background: linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #10b981 100%);
                transition: width 0.3s ease;
            }

            .score-description {
                font-size: 14px;
                color: #6b7280;
            }

            .seo-analysis-details {
                margin-bottom: 20px;
            }

            .analysis-item {
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 12px;
                margin-bottom: 8px;
            }

            .analysis-label {
                font-weight: 600;
                margin-bottom: 4px;
            }

            .analysis-status {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                margin-bottom: 4px;
            }

            .analysis-status.good {
                background: #d1fae5;
                color: #065f46;
            }

            .analysis-status.warning {
                background: #fef3c7;
                color: #92400e;
            }

            .analysis-status.error {
                background: #fee2e2;
                color: #991b1b;
            }

            .analysis-details {
                font-size: 14px;
                color: #6b7280;
            }

            .seo-recommendations h4 {
                margin: 0 0 12px 0;
                font-size: 16px;
                font-weight: 600;
            }

            .seo-recommendations ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .seo-recommendations li {
                background: #fffbeb;
                border: 1px solid #fbbf24;
                border-radius: 4px;
                padding: 8px 12px;
                margin-bottom: 4px;
                font-size: 14px;
            }

            .seo-loading {
                text-align: center;
                padding: 40px;
            }

            .loading-spinner {
                width: 32px;
                height: 32px;
                border: 3px solid #e5e7eb;
                border-top: 3px solid #3b82f6;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 12px;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .loading-text {
                color: #6b7280;
                font-size: 14px;
            }

            /* Toast styles */
            .seo-toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ef4444;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                animation: slideIn 0.3s ease;
            }

            .seo-toast.success {
                background: #10b981;
            }

            .seo-toast.warning {
                background: #f59e0b;
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Bind events to form fields with debounce
     */
    bindEvents() {
        const fields = [
            'entityType', 'entityId', 'name', 'description', 'slug',
            'metaTitle', 'metaDescription', 'metaKeywords', 'focusKeyword'
        ];

        fields.forEach(field => {
            const element = document.querySelector(this.options.selectors[field]);
            if (element) {
                element.addEventListener('input', () => {
                    this.debouncedPreview();
                });
                element.addEventListener('change', () => {
                    this.debouncedPreview();
                });
            }
        });
    }

    /**
     * Debounced preview update
     */
    debouncedPreview() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            this.updatePreview();
        }, this.options.debounceDelay);
    }

    /**
     * Load initial preview on page load
     */
    loadInitialPreview() {
        // Load initial preview after a short delay to ensure form is ready
        setTimeout(() => {
            this.updatePreview();
        }, 100);
    }

    /**
     * Update the SEO preview
     */
    async updatePreview() {
        // Cancel previous request if still pending
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        // Show loading state
        this.showLoading();

        // Create new AbortController for this request
        this.currentRequest = new AbortController();

        try {
            // Gather form data
            const formData = this.getFormData();

            // Make API request
            const response = await fetch(this.options.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData),
                signal: this.currentRequest.signal
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                this.renderPreview(data.data);
                this.hideLoading();
            } else {
                throw new Error(data.message || 'SEO önizleme oluşturulamadı');
            }

        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('SEO preview request was cancelled');
                return;
            }

            console.error('SEO Preview Error:', error);
            this.hideLoading();
            this.showToast('SEO önizleme güncellenirken hata oluştu: ' + error.message, 'error');
        } finally {
            this.currentRequest = null;
        }
    }

    /**
     * Get form data for API request
     */
    getFormData() {
        const data = {};
        
        Object.keys(this.options.selectors).forEach(key => {
            if (key !== 'previewContainer' && key !== 'scoreContainer' && key !== 'recommendationsContainer') {
                const element = document.querySelector(this.options.selectors[key]);
                if (element) {
                    const fieldName = this.camelToSnake(key);
                    data[fieldName] = element.value || '';
                }
            }
        });

        return data;
    }

    /**
     * Convert camelCase to snake_case
     */
    camelToSnake(str) {
        return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
    }

    /**
     * Render the preview data with XSS escaping
     */
    renderPreview(data) {
        // Update Google-like preview card
        this.updateElement('#preview-url', this.escapeHtml(data.url));
        this.updateElement('#preview-title', this.escapeHtml(data.title));
        this.updateElement('#preview-description', this.escapeHtml(data.description));

        // Update SEO score
        this.updateScore(data.analysis.overall_score);

        // Update analysis details
        this.updateAnalysis(data.analysis);

        // Update recommendations
        this.updateRecommendations(data.analysis);
    }

    /**
     * Update element content with XSS escaping
     */
    updateElement(selector, content) {
        const element = document.querySelector(selector);
        if (element) {
            element.textContent = content;
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Update SEO score display
     */
    updateScore(score) {
        const scoreValue = document.getElementById('seo-score-value');
        const scoreProgress = document.getElementById('seo-score-progress');
        const scoreDescription = document.getElementById('seo-score-description');

        if (scoreValue) scoreValue.textContent = score;
        if (scoreProgress) scoreProgress.style.width = score + '%';
        
        if (scoreDescription) {
            let description = '';
            if (score >= 80) {
                description = 'Mükemmel! SEO optimizasyonu çok iyi.';
            } else if (score >= 60) {
                description = 'İyi! Birkaç iyileştirme yapılabilir.';
            } else if (score >= 40) {
                description = 'Orta! SEO optimizasyonu geliştirilebilir.';
            } else {
                description = 'Zayıf! SEO optimizasyonu önemli iyileştirmeler gerektirir.';
            }
            scoreDescription.textContent = description;
        }
    }

    /**
     * Update analysis details
     */
    updateAnalysis(analysis) {
        // Title analysis
        this.updateAnalysisItem('title', analysis.title);
        
        // Description analysis
        this.updateAnalysisItem('description', analysis.description);
        
        // URL analysis
        this.updateAnalysisItem('url', analysis.url);
        
        // Keywords analysis
        this.updateAnalysisItem('keywords', analysis.keywords);
        
        // Focus keyword analysis (if available)
        if (analysis.focus_keyword) {
            this.updateAnalysisItem('focus-keyword', analysis.focus_keyword);
            document.getElementById('analysis-focus-keyword').style.display = 'block';
        } else {
            document.getElementById('analysis-focus-keyword').style.display = 'none';
        }
    }

    /**
     * Update individual analysis item
     */
    updateAnalysisItem(type, data) {
        const statusElement = document.getElementById(`${type}-status`);
        const detailsElement = document.getElementById(`${type}-details`);

        if (statusElement) {
            statusElement.className = `analysis-status ${data.status || 'good'}`;
            statusElement.textContent = this.getStatusText(data.status);
        }

        if (detailsElement) {
            let details = '';
            if (data.length !== undefined) {
                details += `Uzunluk: ${data.length} karakter`;
                if (data.optimal !== undefined) {
                    details += data.optimal ? ' (Optimal)' : ' (Optimize edilebilir)';
                }
            }
            if (data.recommendations && data.recommendations.length > 0) {
                details += data.recommendations.length > 0 ? `\n${data.recommendations[0]}` : '';
            }
            detailsElement.textContent = details;
        }
    }

    /**
     * Get status text in Turkish
     */
    getStatusText(status) {
        switch (status) {
            case 'good': return 'İyi';
            case 'warning': return 'Uyarı';
            case 'error': return 'Hata';
            default: return 'Bilinmiyor';
        }
    }

    /**
     * Update recommendations list
     */
    updateRecommendations(analysis) {
        const recommendationsList = document.getElementById('seo-recommendations-list');
        if (!recommendationsList) return;

        const recommendations = [];

        // Collect recommendations from all analysis items
        ['title', 'description', 'url', 'keywords', 'focus_keyword'].forEach(key => {
            if (analysis[key] && analysis[key].recommendations) {
                recommendations.push(...analysis[key].recommendations);
            }
        });

        // Clear existing recommendations
        recommendationsList.innerHTML = '';

        // Add new recommendations with XSS escaping
        recommendations.forEach(recommendation => {
            const li = document.createElement('li');
            li.textContent = recommendation;
            recommendationsList.appendChild(li);
        });

        // Show message if no recommendations
        if (recommendations.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'Harika! Şu anda SEO önerisi bulunmuyor.';
            li.style.background = '#d1fae5';
            li.style.borderColor = '#10b981';
            recommendationsList.appendChild(li);
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const loading = document.getElementById('seo-loading');
        if (loading) {
            loading.style.display = 'block';
        }
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const loading = document.getElementById('seo-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'error') {
        // Remove existing toasts
        document.querySelectorAll('.seo-toast').forEach(toast => toast.remove());

        // Create new toast
        const toast = document.createElement('div');
        toast.className = `seo-toast ${type}`;
        toast.textContent = this.escapeHtml(message);

        // Add to page
        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);

        // Allow manual dismissal
        toast.addEventListener('click', () => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        });
    }

    /**
     * Destroy the component
     */
    destroy() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        this.isInitialized = false;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SEOPreview;
}

// Auto-initialize if DOM is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('#seo-preview-container')) {
                window.seoPreview = new SEOPreview();
            }
        });
    } else {
        // DOM is already ready
        if (document.querySelector('#seo-preview-container')) {
            window.seoPreview = new SEOPreview();
        }
    }
}