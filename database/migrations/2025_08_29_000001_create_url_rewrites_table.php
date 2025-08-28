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
        Schema::create('url_rewrites', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'product', 'category', 'brand'
            $table->unsignedBigInteger('entity_id');
            $table->string('old_path')->index(); // Original URL path
            $table->string('new_path'); // New canonical URL path
            $table->integer('status_code')->default(301); // HTTP status code (301, 302)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['entity_type', 'entity_id']);
            $table->index(['old_path', 'is_active']);
            $table->unique(['old_path']); // Each old path should be unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_rewrites');
    }
};