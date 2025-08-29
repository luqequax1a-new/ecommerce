/**
 * Product Form Toggle Component
 * 
 * Features:
 * - Simple/Variable product type toggle
 * - Conditional stock field visibility
 * - Variant generator integration
 * - Form validation handling
 * - Turkish localization
 * 
 * Usage:
 * const formToggle = new ProductFormToggle({
 *     container: '.product-form',
 *     apiEndpoint: '/admin/products'
 * });
 */

class ProductFormToggle {
    constructor(options) {
        this.options = {
            container: '.product-form',
            apiEndpoint: '/admin/products',
            csrfToken: document.querySelector('meta[name=\"csrf-token\"]')?.content || '',
            ...options
        };

        this.container = document.querySelector(this.options.container);
        this.currentProductType = 'simple';
        this.availableAttributes = [];
        this.isInitialized = false;
        
        // Turkish localization
        this.messages = {
            simple: 'Basit Ürün',
            variable: 'Varyantlı Ürün',
            switchingToSimple: 'Basit ürüne geçiliyor...',
            switchingToVariable: 'Varyantlı ürüne geçiliyor...',
            confirmSwitch: 'Ürün tipini değiştirmek istediğinizden emin misiniz?',
            dataLossWarning: 'Bazı veriler kaybolabilir.',
            selectAttributes: 'Varyant özelliklerini seçin',
            generateVariants: 'Varyantları Oluştur',
            variantsGenerated: 'Varyantlar başarıyla oluşturuldu',
            noAttributesSelected: 'Lütfen en az bir özellik seçin',
            loadingAttributes: 'Özellikler yükleniyor...',
            stockFieldLabel: 'Stok Miktarı',
            skuFieldLabel: 'SKU',
            priceFieldLabel: 'Fiyat'
        };

        this.init();
    }

    init() {
        if (!this.container) {
            console.error('ProductFormToggle: Container not found');
            return;
        }

        this.detectCurrentProductType();
        this.loadAvailableAttributes();
        this.createToggleInterface();
        this.bindEvents();
        this.updateFormVisibility();
        
        this.isInitialized = true;
        console.log('ProductFormToggle initialized');
    }

    detectCurrentProductType() {
        const typeField = this.container.querySelector('input[name=\"product_type\"]');
        if (typeField) {
            this.currentProductType = typeField.value || 'simple';
        }
    }

    async loadAvailableAttributes() {
        try {
            const response = await this.makeRequest('GET', `${this.options.apiEndpoint}/attributes/available`);
            this.availableAttributes = response;
        } catch (error) {
            console.error('Failed to load attributes:', error);
            this.availableAttributes = [];
        }
    }

    createToggleInterface() {
        // Find or create product type section
        let typeSection = this.container.querySelector('.product-type-section');
        if (!typeSection) {
            typeSection = this.createProductTypeSection();
        }

        // Create toggle component
        const toggle = this.createToggleComponent();
        typeSection.appendChild(toggle);

        // Create conditional sections
        this.createConditionalSections();
    }

    createProductTypeSection() {
        const section = document.createElement('div');
        section.className = 'product-type-section bg-white shadow rounded-lg p-6 mb-6';
        section.innerHTML = `
            <h3 class=\"text-lg font-medium text-gray-900 mb-4\">Ürün Tipi</h3>
        `;
        
        // Insert after product name section or at beginning
        const nameSection = this.container.querySelector('.product-basic-info') || 
                           this.container.querySelector('form > div:first-child');
        
        if (nameSection && nameSection.nextSibling) {
            this.container.insertBefore(section, nameSection.nextSibling);
        } else {
            this.container.insertBefore(section, this.container.firstChild);
        }
        
        return section;
    }

