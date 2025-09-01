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
            $table->boolean('checked_in')->default(false)->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('checked_in');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->onDelete('set null')->after('checked_in_at');
            $table->text('check_in_notes')->nullable()->after('checked_in_by');
            $table->foreignId('scanned_by_scanner_id')->nullable()->constrained('scanners')->onDelete('set null')->after('check_in_notes');
            $table->string('scanner_name')->nullable()->after('scanned_by_scanner_id');
            
            // Index for performance
            $table->index(['event_id', 'checked_in']);
            $table->index(['checked_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_guest', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['scanned_by_scanner_id']);
            $table->dropIndex(['event_id', 'checked_in']);
            $table->dropIndex(['checked_in_at']);
            $table->dropColumn([
                'checked_in',
                'checked_in_at',
                'checked_in_by',
                'check_in_notes',
                'scanned_by_scanner_id',
                'scanner_name'
            ]);
        });
    }
};