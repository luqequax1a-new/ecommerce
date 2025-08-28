<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlRewrite extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id', 
        'old_path',
        'new_path',
        'status_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Find active rewrite for given path
     */
    public static function findRewrite(string $path): ?self
    {
        return self::where('old_path', $path)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create rewrite record when slug changes
     */
    public static function createRewrite(
        string $entityType,
        int $entityId,
        string $oldPath,
        string $newPath,
        int $statusCode = 301
    ): self {
        return self::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_path' => $oldPath,
            'new_path' => $newPath,
            'status_code' => $statusCode,
            'is_active' => true,
        ]);
    }

    /**
     * Get all rewrites for entity
     */
    public static function getEntityRewrites(string $entityType, int $entityId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Deactivate old rewrites for entity
     */
    public static function deactivateEntityRewrites(string $entityType, int $entityId): void
    {
        self::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->update(['is_active' => false]);
    }
}