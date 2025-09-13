<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'general_message')) {
                $table->text('general_message')->nullable()->after('invitation_message');
            }
            if (!Schema::hasColumn('events', 'group_messages')) {
                $table->json('group_messages')->nullable()->after('general_message');
            }
            if (!Schema::hasColumn('events', 'per_guest_messages')) {
                $table->json('per_guest_messages')->nullable()->after('group_messages');
            }
            if (!Schema::hasColumn('events', 'ai_generated')) {
                $table->boolean('ai_generated')->default(false)->after('per_guest_messages');
            }
            if (!Schema::hasColumn('events', 'message_mode')) {
                $table->string('message_mode')->nullable()->after('ai_generated');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'message_mode')) {
                $table->dropColumn('message_mode');
            }
            if (Schema::hasColumn('events', 'ai_generated')) {
                $table->dropColumn('ai_generated');
            }
            if (Schema::hasColumn('events', 'per_guest_messages')) {
                $table->dropColumn('per_guest_messages');
            }
            if (Schema::hasColumn('events', 'group_messages')) {
                $table->dropColumn('group_messages');
            }
            if (Schema::hasColumn('events', 'general_message')) {
                $table->dropColumn('general_message');
            }
        });
    }
};









