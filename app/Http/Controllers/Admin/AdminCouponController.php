<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRule;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Display a listing of coupons.
     */
    public function index(Request $request)
    {
        $query = Coupon::with('rules')->withCount('usages');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $coupons = $query->latest()->paginate(15);
        
        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('admin.coupons.create', compact('brands', 'categories', 'products'));
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,free_shipping,first_order',
            'value' => 'nullable|numeric|min:0',
            'minimum_cart_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'is_combinable' => 'boolean',
            'rules' => 'nullable|array',
            'rules.*.type' => 'required|in:general,brand,category,product,customer_group,customer',
            'rules.*.data' => 'required|array'
        ]);
        
        $coupon = Coupon::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'value' => $request->value,
            'minimum_cart_amount' => $request->minimum_cart_amount ?? 0,
            'usage_limit' => $request->usage_limit,
            'usage_limit_per_user' => $request->usage_limit_per_user,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->boolean('is_active', true),
            'priority' => $request->priority ?? 0,
            'is_combinable' => $request->boolean('is_combinable', false)
        ]);
        
        // Save rules
        if ($request->rules) {
            foreach ($request->rules as $ruleData) {
                CouponRule::create([
                    'coupon_id' => $coupon->id,
                    'rule_type' => $ruleData['type'],
                    'rule_data' => $ruleData['data']
                ]);
            }
        }
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon başarıyla oluşturuldu.');
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        $coupon->load(['rules', 'usages.user']);
        $statistics = $this->couponService->getUsageStatistics($coupon);
        
        return view('admin.coupons.show', compact('coupon', 'statistics'));
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon)
    {
        $coupon->load('rules');
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('admin.coupons.edit', compact('coupon', 'brands', 'categories', 'products'));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,free_shipping,first_order',
            'value' => 'nullable|numeric|min:0',
            'minimum_cart_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
            'is_combinable' => 'boolean',
            'rules' => 'nullable|array',
            'rules.*.type' => 'required|in:general,brand,category,product,customer_group,customer',
            'rules.*.data' => 'required|array'
        ]);
        
        $coupon->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'value' => $request->value,
            'minimum_cart_amount' => $request->minimum_cart_amount ?? 0,
            'usage_limit' => $request->usage_limit,
            'usage_limit_per_user' => $request->usage_limit_per_user,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->boolean('is_active', true),
            'priority' => $request->priority ?? 0,
            'is_combinable' => $request->boolean('is_combinable', false)
        ]);
        
        // Delete existing rules
        $coupon->rules()->delete();
        
        // Save new rules
        if ($request->rules) {
            foreach ($request->rules as $ruleData) {
                CouponRule::create([
                    'coupon_id' => $coupon->id,
                    'rule_type' => $ruleData['type'],
                    'rule_data' => $ruleData['data']
                ]);
            }
        }
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon başarıyla güncellendi.');
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Kupon başarıyla silindi.');
    }

    /**
     * Toggle coupon active status.
     */
    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => $coupon->is_active ? 'Kupon aktif edildi.' : 'Kupon pasif edildi.',
            'is_active' => $coupon->is_active
        ]);
    }

    /**
     * Generate a random coupon code.
     */
    public function generateCode(Request $request)
    {
        $prefix = $request->prefix ?? 'CPN';
        $code = strtoupper($prefix . Str::random(6));
        
        // Ensure uniqueness
        while (Coupon::where('code', $code)->exists()) {
            $code = strtoupper($prefix . Str::random(6));
        }
        
        return response()->json(['code' => $code]);
    }

    /**
     * Get coupon statistics for reporting.
     */
    public function getStatistics(Request $request)
    {
        $period = $request->period ?? '30_days';
        $stats = $this->couponService->getReportingData($period);
        
        return response()->json($stats);
    }

    /**
     * Display coupon reporting dashboard.
     */
    public function report()
    {
        $reportData = $this->couponService->getReportingData();
        
        return view('admin.coupons.report', compact('reportData'));
    }
}