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
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('carriers')->onDelete('restrict');
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->onDelete('set null');
            $table->string('tracking_number', 100)->nullable(); // Carrier tracking number
            $table->string('reference_number', 100)->nullable(); // Internal reference
            $table->string('label_url')->nullable(); // Shipping label PDF URL
            $table->string('label_path')->nullable(); // Local label file path
            $table->enum('status', ['pending', 'processing', 'shipped', 'in_transit', 'delivered', 'failed', 'returned'])->default('pending');
            $table->decimal('weight', 8, 3)->nullable(); // Actual package weight
            $table->decimal('length', 8, 2)->nullable(); // Package dimensions
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0); // Actual shipping cost charged
            $table->decimal('insurance_value', 10, 2)->nullable(); // Insurance value
            $table->boolean('signature_required')->default(false);
            $table->boolean('is_cod')->default(false); // Cash on delivery
            $table->decimal('cod_amount', 10, 2)->nullable(); // COD amount if applicable
            $table->text('special_instructions')->nullable(); // Delivery instructions
            $table->json('tracking_events')->nullable(); // Tracking history from carrier API
            $table->timestamp('shipped_at')->nullable(); // When shipped
            $table->timestamp('estimated_delivery_at')->nullable(); // Estimated delivery
            $table->timestamp('delivered_at')->nullable(); // Actual delivery time
            $table->string('delivered_to', 150)->nullable(); // Who received the package
            $table->text('delivery_notes')->nullable(); // Delivery notes
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
            $table->index('carrier_id');
            $table->index(['status', 'shipped_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
