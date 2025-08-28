<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Drop the unique constraint on token
            $table->dropUnique(['token']);
            
            // Add a composite unique constraint on event_id, guest_id, and channel
            // This ensures one invitation record per guest per platform per event
            $table->unique(['event_id', 'guest_id', 'channel'], 'invitations_event_guest_channel_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Restore the unique constraint on token
            $table->unique(['token']);
            
            // Drop the composite unique constraint
            $table->dropUnique('invitations_event_guest_channel_unique');
        });
    }
};
