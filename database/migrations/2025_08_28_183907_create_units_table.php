<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();            // ör: adet, kg, metre
            $table->string('display_name');              // ör: Adet, Kilogram, Metre
            $table->boolean('is_decimal')->default(false);
            $table->unsignedTinyInteger('decimal_places')->default(0);
            $table->decimal('min_qty', 12, 3)->default(1);
            $table->decimal('max_qty', 12, 3)->nullable();
            $table->decimal('qty_step', 12, 3)->default(1);
            $table->decimal('multiples_of', 12, 3)->nullable();
            $table->boolean('allow_free_input')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('units');
    }
};
