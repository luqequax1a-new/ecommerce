/**
 * Enhanced Product Image Manager Component
 * 
 * Features:
 * - Drag-drop image sorting with visual feedback
 * - Cover selection with instant UI update
 * - Multi-select variant assignment with checkbox groups
 * - Bulk operations: delete, set cover, assign variants
 * - Turkish localization
 * - PrestaShop-style interface patterns
 * 
 * Usage:
 * const imageManager = new ProductImageManager({
 *     container: '#image-management-section',
 *     productId: 123,
 *     apiEndpoint: '/admin/products'
 * });
 */

class ProductImageManager {
    constructor(options) {
        this.options = {
            container: '#image-management-section',
            productId: null,
            apiEndpoint: '/admin/products',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
            ...options
        };

        this.container = document.querySelector(this.options.container);
        this.productId = this.options.productId;
        this.selectedImages = new Set();
        this.sortableInstance = null;
        this.isInitialized = false;
        
        // Turkish localization
        this.messages = {
            selectImage: 'Görsel Seç',
            deselectImage: 'Seçimi Kaldır',
            deleteSelected: 'Seçilenleri Sil',
            setCover: 'Ana Görsel Yap',
            assignVariants: 'Varyantlara Ata',
            bulkOperations: 'Toplu İşlemler',
            confirmDelete: 'Seçilen görselleri silmek istediğinizden emin misiniz?',
            imagesSelected: 'görsel seçildi',
            loading: 'Yükleniyor...',
            saving: 'Kaydediliyor...',
            success: 'İşlem başarıyla tamamlandı',
            error: 'İşlem sırasında hata oluştu',
            noImagesSelected: 'Lütfen önce görsel seçin',
            noVariantsSelected: 'Lütfen en az bir varyant seçin',
            selectVariants: 'Varyantları Seç',
            variantAssignment: 'Varyant Ataması',
            applyToSelected: 'Seçilenlere Uygula',
            cancel: 'İptal',
            save: 'Kaydet'
        };

        this.init();
    }

    init() {
        if (!this.container || !this.productId) {
            console.error('ProductImageManager: Container or productId not found');
            return;
        }

        this.bindEvents();
        this.initializeSortable();
        this.updateBulkActionsState();
        
        this.isInitialized = true;
        console.log('ProductImageManager initialized');
    }

