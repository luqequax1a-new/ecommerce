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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            
            // Hiyerarşik yapı (parent-child)
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            
            // Görsel alanları
            $table->string('image_path')->nullable(); // Kategori görseli
            $table->string('banner_path')->nullable(); // Banner görseli
            $table->string('icon_class')->nullable(); // CSS icon class
            
            // SEO alanları (shared hosting için kritik)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->text('schema_markup')->nullable(); // JSON-LD
            
            // Durum ve sıralama
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_footer')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('level')->default(0); // Kategori seviyesi (performans için)
            
            // E-ticaret özel alanlar
            $table->boolean('featured')->default(false); // Öne çıkan kategori
            $table->string('template')->nullable(); // Özel template
            $table->json('filters')->nullable(); // Kategori özel filtreleri
            
            $table->timestamps();
            
            // Shared hosting optimize indeksler
            $table->index(['is_active', 'show_in_menu', 'sort_order']);
            $table->index(['parent_id', 'sort_order']);
            $table->index(['slug']);
            $table->index(['featured', 'is_active']);
            $table->index(['level', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
