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
        Schema::table('products', function (Blueprint $table) {
            // Add missing columns first
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['simple', 'variable'])->default('simple');
            }
            
            if (!Schema::hasColumn('products', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                $table->decimal('stock_quantity', 12, 3)->default(0);
            }
            
            // Add price fields if they don't exist
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('products', 'compare_price')) {
                $table->decimal('compare_price', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->nullable()->unique();
            }
            
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 3)->nullable();
            }
            
            if (!Schema::hasColumn('products', 'tax_class_id')) {
                $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->onDelete('set null');
            }
        });
        
        // Add indexes after columns are created
        Schema::table('products', function (Blueprint $table) {
            if (!$this->indexExists('products', 'products_product_type_is_active_index')) {
                $table->index(['product_type', 'is_active'], 'products_product_type_is_active_index');
            }
            if (!$this->indexExists('products', 'products_unit_id_index')) {
                $table->index(['unit_id'], 'products_unit_id_index');
            }
            if (!$this->indexExists('products', 'products_category_brand_index')) {
                $table->index(['category_id', 'brand_id'], 'products_category_brand_index');
            }
            if (!$this->indexExists('products', 'products_sku_unique')) {
                $table->index('sku', 'products_sku_index');
            }
        });
    }
    
    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes first
            $indexesToDrop = [
                'products_product_type_is_active_index',
                'products_unit_id_index', 
                'products_category_brand_index',
                'products_sku_index'
            ];
            
            foreach ($indexesToDrop as $indexName) {
                if ($this->indexExists('products', $indexName)) {
                    $table->dropIndex($indexName);
                }
            }
        });
        
        Schema::table('products', function (Blueprint $table) {
            // Drop columns in reverse order
            if (Schema::hasColumn('products', 'tax_class_id')) {
                $table->dropForeign(['tax_class_id']);
                $table->dropColumn('tax_class_id');
            }
            
            if (Schema::hasColumn('products', 'weight')) {
                $table->dropColumn('weight');
            }
            
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }
            
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
            
            if (Schema::hasColumn('products', 'compare_price')) {
                $table->dropColumn('compare_price');
            }
            
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
            
            if (Schema::hasColumn('products', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
            
            if (Schema::hasColumn('products', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
            
            if (Schema::hasColumn('products', 'product_type')) {
                $table->dropColumn('product_type');
            }
        });
    }
};
