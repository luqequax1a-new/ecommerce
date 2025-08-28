<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'subtotal',
        'shipping_total',
        'tax_total',
        'discount_total',
        'grand_total',
        'currency',
        'billing_address',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
    ];

    /**
     * Order statuses with Turkish translations
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Ödeme Bekliyor',
            'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal Edildi',
            'refunded' => 'İade Edildi',
        ];
    }

    /**
     * Order status colors for UI
     */
    public static function getStatusColors(): array
    {
        return [
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
        ];
    }

    /**
     * Get the customer that owns the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get sales metrics for dashboard
     */
    public static function getSalesMetrics(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $orders = self::where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count, SUM(grand_total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $orders->map(function ($order) {
            return [
                'date' => $order->date,
                'revenue' => (float) $order->revenue,
                'orders' => (int) $order->order_count,
                'aov' => $order->order_count > 0 ? (float) $order->revenue / $order->order_count : 0,
            ];
        })->toArray();
    }

    /**
     * Get order status distribution
     */
    public static function getStatusDistribution(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $statusCounts = self::where('created_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return $statusCounts->map(function ($item) {
            return [
                'status' => $item->status,
                'count' => (int) $item->count,
            ];
        })->toArray();
    }

    /**
     * Get today's metrics
     */
    public static function getTodayMetrics(): array
    {
        $today = Carbon::today();
        
        $todayOrders = self::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled');
        
        $revenue = $todayOrders->sum('grand_total');
        $orderCount = $todayOrders->count();
        
        return [
            'revenue' => (float) $revenue,
            'orders' => $orderCount,
            'aov' => $orderCount > 0 ? $revenue / $orderCount : 0,
        ];
    }
}