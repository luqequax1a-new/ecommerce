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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Carrier name (e.g., "Aras Kargo", "MNG")
            $table->string('slug', 100)->unique(); // URL-friendly name
            $table->string('code', 50)->unique(); // Internal code (e.g., 'aras', 'mng')
            $table->text('description')->nullable(); // Carrier description
            $table->string('logo_path')->nullable(); // Logo image path
            $table->string('website_url')->nullable(); // Carrier website
            $table->string('contact_phone', 20)->nullable(); // Contact number
            $table->string('contact_email')->nullable(); // Contact email
            $table->text('tracking_url_template')->nullable(); // e.g., "https://kargo.com/track/{tracking}"
            $table->string('api_endpoint')->nullable(); // For future API integration
            $table->json('api_credentials')->nullable(); // API keys/tokens (encrypted)
            $table->boolean('supports_cod')->default(false); // Cash on delivery support
            $table->boolean('supports_return')->default(false); // Return shipment support
            $table->boolean('supports_international')->default(false); // International shipping
            $table->string('estimated_delivery_time', 50)->nullable(); // e.g., "1-3 iş günü"
            $table->decimal('max_weight', 8, 3)->nullable(); // Maximum weight capacity (kg)
            $table->decimal('max_dimensions_length', 8, 2)->nullable(); // cm
            $table->decimal('max_dimensions_width', 8, 2)->nullable(); // cm
            $table->decimal('max_dimensions_height', 8, 2)->nullable(); // cm
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Display order
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_active', 'sort_order']);
            $table->index('slug');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
