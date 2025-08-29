<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('url_rewrites', function (Blueprint $table) {
            $table->integer('hit_count')->default(0)->after('is_active');
            $table->timestamp('last_accessed_at')->nullable()->after('hit_count');
            $table->string('redirect_reason')->nullable()->after('last_accessed_at'); // 'slug_change', 'manual', 'cleanup'
            
            // Index for analytics queries
            $table->index(['hit_count', 'last_accessed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_rewrites', function (Blueprint $table) {
            $table->dropIndex(['hit_count', 'last_accessed_at']);
            $table->dropColumn(['hit_count', 'last_accessed_at', 'redirect_reason']);
        });
    }
};
