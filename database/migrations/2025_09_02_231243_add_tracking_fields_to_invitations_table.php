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
        Schema::table('invitations', function (Blueprint $table) {
            // Add external_id for Twilio message SID
            $table->string('external_id')->nullable()->after('rsvp_note');
            
            // Add delivery_details for storing Twilio status updates
            $table->json('delivery_details')->nullable()->after('external_id');
            
            // Update status enum to include more statuses
            $table->enum('status', ['pending', 'queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'undelivered', 'canceled', 'bounced', 'expired'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Remove added fields
            $table->dropColumn(['external_id', 'delivery_details']);
            
            // Revert status enum
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->change();
        });
    }
};
