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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->string('name', 150); // Method name (e.g., "Aras Kargo - Standart")
            $table->string('code', 100); // Unique code for this method
            $table->text('description')->nullable();
            
            // Calculation method and pricing
            $table->enum('calc_method', ['flat', 'by_weight', 'by_price', 'by_quantity', 'table_rate'])->default('flat');
            $table->decimal('base_fee', 10, 2)->default(0); // Base shipping cost
            $table->decimal('step_fee', 10, 2)->default(0); // Per kg/unit/price step fee
            $table->decimal('step_size', 8, 3)->default(1); // Step size (e.g., per 0.5 kg)
            
            // Free shipping threshold
            $table->decimal('free_threshold', 10, 2)->nullable(); // Free shipping above this amount
            $table->boolean('free_threshold_includes_tax')->default(false);
            
            // Weight constraints
            $table->decimal('min_weight', 8, 3)->nullable(); // Minimum weight (kg)
            $table->decimal('max_weight', 8, 3)->nullable(); // Maximum weight (kg)
            
            // Price constraints
            $table->decimal('min_price', 10, 2)->nullable(); // Minimum order value
            $table->decimal('max_price', 10, 2)->nullable(); // Maximum order value
            
            // Quantity constraints
            $table->integer('min_quantity')->nullable(); // Minimum item quantity
            $table->integer('max_quantity')->nullable(); // Maximum item quantity
            
            // Delivery time estimates
            $table->integer('min_delivery_days')->nullable(); // Minimum delivery days
            $table->integer('max_delivery_days')->nullable(); // Maximum delivery days
            $table->string('delivery_time_text', 100)->nullable(); // Custom text (e.g., "1-3 iş günü")
            
            // Restrictions
            $table->boolean('exclude_virtual_products')->default(true); // Hide for virtual products
            $table->boolean('require_signature')->default(false); // Signature required
            $table->boolean('supports_cod')->default(false); // Cash on delivery
            $table->json('excluded_product_categories')->nullable(); // Category IDs to exclude
            $table->json('excluded_product_types')->nullable(); // Product types to exclude
            
            // Tax handling
            $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->onDelete('set null');
            $table->boolean('is_taxable')->default(false);
            
            // Status and display
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['carrier_id', 'zone_id', 'is_active']);
            $table->index(['is_active', 'sort_order']);
            $table->index('calc_method');
            $table->unique(['carrier_id', 'zone_id', 'code'], 'carrier_zone_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