    createToggleComponent() {
        const toggleContainer = document.createElement('div');
        toggleContainer.className = 'product-type-toggle';
        toggleContainer.innerHTML = `
            <div class=\"flex items-center space-x-4\">
                <div class=\"flex items-center space-x-2\">
                    <input type=\"radio\" 
                           id=\"product_type_simple\" 
                           name=\"product_type\" 
                           value=\"simple\" 
                           class=\"product-type-radio\"
                           ${this.currentProductType === 'simple' ? 'checked' : ''}>
                    <label for=\"product_type_simple\" class=\"text-sm font-medium text-gray-700\">
                        📦 ${this.messages.simple}
                    </label>
                </div>
                <div class=\"flex items-center space-x-2\">
                    <input type=\"radio\" 
                           id=\"product_type_variable\" 
                           name=\"product_type\" 
                           value=\"variable\" 
                           class=\"product-type-radio\"
                           ${this.currentProductType === 'variable' ? 'checked' : ''}>
                    <label for=\"product_type_variable\" class=\"text-sm font-medium text-gray-700\">
                        🔄 ${this.messages.variable}
                    </label>
                </div>
            </div>
            <div class=\"mt-3 text-sm text-gray-600\">
                <div class=\"simple-description ${this.currentProductType === 'simple' ? '' : 'hidden'}\">
                    Tek varyantlı ürün. Stok ve fiyat doğrudan ürün üzerinde tanımlanır.
                </div>
                <div class=\"variable-description ${this.currentProductType === 'variable' ? '' : 'hidden'}\">
                    Çoklu varyantlı ürün. Farklı özellik kombinasyonları için ayrı stok ve fiyat.
                </div>
            </div>
        `;

        return toggleContainer;
    }

    createConditionalSections() {
        this.createSimpleProductSection();
        this.createVariableProductSection();
    }

    createSimpleProductSection() {
        let simpleSection = this.container.querySelector('.simple-product-section');
        if (!simpleSection) {
            simpleSection = document.createElement('div');
            simpleSection.className = 'simple-product-section bg-white shadow rounded-lg p-6 mb-6';
            simpleSection.innerHTML = `
                <h3 class=\"text-lg font-medium text-gray-900 mb-4\">Basit Ürün Bilgileri</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-3 gap-6\">
                    <div>
                        <label for=\"simple_sku\" class=\"block text-sm font-medium text-gray-700\">
                            ${this.messages.skuFieldLabel} *
                        </label>
                        <input type=\"text\" 
                               name=\"simple_sku\" 
                               id=\"simple_sku\" 
                               class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\"
                               placeholder=\"Örn: URUN-001\">
                        <p class=\"mt-1 text-xs text-gray-500\">Benzersiz ürün kodu</p>
                    </div>
                    <div>
                        <label for=\"simple_price\" class=\"block text-sm font-medium text-gray-700\">
                            ${this.messages.priceFieldLabel} (₺) *
                        </label>
                        <input type=\"number\" 
                               name=\"simple_price\" 
                               id=\"simple_price\" 
                               step=\"0.01\" 
                               min=\"0\" 
                               max=\"999999.99\"
                               class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\"
                               placeholder=\"0,00\">
                    </div>
                    <div>
                        <label for=\"stock_quantity\" class=\"block text-sm font-medium text-gray-700\">
                            ${this.messages.stockFieldLabel}
                        </label>
                        <input type=\"number\" 
                               name=\"stock_quantity\" 
                               id=\"stock_quantity\" 
                               step=\"0.001\" 
                               min=\"0\" 
                               max=\"999999999.999\"
                               class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500\"
                               placeholder=\"0,000\">
                    </div>
                </div>
            `;
            
            this.insertSectionAfterTypeSection(simpleSection);
        }
    }

