/**
 * Product Clone Component - No Images Policy
 * 
 * Features:
 * - PrestaShop-style product cloning
 * - Zero images policy enforcement
 * - Turkish localization
 * - Progress feedback
 * - Policy warnings
 * 
 * Usage:
 * const cloner = new ProductCloner({
 *     apiEndpoint: '/admin/products',
 *     csrfToken: document.querySelector('meta[name=\"csrf-token\"]').content
 * });
 */

class ProductCloner {
    constructor(options) {
        this.options = {
            apiEndpoint: '/admin/products',
            csrfToken: '',
            ...options
        };

        this.isCloning = false;
        
        // Turkish localization
        this.messages = {
            cloneSuccess: 'Ürün kopyalandı. Görseller taşınmadı.',
            cloneError: 'Kopyalama hatası',
            cloning: 'Kopyalanıyor...',
            noImagesPolicy: 'Görseller kopyalanmayacak - temiz başlangıç için',
            confirm: 'Bu ürünü kopyalamak istediğinizden emin misiniz?',
            policyWarning: 'Dikkat: Yeni ürün 0 görselle oluşturulacak',
            networkError: 'Ağ hatası. Lütfen tekrar deneyin.',
            editProduct: 'Ürünü Düzenle',
            complexProduct: 'Karmaşık ürün - işlem uzun sürebilir'
        };

        this.init();
    }

    init() {
        this.bindEvents();
        console.log('ProductCloner initialized with no-images policy');
    }

    bindEvents() {
        // Clone button events - using event delegation
        document.addEventListener('click', this.handleCloneClick.bind(this));
    }

    handleCloneClick(event) {
        const cloneBtn = event.target.closest('[data-clone-product]');
        if (!cloneBtn || this.isCloning) {
            return;
        }

        event.preventDefault();
        
        const productId = cloneBtn.dataset.cloneProduct;
        if (!productId) {
            console.error('Product ID not found for clone button');
            return;
        }

        this.showCloneDialog(productId);
    }

    async showCloneDialog(productId) {
        try {
            // Get clone info first
            const info = await this.getCloneInfo(productId);
            
            // Build dialog content
            const dialogContent = this.buildCloneDialog(info);
            
            // Show dialog (using basic confirm for now, can be enhanced with modal)
            const confirmText = `${this.messages.confirm}\n\n` +
                               `${this.messages.policyWarning}\n` +
                               `${info.policy.message}\n\n` +
                               (info.warnings.length > 0 ? info.warnings.join('\n') + '\n\n' : '') +
                               'Devam edilsin mi?';
            
            if (confirm(confirmText)) {
                await this.executeClone(productId, info);
            }
            
        } catch (error) {
            console.error('Clone dialog error:', error);
            this.showNotification(this.messages.cloneError, 'error');
        }
    }

    async getCloneInfo(productId) {
        const response = await this.makeRequest('GET', `${this.options.apiEndpoint}/${productId}/clone-info`);
        return response;
    }

    buildCloneDialog(info) {
        // For future enhancement - can build proper modal dialog
        return {
            title: 'Ürün Kopyala (Görselsiz)',
            product: info.product,
            policy: info.policy,
            warnings: info.warnings,
            stats: {
                variants: info.variants_count,
                images: info.images_count,
                seo: info.has_seo_data
            }
        };
    }

    async executeClone(productId, info) {
        if (this.isCloning) {
            return;
        }

        this.isCloning = true;
        this.showCloneProgress(true);

        try {
            const response = await this.makeRequest('POST', `${this.options.apiEndpoint}/${productId}/clone`, {
                name_suffix: ' (Kopya)' // Can be made configurable
            });

            if (response.success) {
                this.showNotification(response.message, 'success');
                
                // Show edit link
                this.showCloneSuccess(response.product);
                
                // Optionally refresh the product list
                setTimeout(() => {
                    if (typeof window.productQuickEdit !== 'undefined') {
                        window.productQuickEdit.refresh();
                    }
                }, 1000);
                
            } else {
                throw new Error(response.message || this.messages.cloneError);
            }

        } catch (error) {
            console.error('Clone execution error:', error);
            this.showNotification(error.message || this.messages.cloneError, 'error');
        } finally {
            this.isCloning = false;
            this.showCloneProgress(false);
        }
    }

    showCloneProgress(isVisible) {
        // Update UI to show cloning progress
        const cloneButtons = document.querySelectorAll('[data-clone-product]');
        cloneButtons.forEach(btn => {
            btn.disabled = isVisible;
            if (isVisible) {
                btn.textContent = this.messages.cloning;
                btn.classList.add('cloning');
            } else {
                btn.textContent = 'Kopyala';
                btn.classList.remove('cloning');
            }
        });
    }

    showCloneSuccess(product) {
        // Create success notification with edit link
        const notification = document.createElement('div');
        notification.className = 'clone-success-notification';
        notification.innerHTML = `
            <div class=\"clone-success-content\">
                <h4>✓ Ürün Başarıyla Kopyalandı</h4>
                <p><strong>${product.name}</strong></p>
                <p class=\"policy-note\">📷 Görseller taşınmadı - temiz başlangıç için</p>
                <div class=\"clone-actions\">
                    <a href=\"${product.edit_url}\" class=\"btn btn-primary btn-sm\">
                        ${this.messages.editProduct}
                    </a>
                </div>
            </div>
        `;
        
        // Add to body and auto-remove
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 8000);
    }

    async makeRequest(method, url, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.options.csrfToken
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Bu işlem için yetkiniz bulunmuyor');
            } else if (response.status >= 500) {
                throw new Error(this.messages.networkError);
            }
        }

        return response.json();
    }

    showNotification(message, type = 'info') {
        // Create or update notification
        let notification = document.querySelector('.clone-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'clone-notification';
            document.body.appendChild(notification);
        }

        notification.className = `clone-notification ${type}`;
        notification.textContent = message;
        notification.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.display = 'none';
            }
        }, 5000);
    }

    // ==================== PUBLIC METHODS ====================

    cloneProduct(productId, options = {}) {
        const cloneOptions = {
            nameSuffix: ' (Kopya)',
            showDialog: true,
            ...options
        };

        if (cloneOptions.showDialog) {
            this.showCloneDialog(productId);
        } else {
            this.executeClone(productId, { policy: { message: this.messages.noImagesPolicy } });
        }
    }

    destroy() {
        document.removeEventListener('click', this.handleCloneClick);
        
        // Remove notifications
        const notifications = document.querySelectorAll('.clone-notification, .clone-success-notification');
        notifications.forEach(n => n.remove());
        
        console.log('ProductCloner destroyed');
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductCloner;
}

// Global registration for direct script usage
if (typeof window !== 'undefined') {
    window.ProductCloner = ProductCloner;
}

// Auto-initialize if DOM is ready and CSRF token exists
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
        
        if (csrfToken) {
            window.productCloner = new ProductCloner({
                apiEndpoint: '/admin/products',
                csrfToken: csrfToken
            });
        }
    });
}