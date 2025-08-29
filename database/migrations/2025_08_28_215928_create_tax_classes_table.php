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
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Tax class name (e.g., "Standard", "Reduced", "Exempt")
            $table->string('code', 50)->unique(); // Tax class code
            $table->text('description')->nullable();
            $table->decimal('default_rate', 5, 4)->default(0); // Default tax rate (e.g., 0.1800 for 18%)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_classes');
    }
};
