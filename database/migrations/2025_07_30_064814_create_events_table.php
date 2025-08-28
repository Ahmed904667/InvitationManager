<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->string('parking_info')->nullable();
            
            // Invitation Settings
            $table->string('invitation_title')->default("You're Invited!");
            $table->string('invitation_subtitle')->nullable();
            $table->text('invitation_message')->nullable();
            $table->string('rsvp_message')->nullable();
            $table->string('rsvp_deadline')->nullable();
            $table->string('rsvp_contact')->nullable();
            $table->boolean('rsvp_enabled')->default(true);
            
            // QR Code Settings
            $table->boolean('qr_checkin_enabled')->default(true);
            $table->string('qr_code_url')->nullable();
            $table->string('qr_description')->nullable();
            
            // Design Settings
            $table->string('hero_color1')->default('#ff6b6b');
            $table->string('hero_color2')->default('#ffa726');
            $table->string('accent_color')->default('#667eea');
            $table->string('font_family')->default('Segoe UI');
            
            // Guest Lists
            $table->json('guest_list_ids')->nullable();
            
            // Message Template
            $table->text('message_template')->nullable();
            $table->json('custom_messages')->nullable(); // Per guest custom messages
            $table->json('attachments')->nullable(); // File attachments
            
            // Scheduling
            $table->enum('send_type', ['now', 'scheduled'])->default('now');
            $table->dateTime('scheduled_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft');
            
            // User relationship
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
