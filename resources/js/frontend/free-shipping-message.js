/**
 * Free Shipping Threshold Message Component
 * Displays \"₺X Daha → Kargo Ücretsiz\" message in mini cart, cart, and checkout
 * Features: responsive design, aria-live updates, debounced updates, Turkish formatting
 */
class FreeShippingMessage {
    constructor(options = {}) {
        this.options = {
            debounceDelay: 100,
            apiEndpoint: '/api/shipping/calculate',
            selectors: {
                containers: '.free-shipping-message',
                cartSubtotal: '[data-cart-subtotal]',
                cartItems: '[data-cart-items]'
            },
            messages: {
                remaining: 'Kargo ücretsiz için ₺{remaining} daha alışveriş yapın.',
                achieved: 'Kargo ücretsiz.',
                loading: 'Hesaplanıyor...'
            },
            ...options
        };

        this.debounceTimer = null;
        this.currentSubtotal = 0;
        this.isInitialized = false;
        this.settings = null;

        this.init();
    }

    /**
     * Initialize the component
     */
    async init() {
        if (this.isInitialized) return;

        try {
            await this.loadShippingSettings();
            this.createMessageContainers();
            this.bindEvents();
            this.updateMessage();
            
            this.isInitialized = true;
            console.log('FreeShippingMessage initialized successfully');
        } catch (error) {
            console.error('Failed to initialize FreeShippingMessage:', error);
        }
    }

    /**
     * Load shipping settings from API
     */
    async loadShippingSettings() {
        try {
            const response = await fetch('/admin/shipping/settings/configuration', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.settings = data.data.configuration;
            } else {
                throw new Error(data.message || 'Failed to load settings');
            }
        } catch (error) {
            console.error('Error loading shipping settings:', error);
            // Use fallback settings
            this.settings = {
                free_shipping: {
                    enabled: true,
                    threshold: 300,
                    threshold_formatted: '₺300,00'
                }
            };
        }
    }

    /**
     * Create message containers if they don't exist
     */
    createMessageContainers() {
        const containers = document.querySelectorAll(this.options.selectors.containers);
        
        if (containers.length === 0) {
            // Create containers in common locations
            this.createContainerInMiniCart();
            this.createContainerInCart();
            this.createContainerInCheckout();
        } else {
            // Initialize existing containers
            containers.forEach(container => {
                this.initializeContainer(container);
            });
        }
    }

    /**
     * Create container in mini cart
     */
    createContainerInMiniCart() {
        const miniCart = document.querySelector('.mini-cart, .cart-dropdown, #mini-cart');
        if (miniCart) {
            const container = this.createContainer('mini-cart');
            const insertPoint = miniCart.querySelector('.mini-cart-header, .cart-header') || miniCart.firstChild;
            if (insertPoint) {
                insertPoint.parentNode.insertBefore(container, insertPoint.nextSibling);
            } else {
                miniCart.appendChild(container);
            }
        }
    }

    /**
     * Create container in cart page
     */
    createContainerInCart() {
        const cartSummary = document.querySelector('.cart-summary, .cart-totals, #cart-summary');
        if (cartSummary) {
            const container = this.createContainer('cart-page');
            cartSummary.insertBefore(container, cartSummary.firstChild);
        }
    }

    /**
     * Create container in checkout
     */
    createContainerInCheckout() {
        const checkoutSummary = document.querySelector('.checkout-summary, .order-summary, #checkout-summary');
        if (checkoutSummary) {
            const container = this.createContainer('checkout');
            checkoutSummary.insertBefore(container, checkoutSummary.firstChild);
        }
    }

    /**
     * Create a message container element
     */
    createContainer(location) {
        const container = document.createElement('div');
        container.className = `free-shipping-message free-shipping-message--${location}`;
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-label', 'Ücretsiz kargo durumu');
        container.setAttribute('data-location', location);
        
        this.initializeContainer(container);
        return container;
    }

