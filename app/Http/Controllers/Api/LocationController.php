<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    /**
     * Get all active provinces
     * 
     * @return JsonResponse
     */
    public function provinces(): JsonResponse
    {
        try {
            $provinces = Province::getCachedList();
            
            return response()->json([
                'success' => true,
                'data' => $provinces,
                'count' => $provinces->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch provinces',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get districts for a specific province
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function districts(Request $request): JsonResponse
    {
        try {
            // Validate province_id parameter
            $validated = $request->validate([
                'province_id' => 'required|integer|min:1|max:81'
            ]);
            
            $provinceId = $validated['province_id'];
            
            // Check if province exists
            $province = Province::find($provinceId);
            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province not found',
                    'data' => []
                ], 404);
            }
            
            if (!$province->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province is not active',
                    'data' => []
                ], 422);
            }
            
            $districts = District::getCachedListByProvince($provinceId);
            
            return response()->json([
                'success' => true,
                'data' => $districts,
                'count' => $districts->count(),
                'province' => [
                    'id' => $province->id,
                    'name' => $province->name
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'data' => []
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch districts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Get province details by ID
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function province(int $id): JsonResponse
    {
        try {
            $province = Province::with(['activeDistricts' => function($query) {
                $query->select('id', 'province_id', 'name')->orderBy('name');
            }])->find($id);
            
            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $province->id,
                    'name' => $province->name,
                    'region' => $province->region,
                    'is_active' => $province->is_active,
                    'districts_count' => $province->activeDistricts->count(),
                    'districts' => $province->activeDistricts
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch province details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
