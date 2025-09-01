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
        Schema::table('guests', function (Blueprint $table) {
            $table->foreignId('scanned_by_scanner_id')->nullable()->constrained('scanners')->onDelete('set null');
            $table->string('scanner_name')->nullable(); // Store scanner name for historical tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['scanned_by_scanner_id']);
            $table->dropColumn(['scanned_by_scanner_id', 'scanner_name']);
        });
    }
};
