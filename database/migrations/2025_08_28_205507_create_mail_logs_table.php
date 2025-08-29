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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mail_configuration_id')->nullable();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('template_name')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();
            
            $table->foreign('mail_configuration_id')->references('id')->on('mail_configurations')->onDelete('set null');
            $table->index(['status', 'sent_at']);
            $table->index(['to_email', 'sent_at']);
            $table->index('template_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