    /**
     * Initialize container with base styling and structure
     */
    initializeContainer(container) {
        if (container.classList.contains('initialized')) return;
        
        // Add CSS classes for styling
        container.classList.add(
            'free-shipping-message',
            'bg-gradient-to-r',
            'from-green-50',
            'to-blue-50',
            'border',
            'border-green-200',
            'rounded-lg',
            'p-3',
            'mb-4',
            'text-sm',
            'font-medium',
            'text-green-800',
            'transition-all',
            'duration-300'
        );
        
        // Create inner structure
        container.innerHTML = `
            <div class=\"flex items-center justify-between\">
                <div class=\"flex items-center\">
                    <svg class=\"w-4 h-4 mr-2 text-green-600\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                        <path d=\"M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z\"></path>
                    </svg>
                    <span class=\"message-text\">Yükleniyor...</span>
                </div>
                <div class=\"message-icon\">
                    <svg class=\"w-4 h-4 text-green-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                    </svg>
                </div>
            </div>
        `;
        
        container.classList.add('initialized');
        
        // Add responsive classes based on location
        const location = container.getAttribute('data-location');
        if (location === 'mini-cart') {
            container.classList.add('text-xs', 'p-2');
        } else if (location === 'checkout') {
            container.classList.add('border-2', 'border-green-300');
        }
    }

