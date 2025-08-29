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
        Schema::create('coupon_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->enum('rule_type', [
                'general', 
                'brand', 
                'category', 
                'product', 
                'customer_group', 
                'customer'
            ]);
            $table->json('rule_data'); // Store rule-specific data as JSON
            $table->timestamps();
            
            $table->index(['coupon_id', 'rule_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_rules');
    }
};