    bindEvents() {
        // Image selection
        this.container.addEventListener('click', this.handleImageClick.bind(this));
        
        // Bulk action buttons
        this.container.addEventListener('click', this.handleBulkActionClick.bind(this));
        
        // Variant assignment modal
        this.container.addEventListener('click', this.handleVariantAssignmentClick.bind(this));
        
        // Select all checkbox
        const selectAllCheckbox = this.container.querySelector('#select-all-images');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', this.handleSelectAllChange.bind(this));
        }
    }

    initializeSortable() {
        const gallery = this.container.querySelector('.image-gallery-container');
        if (gallery && typeof Sortable !== 'undefined') {
            this.sortableInstance = new Sortable(gallery, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle',
                onEnd: this.handleSortEnd.bind(this)
            });
        }
    }

    handleImageClick(event) {
        // Handle image selection
        if (event.target.closest('.image-checkbox')) {
            const checkbox = event.target.closest('.image-checkbox');
            const imageId = parseInt(checkbox.dataset.imageId);
            
            if (checkbox.checked) {
                this.selectedImages.add(imageId);
            } else {
                this.selectedImages.delete(imageId);
            }
            
            this.updateImageSelectionUI(imageId, checkbox.checked);
            this.updateBulkActionsState();
            this.updateSelectAllCheckbox();
        }
        
        // Handle cover image selection
        if (event.target.closest('.set-cover-btn')) {
            const button = event.target.closest('.set-cover-btn');
            const imageId = parseInt(button.dataset.imageId);
            this.setCoverImage(imageId);
        }
        
        // Handle delete image
        if (event.target.closest('.delete-image-btn')) {
            const button = event.target.closest('.delete-image-btn');
            const imageId = parseInt(button.dataset.imageId);
            this.deleteImage(imageId);
        }
    }

    handleBulkActionClick(event) {
        // Handle bulk delete
        if (event.target.closest('#bulk-delete-btn')) {
            this.bulkDeleteImages();
        }
        
        // Handle bulk set cover
        if (event.target.closest('#bulk-cover-btn')) {
            this.bulkSetCoverImage();
        }
        
        // Handle variant assignment
        if (event.target.closest('#bulk-variant-btn')) {
            this.openVariantAssignmentModal();
        }
    }

    handleVariantAssignmentClick(event) {
        // Handle save in variant assignment modal
        if (event.target.closest('#save-variant-assignment')) {
            this.saveVariantAssignment();
        }
    }

    handleSelectAllChange(event) {
        const isChecked = event.target.checked;
        const checkboxes = this.container.querySelectorAll('.image-checkbox');
        
        checkboxes.forEach(checkbox => {
            const imageId = parseInt(checkbox.dataset.imageId);
            checkbox.checked = isChecked;
            
            if (isChecked) {
                this.selectedImages.add(imageId);
            } else {
                this.selectedImages.delete(imageId);
            }
            
            this.updateImageSelectionUI(imageId, isChecked);
        });
        
        this.updateBulkActionsState();
    }

    handleSortEnd(event) {
        this.updateImageOrder();
    }

    updateImageSelectionUI(imageId, isSelected) {
        const imageItem = this.container.querySelector(`[data-image-id="${imageId}"]`);
        if (imageItem) {
            if (isSelected) {
                imageItem.classList.add('selected');
            } else {
                imageItem.classList.remove('selected');
            }
        }
    }

    updateBulkActionsState() {
        const hasSelection = this.selectedImages.size > 0;
        const bulkActions = this.container.querySelectorAll('.bulk-action-btn');
        
        bulkActions.forEach(btn => {
            btn.disabled = !hasSelection;
        });
        
        // Update selection count display
        const selectionCount = this.container.querySelector('.selection-count');
        if (selectionCount) {
            selectionCount.textContent = `${this.selectedImages.size} ${this.messages.imagesSelected}`;
        }
    }

    updateSelectAllCheckbox() {
        const selectAllCheckbox = this.container.querySelector('#select-all-images');
        const totalImages = this.container.querySelectorAll('.image-checkbox').length;
        const selectedCount = this.selectedImages.size;
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount > 0 && selectedCount === totalImages;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalImages;
        }
    }

    async setCoverImage(imageId) {
        try {
            const response = await this.makeRequest('POST', 
                `${this.options.apiEndpoint}/${this.productId}/images/${imageId}/cover`);
            
            if (response.success) {
                this.updateCoverImageUI(imageId);
                this.showNotification(this.messages.success, 'success');
            } else {
                throw new Error(response.message || 'Hata oluştu');
            }
        } catch (error) {
            console.error('Set cover error:', error);
            this.showNotification(`${this.messages.error}: ${error.message}`, 'error');
        }
    }

    updateCoverImageUI(newCoverId) {
        // Remove cover status from all images
        this.container.querySelectorAll('.cover-badge').forEach(badge => {
            badge.remove();
        });
        
        // Remove cover button disabled state from all buttons
        this.container.querySelectorAll('.set-cover-btn').forEach(btn => {
            btn.disabled = false;
            btn.textContent = 'Ana Görsel Yap';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-warning');
        });
        
        // Add cover badge and update button for new cover image
        const newCoverItem = this.container.querySelector(`[data-image-id="${newCoverId}"]`);
        if (newCoverItem) {
            const badgesContainer = newCoverItem.querySelector('.image-badges');
            if (badgesContainer) {
                const coverBadge = document.createElement('span');
                coverBadge.className = 'badge bg-warning text-dark cover-badge';
                coverBadge.innerHTML = '<i class="fas fa-star me-1"></i>Ana Görsel';
                badgesContainer.appendChild(coverBadge);
            }
            
            const coverButton = newCoverItem.querySelector('.set-cover-btn');
            if (coverButton) {
                coverButton.disabled = true;
                coverButton.textContent = 'Ana Görsel';
                coverButton.classList.remove('btn-warning');
                coverButton.classList.add('btn-success');
            }
        }
    }

    async deleteImage(imageId) {
        if (!confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        try {
            const response = await this.makeRequest('DELETE', 
                `${this.options.apiEndpoint}/${this.productId}/images/${imageId}`);
            
            if (response.success) {
                // Remove image from DOM
                const imageItem = this.container.querySelector(`[data-image-id="${imageId}"]`);
                if (imageItem) {
                    imageItem.remove();
                }
                
                // Remove from selection
                this.selectedImages.delete(imageId);
                this.updateBulkActionsState();
                this.updateSelectAllCheckbox();
                
                this.showNotification(this.messages.success, 'success');
            } else {
                throw new Error(response.message || 'Silme işlemi başarısız');
            }
        } catch (error) {
            console.error('Delete image error:', error);
            this.showNotification(`${this.messages.error}: ${error.message}`, 'error');
        }
    }

    async bulkDeleteImages() {
        if (this.selectedImages.size === 0) {
            this.showNotification(this.messages.noImagesSelected, 'warning');
            return;
        }
        
        if (!confirm(this.messages.confirmDelete)) {
            return;
        }
        
        try {
            const response = await this.makeRequest('POST', 
                `${this.options.apiEndpoint}/${this.productId}/images/bulk-delete`, {
                    image_ids: Array.from(this.selectedImages)
                });
            
            if (response.success) {
                // Remove images from DOM
                this.selectedImages.forEach(imageId => {
                    const imageItem = this.container.querySelector(`[data-image-id="${imageId}"]`);
                    if (imageItem) {
                        imageItem.remove();
                    }
                });
                
                // Clear selection
                this.selectedImages.clear();
                this.updateBulkActionsState();
                this.updateSelectAllCheckbox();
                
                this.showNotification(this.messages.success, 'success');
            } else {
                throw new Error(response.message || 'Silme işlemi başarısız');
            }
        } catch (error) {
            console.error('Bulk delete error:', error);
            this.showNotification(`${this.messages.error}: ${error.message}`, 'error');
        }
    }

    async bulkSetCoverImage() {
        if (this.selectedImages.size === 0) {
            this.showNotification(this.messages.noImagesSelected, 'warning');
            return;
        }
        
        // Use the first selected image as cover
        const coverImageId = Array.from(this.selectedImages)[0];
        await this.setCoverImage(coverImageId);
    }

    openVariantAssignmentModal() {
        if (this.selectedImages.size === 0) {
            this.showNotification(this.messages.noImagesSelected, 'warning');
            return;
        }
        
        const modal = this.container.querySelector('#variant-assignment-modal');
        if (modal) {
            // Reset form
            const variantCheckboxes = modal.querySelectorAll('.variant-checkbox');
            variantCheckboxes.forEach(cb => cb.checked = false);
            
            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    async saveVariantAssignment() {
        const modal = this.container.querySelector('#variant-assignment-modal');
        if (!modal) return;
        
        const selectedVariants = Array.from(modal.querySelectorAll('.variant-checkbox:checked'))
            .map(cb => parseInt(cb.value));
            
        if (selectedVariants.length === 0) {
            this.showNotification(this.messages.noVariantsSelected, 'warning');
            return;
        }
        
        const isVariantSpecific = modal.querySelector('#is-variant-specific').checked;
        
        try {
            const response = await this.makeRequest('POST', 
                `${this.options.apiEndpoint}/${this.productId}/images/associate-variants`, {
                    image_ids: Array.from(this.selectedImages),
                    variant_ids: selectedVariants,
                    is_variant_specific: isVariantSpecific
                });
            
            if (response.success) {
                // Close modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
                
                this.showNotification(this.messages.success, 'success');
            } else {
                throw new Error(response.message || 'Atama işlemi başarısız');
            }
        } catch (error) {
            console.error('Variant assignment error:', error);
            this.showNotification(`${this.messages.error}: ${error.message}`, 'error');
        }
    }

    async updateImageOrder() {
        const imageIds = Array.from(this.container.querySelectorAll('[data-image-id]'))
            .map(el => parseInt(el.dataset.imageId));
            
        if (imageIds.length === 0) return;
        
        try {
            const response = await this.makeRequest('POST', 
                `${this.options.apiEndpoint}/${this.productId}/images/order`, {
                    image_ids: imageIds
                });
            
            if (response.success) {
                this.showNotification(this.messages.success, 'success');
            } else {
                throw new Error(response.message || 'Sıralama güncelleme başarısız');
            }
        } catch (error) {
            console.error('Order update error:', error);
            this.showNotification(`${this.messages.error}: ${error.message}`, 'error');
        }
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
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return response.json();
    }

    showNotification(message, type = 'info') {
        // Create or update notification
        let notification = this.container.querySelector('.image-manager-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'image-manager-notification';
            this.container.appendChild(notification);
        }

        notification.className = `image-manager-notification ${type}`;
        notification.textContent = message;
        notification.style.display = 'block';

        // Auto-hide after 4 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.display = 'none';
            }
        }, 4000);
    }

    // ==================== PUBLIC METHODS ====================

    refresh() {
        // Reset selection
        this.selectedImages.clear();
        this.updateBulkActionsState();
        this.updateSelectAllCheckbox();
        
        // Reinitialize sortable
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }
        this.initializeSortable();
    }

    getSelectedImageCount() {
        return this.selectedImages.size;
    }

    destroy() {
        // Clean up event listeners
        this.container.removeEventListener('click', this.handleImageClick);
        this.container.removeEventListener('click', this.handleBulkActionClick);
        this.container.removeEventListener('click', this.handleVariantAssignmentClick);
        
        // Destroy sortable
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }
        
        console.log('ProductImageManager destroyed');
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductImageManager;
}

// Global registration for direct script usage
if (typeof window !== 'undefined') {
    window.ProductImageManager = ProductImageManager;
}

// Auto-initialize if DOM is ready and container exists
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.querySelector('#image-management-section');
        const productId = container?.dataset?.productId;
        
        if (container && productId) {
            window.productImageManager = new ProductImageManager({
                container: '#image-management-section',
                productId: productId,
                apiEndpoint: '/admin/products'
            });
        }
    });
}