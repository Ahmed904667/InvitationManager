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
            // Drop the existing enum constraint and recreate it with 'expired' added
            $table->enum('status', ['pending', 'sent', 'failed', 'expired'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->change();
        });
    }
};
