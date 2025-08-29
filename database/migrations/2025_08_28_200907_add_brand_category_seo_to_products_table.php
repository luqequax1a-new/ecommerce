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
        Schema::table('products', function (Blueprint $table) {
            // Add foreign key constraints to existing columns
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // SEO fields for shared hosting optimization
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->text('schema_markup')->nullable(); // JSON-LD structured data
            
            // Additional product fields
            $table->text('short_description')->nullable();
            $table->decimal('weight', 8, 3)->nullable(); // Product weight
            $table->json('attributes')->nullable(); // Flexible attributes storage
            $table->boolean('featured')->default(false); // Featured product
            $table->integer('sort_order')->default(0);
            
            // Indexes for shared hosting performance
            $table->index(['is_active', 'featured']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['featured', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['is_active', 'featured']);
            $table->dropIndex(['brand_id', 'is_active']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['featured', 'is_active', 'sort_order']);
            
            // Drop foreign key constraints
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            
            // Drop columns (but keep brand_id and category_id as they were in original)
            $table->dropColumn([
                'meta_title',
                'meta_description',
                'meta_keywords',
                'canonical_url',
                'schema_markup',
                'short_description',
                'weight',
                'attributes',
                'featured',
                'sort_order'
            ]);
        });
    }
};
