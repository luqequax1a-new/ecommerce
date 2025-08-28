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
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_rate_id')->constrained('tax_rates')->onDelete('cascade');
            $table->string('entity_type', 50); // 'product', 'category', 'customer', 'shipping', 'payment'
            $table->bigInteger('entity_id')->nullable(); // ID of the entity (product_id, category_id, etc.)
            $table->string('country_code', 2)->default('TR'); // Country code
            $table->string('region')->nullable(); // Region/state if applicable
            $table->string('postal_code_from')->nullable(); // Postal code range start
            $table->string('postal_code_to')->nullable(); // Postal code range end
            $table->decimal('customer_group_id')->nullable(); // Customer group for B2B taxation
            $table->enum('customer_type', ['individual', 'company'])->nullable(); // Individual vs Company taxation
            $table->decimal('order_amount_from', 10, 2)->nullable(); // Minimum order amount
            $table->decimal('order_amount_to', 10, 2)->nullable(); // Maximum order amount
            $table->integer('priority')->default(0); // Rule priority (higher = more priority)
            $table->boolean('stop_processing')->default(false); // Stop processing other rules
            $table->date('date_from')->nullable(); // Rule effective from
            $table->date('date_to')->nullable(); // Rule effective until
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // Additional conditions in JSON format
            $table->text('description')->nullable(); // Rule description
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['entity_type', 'entity_id']);
            $table->index(['country_code', 'region']);
            $table->index(['is_active', 'priority']);
            $table->index(['date_from', 'date_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};
