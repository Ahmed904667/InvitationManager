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
        Schema::table('trials', function (Blueprint $table) {
            $table->string('rsvp_status', 10)->nullable()->after('sample_event_data');
            $table->text('rsvp_note')->nullable()->after('rsvp_status');
            $table->timestamp('rsvp_at')->nullable()->after('rsvp_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trials', function (Blueprint $table) {
            $table->dropColumn(['rsvp_status', 'rsvp_note', 'rsvp_at']);
        });
    }
};
