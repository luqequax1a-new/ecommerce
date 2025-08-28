<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerAddress;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Get user's address book
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $addresses = $user->getAddressBook();
            
            return response()->json([
                'success' => true,
                'data' => $addresses,
                'count' => $addresses->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch addresses',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Store a new address
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:billing,shipping,both',
                'title' => 'nullable|string|max:100',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'company' => 'nullable|string|max:100',
                'tax_number' => 'nullable|string|max:20',
                'province_id' => 'required|integer|min:1|max:81|exists:provinces,id',
                'district_id' => 'required|integer|exists:districts,id',
                'address_line' => 'required|string|min:10',
                'postal_code' => 'nullable|string|size:5|regex:/^[0-9]{5}$/',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:100',
                'is_default_billing' => 'boolean',
                'is_default_shipping' => 'boolean',
                'metadata' => 'nullable|array'
            ]);
            
            // Custom validation for Turkish phone
            $validator->after(function ($validator) use ($request) {
                if (!CustomerAddress::validateTurkishPhone($request->phone)) {
                    $validator->errors()->add('phone', 'Invalid Turkish phone number format');
                }
                
                if ($request->postal_code && !CustomerAddress::validateTurkishPostalCode($request->postal_code)) {
                    $validator->errors()->add('postal_code', 'Invalid Turkish postal code format');
                }
                
                // Validate district belongs to province
                if ($request->province_id && $request->district_id) {
                    $district = District::find($request->district_id);
                    if ($district && $district->province_id != $request->province_id) {
                        $validator->errors()->add('district_id', 'Selected district does not belong to the selected province');
                    }
                }
            });
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $validated = $validator->validated();
            $validated['user_id'] = $user->id;
            
            $address = CustomerAddress::create($validated);
            $address->load(['province', 'district']);
            
            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => $address
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Show a specific address
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $address = CustomerAddress::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->with(['province', 'district'])
                                    ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $address
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Update an address
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $address = CustomerAddress::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|in:billing,shipping,both',
                'title' => 'nullable|string|max:100',
                'first_name' => 'sometimes|string|max:100',
                'last_name' => 'sometimes|string|max:100',
                'company' => 'nullable|string|max:100',
                'tax_number' => 'nullable|string|max:20',
                'province_id' => 'sometimes|integer|min:1|max:81|exists:provinces,id',
                'district_id' => 'sometimes|integer|exists:districts,id',
                'address_line' => 'sometimes|string|min:10',
                'postal_code' => 'nullable|string|size:5|regex:/^[0-9]{5}$/',
                'phone' => 'sometimes|string|max:20',
                'email' => 'nullable|email|max:100',
                'is_default_billing' => 'boolean',
                'is_default_shipping' => 'boolean',
                'metadata' => 'nullable|array'
            ]);
            
            // Custom validation
            $validator->after(function ($validator) use ($request) {
                if ($request->has('phone') && !CustomerAddress::validateTurkishPhone($request->phone)) {
                    $validator->errors()->add('phone', 'Invalid Turkish phone number format');
                }
                
                if ($request->postal_code && !CustomerAddress::validateTurkishPostalCode($request->postal_code)) {
                    $validator->errors()->add('postal_code', 'Invalid Turkish postal code format');
                }
                
                // Validate district belongs to province
                $provinceId = $request->province_id ?? $request->address->province_id;
                $districtId = $request->district_id;
                
                if ($provinceId && $districtId) {
                    $district = District::find($districtId);
                    if ($district && $district->province_id != $provinceId) {
                        $validator->errors()->add('district_id', 'Selected district does not belong to the selected province');
                    }
                }
            });
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $address->update($validator->validated());
            $address->load(['province', 'district']);
            
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Delete an address
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $address = CustomerAddress::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }
            
            $address->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Set an address as default billing
     */
    public function setDefaultBilling(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $address = CustomerAddress::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->billing()
                                    ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Billing address not found'
                ], 404);
            }
            
            $address->setAsDefaultBilling();
            
            return response()->json([
                'success' => true,
                'message' => 'Default billing address updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default billing address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Set an address as default shipping
     */
    public function setDefaultShipping(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $address = CustomerAddress::where('user_id', $user->id)
                                    ->where('id', $id)
                                    ->shipping()
                                    ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping address not found'
                ], 404);
            }
            
            $address->setAsDefaultShipping();
            
            return response()->json([
                'success' => true,
                'message' => 'Default shipping address updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default shipping address',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get default addresses (billing and shipping)
     */
    public function defaults(Request $request): JsonResponse
    {
        try {
            $user = $request->user() ?? Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $defaultBilling = CustomerAddress::getDefaultBilling($user->id);
            $defaultShipping = CustomerAddress::getDefaultShipping($user->id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'billing' => $defaultBilling,
                    'shipping' => $defaultShipping
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch default addresses',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
