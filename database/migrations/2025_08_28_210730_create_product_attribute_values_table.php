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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // e.g., "Red", "Large", "Cotton"
            $table->string('slug'); // e.g., "red", "large", "cotton"
            $table->string('color_code')->nullable(); // For color attributes (#FF0000)
            $table->string('image_path')->nullable(); // For image-based attributes
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['product_attribute_id', 'slug'], 'attr_values_unique');
            $table->index(['product_attribute_id', 'is_active', 'sort_order'], 'attr_values_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
