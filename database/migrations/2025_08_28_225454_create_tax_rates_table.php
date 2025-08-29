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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->constrained('tax_classes')->onDelete('cascade');
            $table->string('name', 100); // Rate name (e.g., "Standard VAT 20%", "Reduced VAT 10%")
            $table->string('code', 50)->unique(); // Rate code (e.g., "TR_VAT_20", "TR_VAT_10")
            $table->decimal('rate', 8, 6); // Tax rate (e.g., 0.200000 for 20%, 0.100000 for 10%)
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->string('country_code', 2)->default('TR'); // Country code (Turkey)
            $table->string('region')->nullable(); // Region/state if applicable
            $table->boolean('is_compound')->default(false); // If tax compounds on other taxes
            $table->integer('priority')->default(0); // Priority for tax calculation order
            $table->date('effective_from')->nullable(); // When this rate becomes effective
            $table->date('effective_until')->nullable(); // When this rate expires
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional tax rate metadata
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['tax_class_id', 'is_active']);
            $table->index(['country_code', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
