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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping', 'first_order']);
            $table->decimal('value', 10, 2)->nullable(); // Discount value (percentage or fixed amount)
            $table->decimal('minimum_cart_amount', 10, 2)->default(0); // Minimum cart amount to apply coupon
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_limit_per_user')->nullable(); // Usage limit per user
            $table->integer('used_count')->default(0); // How many times coupon has been used
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For stacking rules
            $table->boolean('is_combinable')->default(false); // Whether can be combined with other coupons
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};