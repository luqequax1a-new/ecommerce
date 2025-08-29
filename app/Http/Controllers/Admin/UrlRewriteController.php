<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrlRewrite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UrlRewriteController extends Controller
{
    /**
     * Display a listing of URL rewrites
     */
    public function index(Request $request)
    {
        $query = UrlRewrite::query();
        
        // Filters
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        
        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('old_path', 'like', "%{$search}%")
                  ->orWhere('new_path', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        $urlRewrites = $query->paginate(25)->withQueryString();
        
        // Statistics
        $stats = [
            'total' => UrlRewrite::count(),
            'active' => UrlRewrite::where('is_active', true)->count(),
            'inactive' => UrlRewrite::where('is_active', false)->count(),
            'most_accessed' => UrlRewrite::orderByDesc('hit_count')->first(),
            'recent_hits' => UrlRewrite::where('last_accessed_at', '>=', now()->subDay())->sum('hit_count'),
        ];
        
        return view('admin.url-rewrites.index', compact('urlRewrites', 'stats'));
    }

    /**
     * Show the form for creating a new URL rewrite
     */
    public function create()
    {
        return view('admin.url-rewrites.create');
    }

    /**
     * Store a newly created URL rewrite
     */
    public function store(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,category,brand,page',
            'entity_id' => 'nullable|integer',
            'old_path' => 'required|string|unique:url_rewrites,old_path',
            'new_path' => 'required|string',
            'status_code' => 'required|integer|in:301,302',
            'redirect_reason' => 'nullable|string|max:50',
        ]);
        
        UrlRewrite::create([
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'old_path' => $this->normalizePath($request->old_path),
            'new_path' => $this->normalizePath($request->new_path),
            'status_code' => $request->status_code,
            'is_active' => true,
            'redirect_reason' => $request->redirect_reason ?: 'manual',
        ]);
        
        // Clear cache
        $this->clearRewriteCache($request->old_path);
        
        return redirect()
            ->route('admin.url-rewrites.index')
            ->with('success', 'URL rewrite created successfully.');
    }

    /**
     * Show the form for editing the specified URL rewrite
     */
    public function edit(UrlRewrite $urlRewrite)
    {
        return view('admin.url-rewrites.edit', compact('urlRewrite'));
    }

    /**
     * Update the specified URL rewrite
     */
    public function update(Request $request, UrlRewrite $urlRewrite)
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,category,brand,page',
            'entity_id' => 'nullable|integer',
            'old_path' => 'required|string|unique:url_rewrites,old_path,' . $urlRewrite->id,
            'new_path' => 'required|string',
            'status_code' => 'required|integer|in:301,302',
            'is_active' => 'boolean',
            'redirect_reason' => 'nullable|string|max:50',
        ]);
        
        $oldPath = $urlRewrite->old_path;
        
        $urlRewrite->update([
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'old_path' => $this->normalizePath($request->old_path),
            'new_path' => $this->normalizePath($request->new_path),
            'status_code' => $request->status_code,
            'is_active' => $request->boolean('is_active', true),
            'redirect_reason' => $request->redirect_reason,
        ]);
        
        // Clear cache for old and new paths
        $this->clearRewriteCache($oldPath);
        $this->clearRewriteCache($request->old_path);
        
        return redirect()
            ->route('admin.url-rewrites.index')
            ->with('success', 'URL rewrite updated successfully.');
    }

    /**
     * Remove the specified URL rewrite
     */
    public function destroy(UrlRewrite $urlRewrite)
    {
        $oldPath = $urlRewrite->old_path;
        $urlRewrite->delete();
        
        // Clear cache
        $this->clearRewriteCache($oldPath);
        
        return redirect()
            ->route('admin.url-rewrites.index')
            ->with('success', 'URL rewrite deleted successfully.');
    }

    /**
     * Toggle active status of URL rewrite
     */
    public function toggleStatus(UrlRewrite $urlRewrite)
    {
        $urlRewrite->update(['is_active' => !$urlRewrite->is_active]);
        
        // Clear cache
        $this->clearRewriteCache($urlRewrite->old_path);
        
        $status = $urlRewrite->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "URL rewrite {$status} successfully.",
            'is_active' => $urlRewrite->is_active
        ]);
    }

    /**
     * Bulk actions on URL rewrites
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'integer|exists:url_rewrites,id'
        ]);
        
        $urlRewrites = UrlRewrite::whereIn('id', $request->selected_ids);
        $count = $urlRewrites->count();
        
        // Clear cache for all affected paths
        foreach ($urlRewrites->get() as $rewrite) {
            $this->clearRewriteCache($rewrite->old_path);
        }
        
        switch ($request->action) {
            case 'activate':
                $urlRewrites->update(['is_active' => true]);
                $message = "{$count} URL rewrites activated successfully.";
                break;
            case 'deactivate':
                $urlRewrites->update(['is_active' => false]);
                $message = "{$count} URL rewrites deactivated successfully.";
                break;
            case 'delete':
                $urlRewrites->delete();
                $message = "{$count} URL rewrites deleted successfully.";
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export URL rewrites
     */
    public function export(Request $request)
    {
        $query = UrlRewrite::query();
        
        // Apply same filters as index
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        
        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }
        
        $urlRewrites = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'url-rewrites-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($urlRewrites) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Entity Type', 'Entity ID', 'Old Path', 'New Path', 
                'Status Code', 'Is Active', 'Hit Count', 'Redirect Reason',
                'Last Accessed', 'Created At'
            ]);
            
            // CSV data
            foreach ($urlRewrites as $rewrite) {
                fputcsv($file, [
                    $rewrite->id,
                    $rewrite->entity_type,
                    $rewrite->entity_id,
                    $rewrite->old_path,
                    $rewrite->new_path,
                    $rewrite->status_code,
                    $rewrite->is_active ? 'Yes' : 'No',
                    $rewrite->hit_count,
                    $rewrite->redirect_reason,
                    $rewrite->last_accessed_at?->format('Y-m-d H:i:s'),
                    $rewrite->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Analytics for URL rewrites
     */
    public function analytics()
    {
        $stats = [
            'total_redirects' => UrlRewrite::sum('hit_count'),
            'active_redirects' => UrlRewrite::where('is_active', true)->count(),
            'top_redirects' => UrlRewrite::orderByDesc('hit_count')->limit(10)->get(),
            'recent_redirects' => UrlRewrite::where('last_accessed_at', '>=', now()->subWeek())
                                           ->orderByDesc('last_accessed_at')
                                           ->limit(20)
                                           ->get(),
            'by_entity_type' => UrlRewrite::selectRaw('entity_type, COUNT(*) as count, SUM(hit_count) as total_hits')
                                          ->groupBy('entity_type')
                                          ->get(),
            'by_reason' => UrlRewrite::selectRaw('redirect_reason, COUNT(*) as count')
                                    ->groupBy('redirect_reason')
                                    ->get(),
        ];
        
        return view('admin.url-rewrites.analytics', compact('stats'));
    }

    /**
     * Normalize path by ensuring it starts with /
     */
    private function normalizePath(string $path): string
    {
        return '/' . ltrim($path, '/');
    }

    /**
     * Clear URL rewrite cache for specific path
     */
    private function clearRewriteCache(string $path): void
    {
        $cacheKey = 'url_rewrite:' . md5($path);
        Cache::forget($cacheKey);
    }
}