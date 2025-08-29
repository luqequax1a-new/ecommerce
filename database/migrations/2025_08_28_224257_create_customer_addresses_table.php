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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Address Type
            $table->enum('type', ['billing', 'shipping', 'both'])->default('both');
            $table->string('title', 100)->nullable(); // "Home", "Work", "Office" etc.
            
            // Personal Information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 100)->nullable();
            $table->string('tax_number', 20)->nullable(); // Turkish tax number for companies
            
            // Turkish Location Structure (Required)
            $table->unsignedTinyInteger('province_id'); // FK to provinces.id (1-81)
            $table->foreignId('district_id')->constrained('districts')->onDelete('restrict');
            // neighborhood_id will be added in future phase
            
            // Address Details
            $table->text('address_line'); // Full address (min 10 chars)
            $table->string('postal_code', 5)->nullable(); // 5-digit Turkish postal code
            
            // Contact Information
            $table->string('phone', 20); // Turkish phone format (+90 5XX XXX XX XX)
            $table->string('email', 100)->nullable();
            
            // Address Status
            $table->boolean('is_default_billing')->default(false);
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->json('metadata')->nullable(); // For future extensions
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('restrict');
            
            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_default_billing']);
            $table->index(['user_id', 'is_default_shipping']);
            $table->index(['province_id', 'district_id']);
            $table->index('postal_code');
            
            // Unique constraints
            $table->unique(['user_id', 'is_default_billing'], 'unique_default_billing');
            $table->unique(['user_id', 'is_default_shipping'], 'unique_default_shipping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
