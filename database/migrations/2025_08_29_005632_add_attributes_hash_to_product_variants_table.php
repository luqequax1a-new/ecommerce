<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add canonical representation for variants
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Add attributes_hash for canonical representation
            $table->string('attributes_hash', 255)->nullable()->after('attributes')
                  ->comment('Deterministic hash of attribute combinations for uniqueness');
            
            // Add base_sku field for SKU generation
            $table->string('base_sku', 100)->nullable()->after('sku')
                  ->comment('Base SKU from parent product for deterministic generation');
            
            // Note: price and stock_qty already have correct precision in existing migration
            
            // Add fields for enterprise variant management
            $table->boolean('is_active')->default(true)->after('stock_qty')
                  ->comment('Whether this variant is active');
            $table->boolean('is_default')->default(false)->after('is_active')
                  ->comment('Whether this is the default variant for the product');
            $table->string('barcode', 50)->nullable()->after('sku')
                  ->comment('Product barcode (EAN, UPC, etc.)');
            $table->string('ean', 20)->nullable()->after('barcode')
                  ->comment('European Article Number');
            $table->string('image_path', 500)->nullable()->after('ean')
                  ->comment('Variant-specific image path');
            $table->integer('sort_order')->default(0)->after('is_default')
                  ->comment('Display order for variants');
            
            // Add indexes for performance
            $table->index('attributes_hash', 'idx_variants_attributes_hash');
            $table->index('is_active', 'idx_variants_is_active');
            $table->index('is_default', 'idx_variants_is_default');
            $table->index('sort_order', 'idx_variants_sort_order');
            $table->index(['product_id', 'is_default'], 'idx_variants_product_default');
            $table->index(['product_id', 'is_active'], 'idx_variants_product_active');
        });
        
        // Add unique constraint for canonical representation
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(['product_id', 'attributes_hash'], 'uniq_product_attributes_hash');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('uniq_product_attributes_hash');
            
            // Drop indexes
            $table->dropIndex('idx_variants_attributes_hash');
            $table->dropIndex('idx_variants_is_active');
            $table->dropIndex('idx_variants_is_default');
            $table->dropIndex('idx_variants_sort_order');
            $table->dropIndex('idx_variants_product_default');
            $table->dropIndex('idx_variants_product_active');
            
            // Drop new columns
            $table->dropColumn([
                'attributes_hash',
                'base_sku',
                'is_active',
                'is_default',
                'barcode',
                'ean',
                'image_path',
                'sort_order'
            ]);
        });
    }
};
