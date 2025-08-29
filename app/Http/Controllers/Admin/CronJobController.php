<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CronJobController extends Controller
{
    /**
     * Display a listing of cron jobs
     */
    public function index()
    {
        $cronJobs = $this->getCronJobs();
        $predefinedTasks = $this->getPredefinedTasks();
        
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Cron İşleri Yönetimi - ' . config('app.name'),
            'description' => 'Cron işlerini yönetin, durumlarını kontrol edin ve logları görüntüleyin.',
            'keywords' => 'cron, cron job, schedule, task, automation',
            'canonical_url' => route('admin.cron.index'),
        ];

        return view('admin.cron.index', compact('cronJobs', 'predefinedTasks', 'seoData'));
    }

    /**
     * Show the form for creating a new cron job
     */
    public function create()
    {
        $commands = $this->getAvailableCommands();
        
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Yeni Cron İşi Oluştur - ' . config('app.name'),
            'description' => 'Yeni bir cron işi oluşturun.',
            'keywords' => 'cron, cron job, schedule, task, automation, create',
            'canonical_url' => route('admin.cron.create'),
        ];

        return view('admin.cron.create', compact('commands', 'seoData'));
    }

    /**
     * Store a newly created cron job
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:command,url',
            'command' => 'required_if:type,command|string|max:255',
            'url' => 'required_if:type,url|url|max:500',
            'cron_expression' => 'required|string|max:100',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Cron işi adı zorunludur',
            'type.required' => 'Cron işi tipi zorunludur',
            'command.required_if' => 'Komut alanı zorunludur',
            'url.required_if' => 'URL alanı zorunludur',
            'url.url' => 'Geçerli bir URL giriniz',
            'cron_expression.required' => 'CRON ifadesi zorunludur',
        ]);

        try {
            // In a real application, you would save this to a database
            // For this demo, we'll just simulate the creation
            
            return response()->json([
                'success' => true,
                'message' => 'Cron işi başarıyla oluşturuldu',
                'redirect_url' => route('admin.cron.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cron işi oluşturulurken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified cron job
     */
    public function edit($id)
    {
        // In a real application, you would fetch this from a database
        $cronJob = $this->findCronJob($id);
        
        if (!$cronJob) {
            return redirect()->route('admin.cron.index')->with('error', 'Cron işi bulunamadı');
        }
        
        $commands = $this->getAvailableCommands();
        
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Cron İşi Düzenle - ' . config('app.name'),
            'description' => 'Cron işini düzenleyin.',
            'keywords' => 'cron, cron job, schedule, task, automation, edit',
            'canonical_url' => route('admin.cron.edit', $id),
        ];

        return view('admin.cron.edit', compact('cronJob', 'commands', 'seoData'));
    }

    /**
     * Update the specified cron job
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:command,url',
            'command' => 'required_if:type,command|string|max:255',
            'url' => 'required_if:type,url|url|max:500',
            'cron_expression' => 'required|string|max:100',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Cron işi adı zorunludur',
            'type.required' => 'Cron işi tipi zorunludur',
            'command.required_if' => 'Komut alanı zorunludur',
            'url.required_if' => 'URL alanı zorunludur',
            'url.url' => 'Geçerli bir URL giriniz',
            'cron_expression.required' => 'CRON ifadesi zorunludur',
        ]);

        try {
            // In a real application, you would update this in a database
            // For this demo, we'll just simulate the update
            
            return response()->json([
                'success' => true,
                'message' => 'Cron işi başarıyla güncellendi',
                'redirect_url' => route('admin.cron.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cron işi güncellenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified cron job
     */
    public function destroy($id)
    {
        try {
            // In a real application, you would delete this from a database
            // For this demo, we'll just simulate the deletion
            
            return response()->json([
                'success' => true,
                'message' => 'Cron işi başarıyla silindi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cron işi silinirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle cron job status
     */
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            // In a real application, you would update this in a database
            // For this demo, we'll just simulate the status change
            
            $status = $request->input('is_active') ? 'aktif' : 'pasif';
            
            return response()->json([
                'success' => true,
                'message' => "Cron işi {$status} hale getirildi"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Durum değiştirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute a cron job manually
     */
    public function execute(Request $request, $id)
    {
        try {
            // In a real application, you would fetch this from a database
            $cronJob = $this->findCronJob($id);
            
            if (!$cronJob) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cron işi bulunamadı'
                ], 404);
            }
            
            // Simulate execution
            if ($cronJob['type'] === 'command') {
                // In a real application, you would execute: Artisan::call($cronJob['command']);
                Log::info("Cron job executed: " . $cronJob['command']);
            } else {
                // For URL type, you would make an HTTP request
                Log::info("Cron job executed: " . $cronJob['url']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Cron işi başarıyla çalıştırıldı'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cron işi çalıştırılırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View cron job logs
     */
    public function logs($id)
    {
        // In a real application, you would fetch logs from a database or log files
        $cronJob = $this->findCronJob($id);
        
        if (!$cronJob) {
            return redirect()->route('admin.cron.index')->with('error', 'Cron işi bulunamadı');
        }
        
        // Simulate log data
        $logs = [
            [
                'id' => 1,
                'cron_job_id' => $id,
                'level' => 'info',
                'message' => 'Cron job executed successfully',
                'created_at' => now()->subMinutes(5)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'cron_job_id' => $id,
                'level' => 'warning',
                'message' => 'Slow execution detected',
                'created_at' => now()->subHours(1)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'cron_job_id' => $id,
                'level' => 'error',
                'message' => 'Failed to connect to external API',
                'created_at' => now()->subDays(1)->format('Y-m-d H:i:s')
            ]
        ];
        
        // SEO Meta bilgileri
        $seoData = [
            'title' => 'Cron İşi Logları - ' . config('app.name'),
            'description' => 'Cron işi loglarını görüntüleyin.',
            'keywords' => 'cron, cron job, schedule, task, automation, logs',
            'canonical_url' => route('admin.cron.logs', $id),
        ];

        return view('admin.cron.logs', compact('cronJob', 'logs', 'seoData'));
    }

    /**
     * Add a predefined cron task
     */
    public function addPredefinedTask(Request $request)
    {
        $request->validate([
            'task_key' => 'required|string|in:cache_cleanup,coupon_cleanup,image_regeneration,sitemap_generation'
        ]);

        try {
            $taskKey = $request->input('task_key');
            $predefinedTasks = $this->getPredefinedTasks();
            
            if (!isset($predefinedTasks[$taskKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz önceden tanımlı görev'
                ], 400);
            }
            
            $task = $predefinedTasks[$taskKey];
            
            // In a real application, you would save this to a database
            // For this demo, we'll just simulate the creation
            
            return response()->json([
                'success' => true,
                'message' => 'Önceden tanımlı cron işi başarıyla eklendi',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Önceden tanımlı cron işi eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of cron jobs
     */
    private function getCronJobs()
    {
        // In a real application, you would fetch this from a database
        // For this demo, we'll return sample data
        return [
            [
                'id' => 1,
                'name' => 'Cache Temizleme',
                'type' => 'command',
                'command' => 'cache:clear',
                'cron_expression' => '0 2 * * *',
                'last_run' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'next_run' => now()->addHours(22)->format('Y-m-d H:i:s'),
                'is_active' => true,
                'status' => 'success',
                'created_at' => now()->subDays(30)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Kupon Temizleme',
                'type' => 'command',
                'command' => 'coupons:cleanup',
                'cron_expression' => '0 3 * * *',
                'last_run' => now()->subHours(3)->format('Y-m-d H:i:s'),
                'next_run' => now()->addHours(21)->format('Y-m-d H:i:s'),
                'is_active' => true,
                'status' => 'success',
                'created_at' => now()->subDays(30)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'name' => 'Resim Yeniden Boyutlandırma Kuyruğu',
                'type' => 'command',
                'command' => 'images:regenerate-queue',
                'cron_expression' => '*/30 * * * *',
                'last_run' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
                'next_run' => now()->addMinutes(15)->format('Y-m-d H:i:s'),
                'is_active' => true,
                'status' => 'running',
                'created_at' => now()->subDays(15)->format('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'name' => 'Site Haritası Oluşturma',
                'type' => 'command',
                'command' => 'sitemap:generate',
                'cron_expression' => '0 4 * * 0',
                'last_run' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'next_run' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'is_active' => false,
                'status' => 'inactive',
                'created_at' => now()->subDays(60)->format('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Find a specific cron job by ID
     */
    private function findCronJob($id)
    {
        $cronJobs = $this->getCronJobs();
        
        foreach ($cronJobs as $cronJob) {
            if ($cronJob['id'] == $id) {
                return $cronJob;
            }
        }
        
        return null;
    }

    /**
     * Get available artisan commands for cron jobs
     */
    private function getAvailableCommands()
    {
        // In a real application, you might want to filter these
        // For this demo, we'll return a predefined list
        return [
            'cache:clear' => 'Cache Temizleme',
            'config:clear' => 'Config Cache Temizleme',
            'route:clear' => 'Route Cache Temizleme',
            'view:clear' => 'View Cache Temizleme',
            'coupons:cleanup' => 'Kupon Temizleme',
            'images:regenerate-queue' => 'Resim Yeniden Boyutlandırma Kuyruğu',
            'sitemap:generate' => 'Site Haritası Oluşturma',
            'backup:run' => 'Yedekleme',
            'queue:work' => 'Kuyruk İşleme',
            'schedule:run' => 'Zamanlanmış Görevleri Çalıştırma'
        ];
    }

    /**
     * Get predefined cron tasks
     */
    private function getPredefinedTasks()
    {
        return [
            'cache_cleanup' => [
                'name' => 'Cache Temizleme',
                'type' => 'command',
                'command' => 'cache:clear',
                'cron_expression' => '0 2 * * *',
                'description' => 'Günlük cache temizleme',
                'schedule' => 'Her gün saat 02:00'
            ],
            'coupon_cleanup' => [
                'name' => 'Kupon Temizleme',
                'type' => 'command',
                'command' => 'coupons:cleanup',
                'cron_expression' => '0 3 * * *',
                'description' => 'Süresi dolmuş kuponları temizle',
                'schedule' => 'Her gün saat 03:00'
            ],
            'image_regeneration' => [
                'name' => 'Resim Yeniden Boyutlandırma',
                'type' => 'command',
                'command' => 'images:regenerate-queue',
                'cron_expression' => '*/30 * * * *',
                'description' => 'Resimleri yeniden boyutlandır',
                'schedule' => 'Her 30 dakikada bir'
            ],
            'sitemap_generation' => [
                'name' => 'Site Haritası Oluşturma',
                'type' => 'command',
                'command' => 'sitemap:generate',
                'cron_expression' => '0 4 * * 0',
                'description' => 'Site haritası oluştur',
                'schedule' => 'Her Pazar saat 04:00'
            ]
        ];
    }
}