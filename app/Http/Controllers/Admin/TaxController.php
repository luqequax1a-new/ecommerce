<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\TaxRule;
use App\Services\TaxCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxController extends Controller
{
    protected $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Tax management dashboard
     */
    public function index(): View
    {
        $taxClasses = TaxClass::with(['taxRates' => function ($query) {
            $query->active()->effective();
        }])->withCount(['taxRates', 'products'])->get();

        $taxRates = TaxRate::with('taxClass')
                          ->active()
                          ->effective()
                          ->orderBy('priority', 'desc')
                          ->take(10)
                          ->get();

        $stats = [
            'total_classes' => TaxClass::count(),
            'active_classes' => TaxClass::active()->count(),
            'total_rates' => TaxRate::count(),
            'active_rates' => TaxRate::active()->effective()->count(),
            'total_rules' => TaxRule::count(),
            'active_rules' => TaxRule::active()->effective()->count()
        ];

        return view('admin.tax.index', compact('taxClasses', 'taxRates', 'stats'));
    }

    // ==== TAX CLASSES ====

    /**
     * List tax classes
     */
    public function taxClasses(): View
    {
        $taxClasses = TaxClass::with(['taxRates' => function ($query) {
            $query->active()->effective();
        }])
        ->withCount(['taxRates', 'products'])
        ->orderBy('name')
        ->paginate(20);

        return view('admin.tax.classes.index', compact('taxClasses'));
    }

    /**
     * Show tax class creation form
     */
    public function createTaxClass(): View
    {
        return view('admin.tax.classes.create');
    }

    /**
     * Store new tax class
     */
    public function storeTaxClass(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:tax_classes,name',
            'code' => 'required|string|max:50|unique:tax_classes,code',
            'description' => 'nullable|string|max:1000',
            'default_rate' => 'required|numeric|min:0|max:1',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxClass = TaxClass::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tax class created successfully',
                'data' => $taxClass
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show tax class edit form
     */
    public function editTaxClass(TaxClass $taxClass): View
    {
        $taxClass->load(['taxRates' => function ($query) {
            $query->orderBy('priority', 'desc');
        }]);

        return view('admin.tax.classes.edit', compact('taxClass'));
    }

    /**
     * Update tax class
     */
    public function updateTaxClass(Request $request, TaxClass $taxClass): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:tax_classes,name,' . $taxClass->id,
            'code' => 'required|string|max:50|unique:tax_classes,code,' . $taxClass->id,
            'description' => 'nullable|string|max:1000',
            'default_rate' => 'required|numeric|min:0|max:1',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxClass->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tax class updated successfully',
                'data' => $taxClass
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete tax class
     */
    public function destroyTaxClass(TaxClass $taxClass): JsonResponse
    {
        try {
            // Check if tax class is being used
            $productsCount = $taxClass->products()->count();
            $ratesCount = $taxClass->taxRates()->count();

            if ($productsCount > 0 || $ratesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete tax class. It's being used by {$productsCount} products and has {$ratesCount} tax rates."
                ], 400);
            }

            $taxClass->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tax class deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax class: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==== TAX RATES ====

    /**
     * List tax rates
     */
    public function taxRates(): View
    {
        $taxRates = TaxRate::with('taxClass')
                          ->orderBy('priority', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $taxClasses = TaxClass::active()->orderBy('name')->get();

        return view('admin.tax.rates.index', compact('taxRates', 'taxClasses'));
    }

    /**
     * Store new tax rate
     */
    public function storeTaxRate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_class_id' => 'required|exists:tax_classes,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:tax_rates,code',
            'rate' => 'required|numeric|min:0|max:10',
            'type' => 'required|in:percentage,fixed',
            'country_code' => 'required|string|size:2',
            'region' => 'nullable|string|max:100',
            'priority' => 'required|integer|min:0|max:100',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxRate = TaxRate::create($request->all());
            $taxRate->load('taxClass');

            return response()->json([
                'success' => true,
                'message' => 'Tax rate created successfully',
                'data' => $taxRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tax rate
     */
    public function updateTaxRate(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_class_id' => 'required|exists:tax_classes,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:tax_rates,code,' . $taxRate->id,
            'rate' => 'required|numeric|min:0|max:10',
            'type' => 'required|in:percentage,fixed',
            'country_code' => 'required|string|size:2',
            'region' => 'nullable|string|max:100',
            'priority' => 'required|integer|min:0|max:100',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxRate->update($request->all());
            $taxRate->load('taxClass');

            return response()->json([
                'success' => true,
                'message' => 'Tax rate updated successfully',
                'data' => $taxRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete tax rate
     */
    public function destroyTaxRate(TaxRate $taxRate): JsonResponse
    {
        try {
            // Check if tax rate is being used by rules
            $rulesCount = $taxRate->taxRules()->count();

            if ($rulesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete tax rate. It's being used by {$rulesCount} tax rules."
                ], 400);
            }

            $taxRate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tax rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax rate: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==== TAX RULES ====

    /**
     * List tax rules
     */
    public function taxRules(): View
    {
        $taxRules = TaxRule::with(['taxRate.taxClass'])
                          ->orderBy('priority', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $taxRates = TaxRate::with('taxClass')->active()->effective()->get();

        return view('admin.tax.rules.index', compact('taxRules', 'taxRates'));
    }

    /**
     * Store new tax rule
     */
    public function storeTaxRule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'entity_type' => 'required|in:product,category,customer,shipping,payment',
            'entity_id' => 'nullable|integer',
            'country_code' => 'required|string|size:2',
            'region' => 'nullable|string|max:100',
            'postal_code_from' => 'nullable|string|max:10',
            'postal_code_to' => 'nullable|string|max:10',
            'customer_type' => 'nullable|in:individual,company',
            'order_amount_from' => 'nullable|numeric|min:0',
            'order_amount_to' => 'nullable|numeric|min:0',
            'priority' => 'required|integer|min:0|max:100',
            'stop_processing' => 'boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
            'is_active' => 'boolean',
            'conditions' => 'nullable|json',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxRule = TaxRule::create($request->all());
            $taxRule->load('taxRate.taxClass');

            return response()->json([
                'success' => true,
                'message' => 'Tax rule created successfully',
                'data' => $taxRule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax rule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tax rule
     */
    public function updateTaxRule(Request $request, TaxRule $taxRule): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_rate_id' => 'required|exists:tax_rates,id',
            'entity_type' => 'required|in:product,category,customer,shipping,payment',
            'entity_id' => 'nullable|integer',
            'country_code' => 'required|string|size:2',
            'region' => 'nullable|string|max:100',
            'postal_code_from' => 'nullable|string|max:10',
            'postal_code_to' => 'nullable|string|max:10',
            'customer_type' => 'nullable|in:individual,company',
            'order_amount_from' => 'nullable|numeric|min:0',
            'order_amount_to' => 'nullable|numeric|min:0',
            'priority' => 'required|integer|min:0|max:100',
            'stop_processing' => 'boolean',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
            'is_active' => 'boolean',
            'conditions' => 'nullable|json',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxRule->update($request->all());
            $taxRule->load('taxRate.taxClass');

            return response()->json([
                'success' => true,
                'message' => 'Tax rule updated successfully',
                'data' => $taxRule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax rule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete tax rule
     */
    public function destroyTaxRule(TaxRule $taxRule): JsonResponse
    {
        try {
            $taxRule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tax rule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax rule: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==== UTILITIES ====

    /**
     * Test tax calculation
     */
    public function testCalculation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'entity_type' => 'required|in:product,category,customer,shipping,payment',
            'entity_id' => 'nullable|integer',
            'country_code' => 'required|string|size:2',
            'customer_type' => 'nullable|in:individual,company',
            'order_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $conditions = $request->only([
                'entity_type', 'entity_id', 'country_code', 
                'customer_type', 'order_amount'
            ]);

            $result = $this->taxService->calculateTax(
                $request->amount,
                $conditions
            );

            $summary = $this->taxService->getTaxSummary($result);

            return response()->json([
                'success' => true,
                'data' => [
                    'calculation' => $result,
                    'summary' => $summary
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tax calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate Turkish tax number
     */
    public function validateTaxNumber(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $isValid = $this->taxService->validateTurkishTaxNumber($request->tax_number);
        $formatted = $this->taxService->formatTurkishTaxNumber($request->tax_number);

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'formatted' => $formatted,
                'original' => $request->tax_number
            ]
        ]);
    }

    /**
     * Get Turkish VAT rates
     */
    public function getTurkishVATRates(): JsonResponse
    {
        try {
            $rates = $this->taxService->getTurkishVATRates();

            return response()->json([
                'success' => true,
                'data' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch VAT rates: ' . $e->getMessage()
            ], 500);
        }
    }
}
