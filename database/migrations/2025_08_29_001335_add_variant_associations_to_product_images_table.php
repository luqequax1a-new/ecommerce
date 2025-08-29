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
        Schema::table('product_images', function (Blueprint $table) {
            // Add variant associations
            $table->json('variant_ids')->nullable()->after('product_id');
            $table->boolean('is_variant_specific')->default(false)->after('is_cover');
            $table->string('image_type')->default('product')->after('mime_type'); // product, variant, gallery
            $table->text('description')->nullable()->after('alt_text');
            
            // Add indexes for variant queries
            $table->index('is_variant_specific');
            $table->index('image_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['is_variant_specific']);
            $table->dropIndex(['image_type']);
            $table->dropColumn(['variant_ids', 'is_variant_specific', 'image_type', 'description']);
        });
    }
};
