/**
 * PrestaShop-style Quick Edit Component
 * 
 * Features:
 * - Inline editing for product fields (is_active, featured, category, price, stock, sort_order, brand, SEO title/slug)
 * - Bulk operations with multi-select
 * - Turkish localization
 * - Error handling and validation
 * - ARIA accessibility
 * - Debounced input for performance
 * 
 * Usage:
 * const quickEdit = new ProductQuickEdit({
 *     container: '.products-table',
 *     apiEndpoint: '/admin/products',
 *     csrfToken: document.querySelector('meta[name=\"csrf-token\"]').content
 * });
 */

class ProductQuickEdit {
    constructor(options) {
        this.options = {
            container: '.products-table',
            apiEndpoint: '/admin/products',
            csrfToken: '',
            debounceDelay: 500,
            ...options
        };

        this.container = document.querySelector(this.options.container);
        this.selectedProducts = new Set();
        this.isLoading = false;
        this.dropdownData = null;
        this.debounceTimers = new Map();
        
        // Turkish localization
        this.messages = {
            success: 'Başarıyla güncellendi',
            error: 'Güncelleme hatası',
            loading: 'Güncelleniyor...',
            confirm: 'Emin misiniz?',
            selectProducts: 'Lütfen ürün seçin',
            bulkUpdateSuccess: '{count} ürün başarıyla güncellendi',
            bulkUpdateError: 'Bazı ürünler güncellenemedi',
            invalidValue: 'Geçersiz değer',
            networkError: 'Ağ hatası. Lütfen tekrar deneyin.',
            unauthorized: 'Bu işlem için yetkiniz bulunmuyor'
        };

        this.init();
    }

    init() {
        if (!this.container) {
            console.error('ProductQuickEdit: Container not found');
            return;
        }

        this.loadDropdownData();
        this.bindEvents();
        this.initializeBulkActions();
        this.makeFieldsEditable();
        
        console.log('ProductQuickEdit initialized');
    }

    async loadDropdownData() {
        try {
            const response = await this.makeRequest('GET', `${this.options.apiEndpoint}/quick-edit-options`);
            this.dropdownData = response;
        } catch (error) {
            console.error('Failed to load dropdown data:', error);
        }
    }

    bindEvents() {
        // Bulk selection events
        this.container.addEventListener('change', this.handleCheckboxChange.bind(this));
        
        // Quick edit events - using event delegation
        this.container.addEventListener('click', this.handleFieldClick.bind(this));
        this.container.addEventListener('keydown', this.handleKeydown.bind(this));
        this.container.addEventListener('blur', this.handleFieldBlur.bind(this), true);
        
        // Bulk action events
        document.addEventListener('click', this.handleBulkAction.bind(this));
    }

    makeFieldsEditable() {
        const editableFields = this.container.querySelectorAll('[data-quick-edit]');
        
        editableFields.forEach(field => {
            const fieldType = field.dataset.quickEdit;
            const productId = this.getProductId(field);
            
            if (!productId) {
                console.warn('Product ID not found for field:', field);
                return;
            }

            // Add ARIA attributes for accessibility
            field.setAttribute('tabindex', '0');
            field.setAttribute('role', 'button');
            field.setAttribute('aria-label', `${fieldType} düzenle`);
            
            // Add visual indicator
            field.classList.add('quick-editable');
            
            // Add tooltip
            field.title = 'Düzenlemek için tıklayın';
        });
    }

    handleFieldClick(event) {
        const field = event.target.closest('[data-quick-edit]');
        if (!field || field.classList.contains('editing')) {
            return;
        }

        event.preventDefault();
        this.editField(field);
    }

