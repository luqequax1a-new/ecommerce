<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'total_items',
        'subtotal',
        'tax_total',
        'grand_total',
        'currency',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get active carts within specified minutes
     */
    public static function getActiveCarts(int $minutes = 30): array
    {
        $threshold = Carbon::now()->subMinutes($minutes);
        
        $activeCarts = self::where('updated_at', '>=', $threshold)
            ->where('total_items', '>', 0)
            ->where('status', 'active');
        
        $count = $activeCarts->count();
        $total = $activeCarts->sum('grand_total');
        $avg = $count > 0 ? $total / $count : 0;
        
        return [
            'count' => $count,
            'total' => (float) $total,
            'avg' => (float) $avg,
        ];
    }

    /**
     * Scope for active carts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('total_items', '>', 0);
    }

    /**
     * Scope for recent activity
     */
    public function scopeRecentActivity($query, int $minutes = 30)
    {
        return $query->where('updated_at', '>=', Carbon::now()->subMinutes($minutes));
    }
}