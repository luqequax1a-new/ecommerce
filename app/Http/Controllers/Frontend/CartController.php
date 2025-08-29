<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\TaxCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    protected $cartService;
    protected $taxService;

    public function __construct(CartService $cartService, TaxCalculationService $taxService)
    {
        $this->cartService = $cartService;
        $this->taxService = $taxService;
    }

    /**
     * Display the cart page with tax calculation scenarios
     */
    public function index()
    {
        // Get cart items from session or database
        $cartItems = $this->getCartItems();
        
        // Calculate cart totals with taxes
        $cartTotals = $this->cartService->calculateCartTotals($cartItems, [
            'customer_type' => 'individual', // Default to individual
            'country_code' => 'TR',
            'include_shipping' => false
        ]);
        
        // Format tax breakdown for display
        $formattedTaxBreakdown = $this->cartService->formatTaxBreakdown($cartTotals['tax_breakdown']);
        
        return view('frontend.cart.index', compact('cartTotals', 'formattedTaxBreakdown'));
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $quantity = $request->input('quantity');

        // Get product and variant
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::find($variantId) : null;

        // Add to cart session
        $cart = $this->getCart();
        $cartItemId = $variantId ? "{$productId}_{$variantId}" : $productId;
        
        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] += $quantity;
        } else {
            $cart[$cartItemId] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $variant ? $variant->price : $product->price
            ];
        }

        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => __('Ürün sepete eklendi'),
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $quantity = $request->input('quantity');
        $cart = $this->getCart();

        if (isset($cart[$itemId])) {
            if ($quantity > 0) {
                $cart[$itemId]['quantity'] = $quantity;
            } else {
                unset($cart[$itemId]);
            }

            Session::put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => __('Sepet güncellendi'),
                'cart_count' => array_sum(array_column($cart, 'quantity'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Ürün bulunamadı')
        ], 404);
    }

    /**
     * Remove item from cart
     */
    public function removeItem($itemId)
    {
        $cart = $this->getCart();
        
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => __('Ürün sepetten kaldırıldı'),
                'cart_count' => array_sum(array_column($cart, 'quantity'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Ürün bulunamadı')
        ], 404);
    }

    /**
     * Calculate tax for different scenarios
     */
    public function calculateTaxScenario(Request $request)
    {
        $request->validate([
            'scenario' => 'required|in:individual,company,export',
            'amount' => 'required|numeric|min:0'
        ]);

        $scenario = $request->input('scenario');
        $amount = $request->input('amount');

        // Set conditions based on scenario
        $conditions = [
            'country_code' => 'TR',
            'order_amount' => $amount
        ];

        switch ($scenario) {
            case 'individual':
                $conditions['customer_type'] = 'individual';
                break;
            case 'company':
                $conditions['customer_type'] = 'company';
                break;
            case 'export':
                $conditions['is_export'] = true;
                break;
        }

        // Calculate tax using the service
        $taxResult = $this->taxService->calculateTax($amount, $conditions);

        return response()->json([
            'success' => true,
            'data' => [
                'base_amount' => $amount,
                'tax_amount' => $taxResult['tax_amount'],
                'total_with_tax' => $taxResult['total_with_tax'],
                'effective_rate' => $taxResult['effective_rate'],
                'tax_class' => $taxResult['tax_class_name']
            ]
        ]);
    }

    /**
     * Toggle tax display settings
     */
    public function toggleTaxDisplay(Request $request)
    {
        $request->validate([
            'display_type' => 'required|in:inclusive,exclusive'
        ]);

        Session::put('tax_display_type', $request->input('display_type'));

        return response()->json([
            'success' => true,
            'message' => __('Vergi görünümü ayarı güncellendi')
        ]);
    }

    /**
     * Get cart items from session
     */
    protected function getCartItems()
    {
        $cart = $this->getCart();
        $cartItems = [];

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }
        }

        return $cartItems;
    }

    /**
     * Get cart from session
     */
    protected function getCart()
    {
        return Session::get('cart', []);
    }
}