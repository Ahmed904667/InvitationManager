<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->string('token')->unique();
            $table->enum('channel', ['email', 'whatsapp']);
            $table->string('recipient')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->enum('rsvp_status', ['yes', 'no', 'maybe', 'none'])->default('none');
            $table->timestamp('rsvp_at')->nullable();
            $table->string('rsvp_note')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};

