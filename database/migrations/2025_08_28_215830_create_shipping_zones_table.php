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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Zone name (e.g., "Türkiye", "İstanbul İçi", "Avrupa")
            $table->string('slug', 100)->unique(); // URL-friendly name
            $table->string('code', 20)->unique(); // Short code (e.g., 'TR', 'IST', 'EU')
            $table->text('description')->nullable(); // Zone description
            $table->enum('type', ['country', 'region', 'city', 'postal_code', 'custom'])->default('custom');
            $table->json('countries')->nullable(); // Array of country codes (ISO 3166-1)
            $table->json('regions')->nullable(); // Array of region/state codes
            $table->json('cities')->nullable(); // Array of city names
            $table->json('postal_codes')->nullable(); // Array of postal code patterns
            $table->json('postal_code_ranges')->nullable(); // Array of ranges [{"from": "34000", "to": "34999"}]
            $table->decimal('default_tax_rate', 5, 4)->nullable(); // Default tax rate for this zone
            $table->string('currency_code', 3)->default('TRY'); // Default currency
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('slug');
            $table->index('code');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
