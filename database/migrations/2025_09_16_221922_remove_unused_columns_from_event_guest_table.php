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
        Schema::table('event_guest', function (Blueprint $table) {
            // Drop indexes first before dropping columns
            $table->dropIndex(['event_id', 'guest_name']);
            $table->dropIndex(['event_id', 'guest_email']);
            $table->dropIndex(['event_id', 'guest_phone']);
        });
        
        Schema::table('event_guest', function (Blueprint $table) {
            // Drop unused columns
            $table->dropColumn([
                'guest_name',
                'guest_email', 
                'guest_phone',
                'guest_language',
                'guest_notes',
                'guest_timezone',
                'guest_group_name',
                'guest_list_name'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_guest', function (Blueprint $table) {
            // Re-add the columns in case we need to rollback
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_language')->nullable();
            $table->text('guest_notes')->nullable();
            $table->string('guest_timezone')->nullable();
            $table->string('guest_group_name')->nullable();
            $table->string('guest_list_name')->nullable();
        });
        
        Schema::table('event_guest', function (Blueprint $table) {
            // Re-add the indexes
            $table->index(['event_id', 'guest_name']);
            $table->index(['event_id', 'guest_email']);
            $table->index(['event_id', 'guest_phone']);
        });
    }
};