    createVariableProductSection() {
        let variableSection = this.container.querySelector('.variable-product-section');
        if (!variableSection) {
            variableSection = document.createElement('div');
            variableSection.className = 'variable-product-section bg-white shadow rounded-lg p-6 mb-6';
            variableSection.innerHTML = `
                <h3 class=\"text-lg font-medium text-gray-900 mb-4\">Varyantlı Ürün Ayarları</h3>
                <div class=\"space-y-6\">
                    <div class=\"attribute-selection\">
                        <label class=\"block text-sm font-medium text-gray-700 mb-3\">
                            ${this.messages.selectAttributes}
                        </label>
                        <div class=\"attributes-list grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4\">
                            ${this.renderAttributeCheckboxes()}
                        </div>
                    </div>
                    <div class=\"variant-generation\">
                        <button type=\"button\" 
                                class=\"generate-variants-btn bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors\"
                                disabled>
                            ${this.messages.generateVariants}
                        </button>
                        <p class=\"mt-2 text-sm text-gray-600\">
                            Seçilen özellikler için tüm kombinasyonlar oluşturulacak
                        </p>
                    </div>
                    <div class=\"variants-preview hidden\">
                        <h4 class=\"text-md font-medium text-gray-900 mb-3\">Oluşturulacak Varyantlar</h4>
                        <div class=\"variants-list\"></div>
                    </div>
                </div>
            `;
            
            this.insertSectionAfterTypeSection(variableSection);
        }
    }

