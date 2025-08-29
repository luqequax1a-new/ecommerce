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
        Schema::table('categories', function (Blueprint $table) {
            // Drop existing unique constraint on slug if it exists
            try {
                $table->dropUnique(['slug']);
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
            
            // Add partial unique index for parent_id + slug
            // This handles both NULL parent_id (root categories) and specific parent_id
            $table->unique(['parent_id', 'slug'], 'categories_parent_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('categories_parent_slug_unique');
            
            // Restore simple slug unique constraint (may cause issues with existing data)
            $table->unique('slug');
        });
    }
};