    handleKeydown(event) {
        const field = event.target.closest('[data-quick-edit]');
        if (!field) return;

        // Enter or Space to edit
        if ((event.key === 'Enter' || event.key === ' ') && !field.classList.contains('editing')) {
            event.preventDefault();
            this.editField(field);
        }
        
        // Escape to cancel editing
        if (event.key === 'Escape' && field.classList.contains('editing')) {
            event.preventDefault();
            this.cancelEdit(field);
        }
        
        // Enter to save (for input fields)
        if (event.key === 'Enter' && field.classList.contains('editing')) {
            const input = field.querySelector('input, select');
            if (input) {
                event.preventDefault();
                this.saveField(field);
            }
        }
    }

    handleFieldBlur(event) {
        const field = event.target.closest('[data-quick-edit]');
        if (!field || !field.classList.contains('editing')) {
            return;
        }

        // Delay to allow clicking on dropdown options
        setTimeout(() => {
            if (field.classList.contains('editing') && !field.contains(document.activeElement)) {
                this.saveField(field);
            }
        }, 150);
    }

    editField(field) {
        if (this.isLoading || field.classList.contains('editing')) {
            return;
        }

        const fieldType = field.dataset.quickEdit;
        const currentValue = field.dataset.value || field.textContent.trim();
        const productId = this.getProductId(field);

        // Store original state
        field.dataset.originalValue = currentValue;
        field.dataset.originalContent = field.innerHTML;
        field.classList.add('editing');

        // Create appropriate input based on field type
        const input = this.createFieldInput(fieldType, currentValue, productId);
        
        // Replace content with input
        field.innerHTML = '';
        field.appendChild(input);
        
        // Focus and select content
        input.focus();
        if (input.type === 'text') {
            input.select();
        }
        
        // Add loading indicator
        this.addLoadingIndicator(field);
    }

    createFieldInput(fieldType, currentValue, productId) {
        switch (fieldType) {
            case 'is_active':
            case 'featured':
                return this.createBooleanToggle(fieldType, currentValue);
            
            case 'category_id':
                return this.createCategorySelect(currentValue);
            
            case 'brand_id':
                return this.createBrandSelect(currentValue);
            
            case 'price':
            case 'stock_quantity':
                return this.createNumberInput(fieldType, currentValue);
            
            case 'sort_order':
                return this.createNumberInput('sort_order', currentValue, { min: 0, max: 9999 });
            
            case 'meta_title':
            case 'slug':
                return this.createTextInput(fieldType, currentValue);
            
            default:
                return this.createTextInput(fieldType, currentValue);
        }
    }

    createBooleanToggle(fieldType, currentValue) {
        const select = document.createElement('select');
        select.className = 'quick-edit-select';
        
        const isActive = currentValue === 'true' || currentValue === '1' || currentValue === 'Aktif' || currentValue === 'Evet';
        
        const options = fieldType === 'is_active' 
            ? [{ value: '1', text: 'Aktif' }, { value: '0', text: 'Pasif' }]
            : [{ value: '1', text: 'Evet' }, { value: '0', text: 'Hayır' }];
        
        options.forEach(option => {
            const optionEl = document.createElement('option');
            optionEl.value = option.value;
            optionEl.textContent = option.text;
            optionEl.selected = (option.value === '1') === isActive;
            select.appendChild(optionEl);
        });
        
        return select;
    }