    renderAttributeCheckboxes() {
        if (!this.availableAttributes.length) {
            return `
                <div class=\"col-span-full text-center py-8 text-gray-500\">
                    <p>${this.messages.loadingAttributes}</p>
                </div>
            `;
        }

        return this.availableAttributes.map(attr => `
            <div class=\"flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50\">
                <input type=\"checkbox\" 
                       id=\"attr_${attr.id}\" 
                       name=\"selected_attributes[]\" 
                       value=\"${attr.id}\" 
                       class=\"attribute-checkbox mt-1\">
                <div class=\"flex-1 min-w-0\">
                    <label for=\"attr_${attr.id}\" class=\"text-sm font-medium text-gray-900 cursor-pointer\">
                        ${attr.name}
                    </label>
                    <p class=\"text-xs text-gray-500 mt-1\">
                        ${attr.values?.length || 0} değer
                    </p>
                    ${attr.values?.length ? `
                        <div class=\"mt-2 flex flex-wrap gap-1\">
                            ${attr.values.slice(0, 3).map(val => `
                                <span class=\"inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded\">
                                    ${val.value}
                                </span>
                            `).join('')}
                            ${attr.values.length > 3 ? `
                                <span class=\"inline-block px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded\">
                                    +${attr.values.length - 3}
                                </span>
                            ` : ''}
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    insertSectionAfterTypeSection(section) {
        const typeSection = this.container.querySelector('.product-type-section');
        if (typeSection && typeSection.nextSibling) {
            this.container.insertBefore(section, typeSection.nextSibling);
        } else {
            this.container.appendChild(section);
        }
    }

    bindEvents() {
        // Product type radio change
        this.container.addEventListener('change', this.handleProductTypeChange.bind(this));
        
        // Attribute selection change
        this.container.addEventListener('change', this.handleAttributeChange.bind(this));
        
        // Generate variants button
        this.container.addEventListener('click', this.handleGenerateVariants.bind(this));
    }

    handleProductTypeChange(event) {
        if (!event.target.matches('.product-type-radio')) {
            return;
        }

        const newType = event.target.value;
        const oldType = this.currentProductType;

        if (newType === oldType) {
            return;
        }

        // Show confirmation if switching away from variable with data
        if (oldType === 'variable' && this.hasVariantData()) {
            const confirmed = confirm(`${this.messages.confirmSwitch}\n${this.messages.dataLossWarning}`);
            if (!confirmed) {
                // Revert radio selection
                this.container.querySelector(`input[value=\"${oldType}\"]`).checked = true;
                return;
            }
        }

        this.currentProductType = newType;
        this.updateFormVisibility();
        this.updateFormValidation();
        
        this.showNotification(
            newType === 'simple' ? this.messages.switchingToSimple : this.messages.switchingToVariable,
            'info'
        );
    }

    handleAttributeChange(event) {
        if (!event.target.matches('.attribute-checkbox')) {
            return;
        }

        this.updateGenerateButton();
        this.updateVariantsPreview();
    }

    async handleGenerateVariants(event) {
        if (!event.target.matches('.generate-variants-btn')) {
            return;
        }

        event.preventDefault();
        
        const selectedAttributes = this.getSelectedAttributes();
        if (selectedAttributes.length === 0) {
            this.showNotification(this.messages.noAttributesSelected, 'warning');
            return;
        }

        try {
            await this.generateVariantCombinations(selectedAttributes);
        } catch (error) {
            console.error('Variant generation error:', error);
            this.showNotification('Varyant oluşturma hatası: ' + error.message, 'error');
        }
    }

    updateFormVisibility() {
        const simpleSection = this.container.querySelector('.simple-product-section');
        const variableSection = this.container.querySelector('.variable-product-section');
        const simpleDesc = this.container.querySelector('.simple-description');
        const variableDesc = this.container.querySelector('.variable-description');

        if (this.currentProductType === 'simple') {
            simpleSection?.classList.remove('hidden');
            variableSection?.classList.add('hidden');
            simpleDesc?.classList.remove('hidden');
            variableDesc?.classList.add('hidden');
        } else {
            simpleSection?.classList.add('hidden');
            variableSection?.classList.remove('hidden');
            simpleDesc?.classList.add('hidden');
            variableDesc?.classList.remove('hidden');
        }
    }

    updateFormValidation() {
        const simpleFields = this.container.querySelectorAll('.simple-product-section input[required]');
        const variableFields = this.container.querySelectorAll('.variable-product-section input[required]');

        if (this.currentProductType === 'simple') {
            simpleFields.forEach(field => field.required = true);
            variableFields.forEach(field => field.required = false);
        } else {
            simpleFields.forEach(field => field.required = false);
            variableFields.forEach(field => field.required = true);
        }
    }

    updateGenerateButton() {
        const generateBtn = this.container.querySelector('.generate-variants-btn');
        const selectedCount = this.getSelectedAttributes().length;
        
        if (generateBtn) {
            generateBtn.disabled = selectedCount === 0;
            generateBtn.textContent = selectedCount > 0 
                ? `${this.messages.generateVariants} (${selectedCount})` 
                : this.messages.generateVariants;
        }
    }

    async updateVariantsPreview() {
        const selectedAttributes = this.getSelectedAttributes();
        const previewSection = this.container.querySelector('.variants-preview');
        const previewList = this.container.querySelector('.variants-list');
        
        if (!previewSection || !previewList) return;

        if (selectedAttributes.length === 0) {
            previewSection.classList.add('hidden');
            return;
        }

        try {
            // Calculate potential combinations count
            let totalCombinations = 1;
            selectedAttributes.forEach(attrId => {
                const attr = this.availableAttributes.find(a => a.id == attrId);
                if (attr && attr.values) {
                    totalCombinations *= attr.values.length;
                }
            });

            previewList.innerHTML = `
                <div class=\"bg-blue-50 border border-blue-200 rounded-lg p-4\">
                    <div class=\"flex items-center space-x-2\">
                        <span class=\"text-blue-600 font-medium\">${totalCombinations}</span>
                        <span class=\"text-sm text-blue-800\">varyant oluşturulacak</span>
                    </div>
                    ${totalCombinations > 100 ? `
                        <p class=\"mt-2 text-xs text-amber-700\">
                            ⚠️ Çok sayıda varyant oluşturulacak. İşlem uzun sürebilir.
                        </p>
                    ` : ''}
                </div>
            `;
            
            previewSection.classList.remove('hidden');
            
        } catch (error) {
            console.error('Preview update error:', error);
        }
    }

    getSelectedAttributes() {
        const checkboxes = this.container.querySelectorAll('.attribute-checkbox:checked');
        return Array.from(checkboxes).map(cb => parseInt(cb.value));
    }

    hasVariantData() {
        // Check if there are existing variants or variant-related data
        const variantInputs = this.container.querySelectorAll('input[name*=\"variant\"]');
        return variantInputs.length > 0 && Array.from(variantInputs).some(input => input.value.trim() !== '');
    }

    async generateVariantCombinations(selectedAttributes) {
        const generateBtn = this.container.querySelector('.generate-variants-btn');
        const originalText = generateBtn.textContent;
        
        generateBtn.disabled = true;
        generateBtn.textContent = 'Oluşturuluyor...';

        try {
            // Get product name for better SKU generation
            const productNameField = this.container.querySelector('input[name="name"]');
            const productName = productNameField ? productNameField.value : '';

            const response = await this.makeRequest('POST', `${this.options.apiEndpoint}/variants/preview-combinations`, {
                attributes: selectedAttributes,
                product_name: productName
            });

            if (response.success) {
                this.showNotification(this.messages.variantsGenerated, 'success');
                // Display the combinations preview
                this.displayVariantCombinations(response.combinations);
                console.log('Generated variants:', response);
            } else {
                throw new Error(response.message || 'Varyant oluşturma başarısız');
            }
            
        } finally {
            generateBtn.disabled = false;
            generateBtn.textContent = originalText;
        }
    }

    /**
     * Display generated variant combinations in the preview area
     */
    displayVariantCombinations(combinations) {
        const previewSection = this.container.querySelector('.variants-preview');
        const previewList = this.container.querySelector('.variants-list');
        
        if (!previewSection || !previewList || !combinations.length) {
            return;
        }

        let html = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-green-600 font-medium">${combinations.length}</span>
                    <span class="text-sm text-green-800">varyant kombinasyonu oluşturuldu</span>
                </div>
                <p class="mt-1 text-xs text-green-700">
                    Bu kombinasyonlar ürün kaydedildiğinde oluşturulacaktır.
                </p>
            </div>
            <div class="space-y-3 max-h-400 overflow-y-auto">
        `;

        combinations.slice(0, 10).forEach((combination, index) => { // Show only first 10 for performance
            html += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h5 class="text-sm font-medium text-gray-900">
                                ${combination.attribute_display || `Varyant ${index + 1}`}
                            </h5>
                            <div class="mt-1 text-xs text-gray-600">
                                SKU: <code class="bg-gray-100 px-1 rounded">${combination.sku || 'Otomatik oluşturulacak'}</code>
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <div class="text-gray-500">Fiyat: ₺0,00</div>
                            <div class="text-gray-500">Stok: 0</div>
                        </div>
                    </div>
                </div>
            `;
        });

        if (combinations.length > 10) {
            html += `
                <div class="text-center py-3">
                    <span class="text-sm text-gray-500">
                        ... ve ${combinations.length - 10} varyant daha
                    </span>
                </div>
            `;
        }

        html += '</div>';
        
        previewList.innerHTML = html;
        previewSection.classList.remove('hidden');
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
        let notification = document.querySelector('.form-toggle-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'form-toggle-notification';
            document.body.appendChild(notification);
        }

        notification.className = `form-toggle-notification ${type}`;
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

    switchProductType(type) {
        if (['simple', 'variable'].includes(type)) {
            this.currentProductType = type;
            const radio = this.container.querySelector(`input[value=\"${type}\"]`);
            if (radio) {
                radio.checked = true;
                this.updateFormVisibility();
                this.updateFormValidation();
            }
        }
    }

    getProductType() {
        return this.currentProductType;
    }

    refreshAttributes() {
        return this.loadAvailableAttributes().then(() => {
            const attributesList = this.container.querySelector('.attributes-list');
            if (attributesList) {
                attributesList.innerHTML = this.renderAttributeCheckboxes();
            }
        });
    }

    destroy() {
        this.container.removeEventListener('change', this.handleProductTypeChange);
        this.container.removeEventListener('change', this.handleAttributeChange);
        this.container.removeEventListener('click', this.handleGenerateVariants);
        
        // Remove notifications
        const notifications = document.querySelectorAll('.form-toggle-notification');
        notifications.forEach(n => n.remove());
        
        console.log('ProductFormToggle destroyed');
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ProductFormToggle;
}

// Global registration for direct script usage
if (typeof window !== 'undefined') {
    window.ProductFormToggle = ProductFormToggle;
}

// Auto-initialize if DOM is ready and form exists
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const productForm = document.querySelector('.product-form, form[data-product-form]');
        
        if (productForm) {
            window.productFormToggle = new ProductFormToggle({
                container: '.product-form, form[data-product-form]',
                apiEndpoint: '/admin/products'
            });
        }
    });
}