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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Color", "Size", "Material"
            $table->string('slug')->unique(); // e.g., "color", "size", "material"
            $table->enum('type', ['text', 'color', 'image'])->default('text'); // Type of attribute
            $table->boolean('is_required')->default(false); // Is this attribute required for variants
            $table->boolean('is_variation')->default(true); // Can this attribute create variations
            $table->integer('sort_order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
