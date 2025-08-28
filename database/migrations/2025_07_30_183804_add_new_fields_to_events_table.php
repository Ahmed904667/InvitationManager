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
        Schema::table('events', function (Blueprint $table) {
            // Additional event information
            $table->text('additional_information')->nullable()->after('parking_info');
            
            // Invitation platforms and messaging
            $table->json('invitation_platforms')->nullable()->after('qr_description');
            $table->enum('message_mode', ['general', 'group', 'per_guest'])->default('general')->after('invitation_platforms');
            $table->text('general_message')->nullable()->after('message_mode');
            $table->json('group_messages')->nullable()->after('general_message');
            $table->json('per_guest_messages')->nullable()->after('group_messages');
            $table->boolean('ai_generated')->default(false)->after('per_guest_messages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'additional_information',
                'invitation_platforms',
                'message_mode',
                'general_message',
                'group_messages',
                'per_guest_messages',
                'ai_generated'
            ]);
        });
    }
};
