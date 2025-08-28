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
        Schema::create('shipping_blackouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->nullable()->constrained('carriers')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('shipping_zones')->onDelete('cascade');
            $table->enum('restriction_type', ['postal_code', 'city', 'region', 'country'])->default('postal_code');
            $table->string('restriction_value', 100); // Postal code, city name, etc.
            $table->string('reason')->nullable(); // Reason for blackout
            $table->date('start_date')->nullable(); // Temporary blackout start
            $table->date('end_date')->nullable(); // Temporary blackout end
            $table->boolean('is_permanent')->default(true); // Permanent restriction
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['carrier_id', 'restriction_type', 'is_active']);
            $table->index(['zone_id', 'restriction_type', 'is_active']);
            $table->index('restriction_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_blackouts');
    }
};
