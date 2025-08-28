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
        Schema::create('mail_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // smtp_primary, smtp_backup, etc.
            $table->string('driver')->default('smtp'); // smtp, sendmail, mailgun
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('encryption')->nullable(); // tls, ssl
            $table->string('from_address');
            $table->string('from_name');
            $table->string('reply_to_address')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('daily_limit')->default(500); // Shared hosting limits
            $table->integer('hourly_limit')->default(50);
            $table->integer('sent_today')->default(0);
            $table->integer('sent_this_hour')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('last_reset_at')->nullable();
            $table->json('additional_settings')->nullable(); // Extra SMTP settings
            $table->timestamps();
            
            $table->index(['is_active', 'is_default']);
            $table->index('last_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_configurations');
    }
};
