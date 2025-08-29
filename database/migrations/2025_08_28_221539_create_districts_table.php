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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('province_id'); // FK to provinces.id (plate code)
            $table->string('name', 100); // District name (e.g., "Kadıköy", "Beşiktaş")
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            
            // Indexes for performance
            $table->unique(['province_id', 'name']); // Prevent duplicate districts per province
            $table->index(['province_id', 'is_active']); // For dependent dropdown queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
