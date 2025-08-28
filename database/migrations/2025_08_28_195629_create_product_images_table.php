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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('original_filename'); // Orijinal dosya adı
            $table->string('path'); // Relatif dosya yolu
            $table->string('alt_text')->nullable(); // SEO için alt text
            $table->integer('sort_order')->default(0); // Görsel sıralaması
            $table->boolean('is_cover')->default(false); // Ana görsel mi?
            $table->integer('width')->nullable(); // Genişlik (pixel)
            $table->integer('height')->nullable(); // Yükseklik (pixel)
            $table->integer('file_size')->nullable(); // Dosya boyutu (bytes)
            $table->string('mime_type')->nullable(); // image/jpeg, image/png vb.
            $table->timestamps();
            
            // İndeksler
            $table->index(['product_id', 'sort_order']);
            $table->index(['product_id', 'is_cover']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
