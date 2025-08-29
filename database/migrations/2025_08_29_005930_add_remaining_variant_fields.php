<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add attributes_hash field for canonical representation
     * Note: Other enterprise fields are handled in previous migration
     */
    public function up(): void
    {
        // Only add attributes_hash if it doesn't exist (handled by previous migration)
        if (!Schema::hasColumn('product_variants', 'attributes_hash')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('attributes_hash', 255)->nullable()->after('attributes')
                      ->comment('Deterministic hash of attribute combinations for uniqueness');
                $table->index('attributes_hash', 'idx_variants_attributes_hash');
            });
        }
        
        // Add unique constraint for canonical representation if it doesn't exist
        try {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unique(['product_id', 'attributes_hash'], 'uniq_product_attributes_hash');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Only drop what this migration added
        try {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropUnique('uniq_product_attributes_hash');
            });
        } catch (\Exception $e) {
            // Constraint might not exist
        }
        
        if (Schema::hasColumn('product_variants', 'attributes_hash')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropIndex('idx_variants_attributes_hash');
                $table->dropColumn('attributes_hash');
            });
        }
    }
};
