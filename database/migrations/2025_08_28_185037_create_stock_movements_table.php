<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_change', 12, 3);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