    createCategorySelect(currentValue) {
        const select = document.createElement('select');
        select.className = 'quick-edit-select';
        
        // Add empty option
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = 'Kategorisiz';
        select.appendChild(emptyOption);
        
        if (this.dropdownData?.categories) {
            this.dropdownData.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                option.selected = String(category.id) === String(currentValue);
                select.appendChild(option);
            });
        }
        
        return select;
    }

    createBrandSelect(currentValue) {
        const select = document.createElement('select');
        select.className = 'quick-edit-select';
        
        // Add empty option
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = 'Marksız';
        select.appendChild(emptyOption);
        
        if (this.dropdownData?.brands) {
            this.dropdownData.brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.name;
                option.selected = String(brand.id) === String(currentValue);
                select.appendChild(option);
            });
        }
        
        return select;
    }

    createNumberInput(fieldType, currentValue, options = {}) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'quick-edit-input';
        input.value = this.parseNumericValue(currentValue);
        
        if (fieldType === 'price') {
            input.min = '0';
            input.max = '999999.99';
            input.step = '0.01';
            input.placeholder = '0,00';
        } else if (fieldType === 'stock_quantity') {
            input.min = '0';
            input.max = '999999999.999';
            input.step = '0.001';
            input.placeholder = '0,000';
        } else {
            input.min = options.min || '0';
            input.max = options.max || '9999';
            input.step = '1';
        }
        
        return input;
    }

    createTextInput(fieldType, currentValue) {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'quick-edit-input';
        input.value = currentValue;
        input.maxLength = fieldType === 'meta_title' ? 255 : 255;
        
        return input;
    }

    parseNumericValue(value) {
        if (typeof value === 'number') {
            return value;
        }
        
        // Handle Turkish number format (₺12.345,67)
        const numericValue = String(value)
            .replace(/[₺\\s]/g, '') // Remove currency and spaces
            .replace(/\\./g, '') // Remove thousand separators
            .replace(/,/g, '.'); // Replace decimal comma with dot
        
        return parseFloat(numericValue) || 0;
    }

    addLoadingIndicator(field) {
        const indicator = document.createElement('span');
        indicator.className = 'quick-edit-loading';
        indicator.innerHTML = '⟳';
        indicator.style.display = 'none';
        field.appendChild(indicator);
    }

    async saveField(field) {
        if (this.isLoading) {
            return;
        }

        const input = field.querySelector('input, select');
        if (!input) {
            this.cancelEdit(field);
            return;
        }

        const fieldType = field.dataset.quickEdit;
        const newValue = input.value;
        const originalValue = field.dataset.originalValue;
        const productId = this.getProductId(field);

        // Check if value actually changed
        if (String(newValue) === String(originalValue)) {
            this.cancelEdit(field);
            return;
        }

        // Show loading state
        this.isLoading = true;
        this.showLoadingState(field, true);

        try {
            const response = await this.makeRequest('PATCH', `${this.options.apiEndpoint}/${productId}/quick-update`, {
                field: fieldType,
                value: newValue
            });

            if (response.success) {
                // Update field with new value
                field.dataset.value = response.value;
                field.innerHTML = response.display_value;
                field.classList.remove('editing');
                
                // Show success feedback
                this.showNotification(this.messages.success, 'success');
                
                // Add updated class for visual feedback
                field.classList.add('just-updated');
                setTimeout(() => field.classList.remove('just-updated'), 2000);
                
            } else {
                throw new Error(response.message || this.messages.error);
            }

        } catch (error) {
            console.error('Save field error:', error);
            this.showNotification(error.message || this.messages.error, 'error');
            this.cancelEdit(field);
        } finally {
            this.isLoading = false;
            this.showLoadingState(field, false);
        }
    }

    cancelEdit(field) {
        field.innerHTML = field.dataset.originalContent;
        field.classList.remove('editing');
        delete field.dataset.originalValue;
        delete field.dataset.originalContent;
    }

    showLoadingState(field, isLoading) {
        const indicator = field.querySelector('.quick-edit-loading');
        if (indicator) {
            indicator.style.display = isLoading ? 'inline' : 'none';
        }
        
        const input = field.querySelector('input, select');
        if (input) {
            input.disabled = isLoading;
        }
    }

    getProductId(element) {
        const row = element.closest('[data-product-id]');
        return row ? row.dataset.productId : null;
    }

    // ==================== BULK OPERATIONS ====================

    initializeBulkActions() {
        this.createBulkActionsBar();
        this.updateBulkActionsVisibility();
    }

    createBulkActionsBar() {
        if (document.querySelector('.bulk-actions-bar')) {
            return; // Already exists
        }

        const bar = document.createElement('div');
        bar.className = 'bulk-actions-bar';
        bar.style.display = 'none';
        bar.innerHTML = `
            <div class=\"bulk-actions-content\">
                <span class=\"selected-count\">0 ürün seçildi</span>
                <div class=\"bulk-actions\">
                    <button type=\"button\" class=\"bulk-action-btn\" data-bulk-action=\"activate\">
                        ✓ Aktifleştir
                    </button>
                    <button type=\"button\" class=\"bulk-action-btn\" data-bulk-action=\"deactivate\">
                        ✗ Pasifleştir
                    </button>
                    <button type=\"button\" class=\"bulk-action-btn\" data-bulk-action=\"feature\">
                        ★ Öne Çıkar
                    </button>
                    <button type=\"button\" class=\"bulk-action-btn\" data-bulk-action=\"unfeature\">
                        ☆ Öne Çıkarma
                    </button>
                    <div class=\"bulk-action-dropdown\">
                        <button type=\"button\" class=\"bulk-action-btn dropdown-toggle\">
                            Kategori Değiştir ▼
                        </button>
                        <select class=\"bulk-category-select\" data-bulk-action=\"move_category\" style=\"display: none;\">
                            <option value=\"\">Kategorisiz</option>
                        </select>
                    </div>
                    <div class=\"bulk-action-dropdown\">
                        <button type=\"button\" class=\"bulk-action-btn dropdown-toggle\">
                            Marka Ata ▼
                        </button>
                        <select class=\"bulk-brand-select\" data-bulk-action=\"assign_brand\" style=\"display: none;\">
                            <option value=\"\">Marksız</option>
                        </select>
                    </div>
                    <div class=\"bulk-price-adjustment\">
                        <input type=\"number\" class=\"price-percentage\" placeholder=\"%\" min=\"-99\" max=\"999\" step=\"1\">
                        <button type=\"button\" class=\"bulk-action-btn\" data-bulk-action=\"price_adjustment\">
                            Fiyat Ayarla
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.container.parentNode.insertBefore(bar, this.container);
        this.populateBulkDropdowns();
    }

    populateBulkDropdowns() {
        if (!this.dropdownData) return;

        // Populate category dropdown
        const categorySelect = document.querySelector('.bulk-category-select');
        if (categorySelect && this.dropdownData.categories) {
            this.dropdownData.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }

        // Populate brand dropdown
        const brandSelect = document.querySelector('.bulk-brand-select');
        if (brandSelect && this.dropdownData.brands) {
            this.dropdownData.brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.name;
                brandSelect.appendChild(option);
            });
        }
    }

    handleCheckboxChange(event) {
        if (!event.target.matches('input[type=\"checkbox\"][data-product-id]')) {
            return;
        }

        const productId = event.target.dataset.productId;
        
        if (event.target.checked) {
            this.selectedProducts.add(productId);
        } else {
            this.selectedProducts.delete(productId);
        }

        this.updateBulkActionsVisibility();
    }

    updateBulkActionsVisibility() {
        const bar = document.querySelector('.bulk-actions-bar');
        const countElement = document.querySelector('.selected-count');
        
        if (!bar || !countElement) return;

        const count = this.selectedProducts.size;
        
        if (count > 0) {
            bar.style.display = 'block';
            countElement.textContent = `${count} ürün seçildi`;
        } else {
            bar.style.display = 'none';
        }
    }

    handleBulkAction(event) {
        const actionBtn = event.target.closest('[data-bulk-action]');
        if (!actionBtn) return;

        event.preventDefault();
        
        const action = actionBtn.dataset.bulkAction;
        this.executeBulkAction(action);
    }

    async executeBulkAction(action) {
        if (this.selectedProducts.size === 0) {
            this.showNotification(this.messages.selectProducts, 'warning');
            return;
        }

        if (this.isLoading) {
            return;
        }

        // Confirm dangerous actions
        if (['deactivate', 'price_adjustment'].includes(action)) {
            if (!confirm(this.messages.confirm)) {
                return;
            }
        }

        const requestData = {
            product_ids: Array.from(this.selectedProducts),
            action: action
        };

        // Add action-specific data
        if (action === 'move_category') {
            const select = document.querySelector('.bulk-category-select');
            requestData.value = select.value;
        } else if (action === 'assign_brand') {
            const select = document.querySelector('.bulk-brand-select');
            requestData.value = select.value;
        } else if (action === 'price_adjustment') {
            const input = document.querySelector('.price-percentage');
            const percentage = parseFloat(input.value);
            if (isNaN(percentage)) {
                this.showNotification('Geçerli bir yüzde değeri girin', 'error');
                return;
            }
            requestData.percentage = percentage;
        }

        this.isLoading = true;
        this.showBulkLoadingState(true);

        try {
            const response = await this.makeRequest('PATCH', `${this.options.apiEndpoint}/bulk-update`, requestData);
            
            if (response.success) {
                const message = this.messages.bulkUpdateSuccess.replace('{count}', response.updated_count);
                this.showNotification(message, 'success');
                
                // Clear selections and refresh view
                this.clearSelections();
                
                // Optionally reload the page or update the UI
                setTimeout(() => window.location.reload(), 1500);
                
            } else {
                throw new Error(response.message || this.messages.bulkUpdateError);
            }

        } catch (error) {
            console.error('Bulk action error:', error);
            this.showNotification(error.message || this.messages.error, 'error');
        } finally {
            this.isLoading = false;
            this.showBulkLoadingState(false);
        }
    }

    showBulkLoadingState(isLoading) {
        const buttons = document.querySelectorAll('.bulk-action-btn');
        buttons.forEach(btn => {
            btn.disabled = isLoading;
        });
    }

    clearSelections() {
        this.selectedProducts.clear();
        
        const checkboxes = document.querySelectorAll('input[type=\"checkbox\"][data-product-id]');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        
        this.updateBulkActionsVisibility();
    }

    // ==================== UTILITY METHODS ====================

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
                throw new Error(this.messages.unauthorized);
            } else if (response.status >= 500) {
                throw new Error(this.messages.networkError);
            }
        }

        return response.json();
    }

    showNotification(message, type = 'info') {
        // Create or update notification
        let notification = document.querySelector('.quick-edit-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'quick-edit-notification';
            document.body.appendChild(notification);
        }

        notification.className = `quick-edit-notification ${type}`;
        notification.textContent = message;
        notification.style.display = 'block';

        // Auto-hide after 4 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 4000);
    }

    // ==================== PUBLIC METHODS ====================

    refresh() {
        this.loadDropdownData();
        this.makeFieldsEditable();
        this.populateBulkDropdowns();
    }

    destroy() {
        // Remove event listeners
        this.container.removeEventListener('change', this.handleCheckboxChange);
        this.container.removeEventListener('click', this.handleFieldClick);
        this.container.removeEventListener('keydown', this.handleKeydown);
        this.container.removeEventListener('blur', this.handleFieldBlur);
        document.removeEventListener('click', this.handleBulkAction);

        // Remove bulk actions bar
        const bar = document.querySelector('.bulk-actions-bar');
        if (bar) {
            bar.remove();
        }

        // Clear timers
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();

        console.log('ProductQuickEdit destroyed');
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductQuickEdit;
}

// Global registration for direct script usage
if (typeof window !== 'undefined') {
    window.ProductQuickEdit = ProductQuickEdit;
}

// Auto-initialize if DOM is ready and container exists
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.querySelector('.products-table');
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content;
        
        if (container && csrfToken) {
            new ProductQuickEdit({
                container: '.products-table',
                apiEndpoint: '/admin/products',
                csrfToken: csrfToken
            });
        }
    });
}