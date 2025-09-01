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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who sent the notification
            $table->enum('type', ['guest_removal', 'event_update', 'event_reminder', 'custom']);
            $table->enum('channel', ['email', 'whatsapp', 'both']);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced']);
            $table->string('external_id')->nullable(); // For tracking with external services (Twilio, Mailgun, etc.)
            $table->json('delivery_details')->nullable(); // Store delivery response details
            $table->text('error_message')->nullable(); // Store error details if failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['event_id', 'guest_id']);
            $table->index(['status', 'channel']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
