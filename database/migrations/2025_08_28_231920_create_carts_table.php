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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('total_items')->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->enum('status', ['active', 'abandoned', 'converted'])->default('active');
            $table->timestamps();
            
            $table->index(['updated_at', 'status']);
            $table->index(['total_items', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
