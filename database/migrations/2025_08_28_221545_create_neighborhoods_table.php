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
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name', 150); // Neighborhood name (e.g., "Acaıbay Mah.", "Yeni Mah.")
            $table->string('slug', 180)->unique(); // URL-friendly name with district prefix
            $table->string('neighborhood_code', 6)->nullable(); // Official neighborhood code
            $table->string('postal_code', 5)->nullable(); // 5-digit postal code
            $table->decimal('latitude', 10, 8)->nullable(); // GPS coordinates
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('type', ['mahalle', 'koye_bagli', 'bucak', 'belde'])->default('mahalle'); // Administrative type
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['district_id', 'is_active', 'sort_order']);
            $table->index(['district_id', 'name']); // For dependent dropdown queries
            $table->index('postal_code'); // For postal code lookups
            $table->index('slug');
            $table->index('neighborhood_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