    /**
     * Bind events for cart updates
     */
    bindEvents() {
        // Listen for cart updates
        document.addEventListener('cart:updated', () => {
            this.debouncedUpdate();
        });
        
        document.addEventListener('cart:item:added', () => {
            this.debouncedUpdate();
        });
        
        document.addEventListener('cart:item:removed', () => {
            this.debouncedUpdate();
        });
        
        document.addEventListener('cart:item:quantity:changed', () => {
            this.debouncedUpdate();
        });
        
        // Listen for subtotal changes in DOM
        const subtotalElements = document.querySelectorAll(this.options.selectors.cartSubtotal);
        subtotalElements.forEach(element => {
            if (window.MutationObserver) {
                const observer = new MutationObserver(() => {
                    this.debouncedUpdate();
                });
                observer.observe(element, { childList: true, characterData: true, subtree: true });
            }
        });
        
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.debouncedUpdate();
            }
        });
    }

    /**
     * Debounced update method
     */
    debouncedUpdate() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            this.updateMessage();
        }, this.options.debounceDelay);
    }

    /**
     * Update the message based on current cart state
     */
    async updateMessage() {
        if (!this.settings || !this.settings.free_shipping.enabled) {
            this.hideAllMessages();
            return;
        }

        const subtotal = this.getCurrentSubtotal();
        const threshold = this.settings.free_shipping.threshold;
        
        if (threshold <= 0) {
            this.hideAllMessages();
            return;
        }

        const remaining = Math.max(0, threshold - subtotal);
        const messageText = this.generateMessageText(remaining);
        
        this.updateAllContainers(messageText, remaining === 0);
    }

    /**
     * Get current cart subtotal
     */
    getCurrentSubtotal() {
        // Try to get from data attribute
        const subtotalElement = document.querySelector(this.options.selectors.cartSubtotal);
        if (subtotalElement) {
            const value = subtotalElement.getAttribute('data-value') || 
                         subtotalElement.getAttribute('data-subtotal') ||
                         subtotalElement.textContent;
            
            // Parse Turkish formatted number
            const parsed = this.parseFormattedNumber(value);
            if (!isNaN(parsed)) {
                return parsed;
            }
        }

        // Try to get from cart items
        const cartItems = document.querySelectorAll(this.options.selectors.cartItems);
        let total = 0;
        
        cartItems.forEach(item => {
            const price = parseFloat(item.getAttribute('data-price') || 0);
            const quantity = parseInt(item.getAttribute('data-quantity') || 1);
            total += price * quantity;
        });

        return total;
    }

    /**
     * Parse Turkish formatted number (₺1.234,56)
     */
    parseFormattedNumber(value) {
        if (!value) return 0;
        
        // Remove currency symbols and spaces
        let cleaned = value.toString().replace(/[₺\\s]/g, '');
        
        // Handle Turkish number format (1.234,56)
        if (cleaned.includes(',') && cleaned.includes('.')) {
            // Format: 1.234,56
            cleaned = cleaned.replace(/\\./g, '').replace(',', '.');
        } else if (cleaned.includes(',')) {
            // Format: 1234,56
            cleaned = cleaned.replace(',', '.');
        }
        
        return parseFloat(cleaned) || 0;
    }

    /**
     * Generate message text based on remaining amount
     */
    generateMessageText(remaining) {
        if (remaining > 0) {
            const formattedRemaining = this.formatCurrency(remaining);
            return this.options.messages.remaining.replace('{remaining}', formattedRemaining);
        } else {
            return this.options.messages.achieved;
        }
    }

    /**
     * Format currency in Turkish format
     */
    formatCurrency(amount) {
        return '₺' + new Intl.NumberFormat('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    /**
     * Update all message containers
     */
    updateAllContainers(messageText, isAchieved) {
        const containers = document.querySelectorAll(this.options.selectors.containers);
        
        containers.forEach(container => {
            this.updateContainer(container, messageText, isAchieved);
        });
    }

    /**
     * Update individual container
     */
    updateContainer(container, messageText, isAchieved) {
        const messageElement = container.querySelector('.message-text');
        const iconElement = container.querySelector('.message-icon svg');
        
        if (messageElement) {
            messageElement.textContent = messageText;
        }
        
        // Update styling based on achievement status
        if (isAchieved) {
            container.classList.remove('from-green-50', 'to-blue-50', 'border-green-200', 'text-green-800');
            container.classList.add('from-green-100', 'to-green-50', 'border-green-300', 'text-green-900');
            
            if (iconElement) {
                iconElement.innerHTML = `
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                `;
            }
        } else {
            container.classList.remove('from-green-100', 'to-green-50', 'border-green-300', 'text-green-900');
            container.classList.add('from-green-50', 'to-blue-50', 'border-green-200', 'text-green-800');
            
            if (iconElement) {
                iconElement.innerHTML = `
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 7h8m0 0v8m0-8l-8 8-4-4-6 6\"></path>
                `;
            }
        }
        
        // Show container
        container.style.display = 'block';
        container.setAttribute('aria-hidden', 'false');
    }

    /**
     * Hide all message containers
     */
    hideAllMessages() {
        const containers = document.querySelectorAll(this.options.selectors.containers);
        containers.forEach(container => {
            container.style.display = 'none';
            container.setAttribute('aria-hidden', 'true');
        });
    }

    /**
     * Refresh settings and update message
     */
    async refresh() {
        try {
            await this.loadShippingSettings();
            this.updateMessage();
        } catch (error) {
            console.error('Error refreshing free shipping message:', error);
        }
    }

    /**
     * Destroy the component
     */
    destroy() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        this.hideAllMessages();
        this.isInitialized = false;
    }

    /**
     * Public API method to trigger update
     */
    triggerUpdate(subtotal = null) {
        if (subtotal !== null) {
            this.currentSubtotal = subtotal;
        }
        this.debouncedUpdate();
    }

    /**
     * Public API method to check if free shipping is achieved
     */
    isFreeShippingAchieved(subtotal = null) {
        const currentSubtotal = subtotal || this.getCurrentSubtotal();
        return this.settings && 
               this.settings.free_shipping.enabled && 
               currentSubtotal >= this.settings.free_shipping.threshold;
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FreeShippingMessage;
}

// Auto-initialize if DOM is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.freeShippingMessage = new FreeShippingMessage();
        });
    } else {
        // DOM is already ready
        window.freeShippingMessage = new FreeShippingMessage();
    }
}"