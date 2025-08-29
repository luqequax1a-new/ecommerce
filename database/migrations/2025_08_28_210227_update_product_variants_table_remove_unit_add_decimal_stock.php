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
        Schema::table('product_variants', function (Blueprint $table) {
            // Rename stock_qty to stock_quantity for consistency
            $table->renameColumn('stock_qty', 'stock_quantity');
            
            // Add indexes for performance
            $table->index(['product_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex(['product_id', 'sku']);
            
            // Rename back to stock_qty
            $table->renameColumn('stock_quantity', 'stock_qty');
        });
    }
};
