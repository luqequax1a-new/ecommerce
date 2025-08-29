/**
 * Cart Tax Calculator
 * Handles tax calculation scenarios for different customer types
 */

class CartTaxCalculator {
    constructor(options = {}) {
        this.options = {
            taxScenarioSelector: '[name="tax-scenario"]',
            amountInputSelector: '#tax-amount',
            calculateButtonSelector: '#calculate-tax',
            resultContainerSelector: '#tax-result',
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
    }
    
    bindEvents() {
        // Calculate tax when button is clicked
        const calculateButton = document.querySelector(this.options.calculateButtonSelector);
        if (calculateButton) {
            calculateButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.calculateTax();
            });
        }
        
        // Calculate tax when amount changes
        const amountInput = document.querySelector(this.options.amountInputSelector);
        if (amountInput) {
            amountInput.addEventListener('input', this.debounce(() => {
                this.calculateTax();
            }, 500));
        }
        
        // Calculate tax when scenario changes
        const scenarioInputs = document.querySelectorAll(this.options.taxScenarioSelector);
        scenarioInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.calculateTax();
            });
        });
    }
    
    async calculateTax() {
        const scenario = document.querySelector(this.options.taxScenarioSelector + ':checked')?.value;
        const amount = parseFloat(document.querySelector(this.options.amountInputSelector)?.value) || 0;
        
        if (!scenario || amount <= 0) {
            return;
        }
        
        try {
            const response = await fetch('/cart/calculate-tax-scenario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    scenario: scenario,
                    amount: amount
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.displayResult(result.data);
            } else {
                this.displayError('Vergi hesaplanırken bir hata oluştu.');
            }
        } catch (error) {
            this.displayError('Vergi hesaplanırken bir hata oluştu: ' + error.message);
        }
    }
    
    displayResult(data) {
        const resultContainer = document.querySelector(this.options.resultContainerSelector);
        if (!resultContainer) return;
        
        const taxRate = (data.effective_rate * 100).toFixed(2);
        const taxAmount = data.tax_amount.toFixed(2);
        const totalWithTax = data.total_with_tax.toFixed(2);
        
        resultContainer.innerHTML = `
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">Vergi Hesaplama Sonucu</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Mal/Hizmet Tutarı:</span>
                        <span class="font-medium">₺${data.base_amount.toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Uygulanan Vergi Oranı:</span>
                        <span class="font-medium">%${taxRate}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Vergi Tutarı:</span>
                        <span class="font-medium">₺${taxAmount}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                        <span>Toplam Tutar (Vergi Dahil):</span>
                        <span class="font-medium">₺${totalWithTax}</span>
                    </div>
                </div>
            </div>
        `;
        
        resultContainer.classList.remove('hidden');
    }
    
    displayError(message) {
        const resultContainer = document.querySelector(this.options.resultContainerSelector);
        if (!resultContainer) return;
        
        resultContainer.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Hata</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>${message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        resultContainer.classList.remove('hidden');
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.cartTaxCalculator = new CartTaxCalculator();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CartTaxCalculator;
}