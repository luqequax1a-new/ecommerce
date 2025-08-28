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
        // Add SEO fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('meta_title', 60)->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('meta_keywords');
            $table->string('robots', 50)->default('index,follow')->after('canonical_url');
            $table->json('schema_markup')->nullable()->after('robots');
            $table->boolean('auto_update_slug')->default(true)->after('schema_markup');
        });

        // Add SEO fields to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->after('name');
            $table->string('meta_title', 60)->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('meta_keywords');
            $table->string('robots', 50)->default('index,follow')->after('canonical_url');
            $table->json('schema_markup')->nullable()->after('robots');
            $table->boolean('auto_update_slug')->default(true)->after('schema_markup');
            
            // Add unique constraint for slug within same parent
            $table->unique(['parent_id', 'slug'], 'categories_parent_slug_unique');
        });

        // Add SEO fields to brands table  
        Schema::table('brands', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('meta_title', 60)->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url')->nullable()->after('meta_keywords');
            $table->string('robots', 50)->default('index,follow')->after('canonical_url');
            $table->json('schema_markup')->nullable()->after('robots');
            $table->boolean('auto_update_slug')->default(true)->after('schema_markup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'meta_title', 'meta_description', 'meta_keywords', 
                'canonical_url', 'robots', 'schema_markup', 'auto_update_slug'
            ]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_parent_slug_unique');
            $table->dropColumn([
                'slug', 'meta_title', 'meta_description', 'meta_keywords',
                'canonical_url', 'robots', 'schema_markup', 'auto_update_slug'
            ]);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'meta_title', 'meta_description', 'meta_keywords',
                'canonical_url', 'robots', 'schema_markup', 'auto_update_slug'
            ]);
        });
    }
};