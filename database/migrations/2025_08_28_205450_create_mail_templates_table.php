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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // welcome, order_confirmation, newsletter
            $table->string('subject');
            $table->longText('html_content');
            $table->longText('text_content')->nullable();
            $table->json('variables')->nullable(); // Available template variables
            $table->string('category')->default('general'); // general, marketing, transactional
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
