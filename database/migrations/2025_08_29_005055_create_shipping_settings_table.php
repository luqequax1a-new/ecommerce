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
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            
            // Free shipping settings
            $table->boolean('free_enabled')->default(true)->comment('Enable free shipping threshold');
            $table->decimal('free_threshold', 10, 2)->default(300.00)->comment('Free shipping threshold amount in TL');
            
            // Flat rate shipping settings
            $table->boolean('flat_rate_enabled')->default(true)->comment('Enable flat rate shipping');
            $table->decimal('flat_rate_fee', 8, 2)->default(15.00)->comment('Flat rate shipping fee in TL');
            
            // Cash on Delivery (COD) settings
            $table->boolean('cod_enabled')->default(true)->comment('Enable cash on delivery');
            $table->decimal('cod_extra_fee', 8, 2)->default(5.00)->comment('Extra fee for cash on delivery in TL');
            
            // Additional settings
            $table->string('currency', 3)->default('TRY')->comment('Currency code');
            $table->text('free_shipping_message')->nullable()->comment('Custom free shipping message');
            $table->text('shipping_description')->nullable()->comment('Shipping method description');
            
            // Meta fields
            $table->boolean('is_active')->default(true)->comment('Whether shipping settings are active');
            $table->json('metadata')->nullable()->comment('Additional configuration data');
            
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
