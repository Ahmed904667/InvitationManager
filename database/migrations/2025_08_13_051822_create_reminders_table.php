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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            
            // Reminder details
            $table->dateTime('scheduled_for');
            $table->enum('platform', ['email', 'whatsapp']);
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            
            // Event information (cached for reliability)
            $table->string('event_name');
            $table->dateTime('event_date');
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_timezone', 50)->default('UTC');
            
            // Reminder content
            $table->text('message_content')->nullable();
            $table->string('subject')->nullable(); // For email reminders
            
            // Tracking
            $table->dateTime('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->dateTime('last_attempt_at')->nullable();
            
            // Job tracking
            $table->string('job_id')->nullable(); // Laravel job ID for tracking
            $table->string('queue')->nullable(); // Queue name
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['scheduled_for', 'status']);
            $table->index(['invitation_id', 'status']);
            $table->index(['event_id', 'scheduled_for']);
            $table->index(['guest_id', 'status']);
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
