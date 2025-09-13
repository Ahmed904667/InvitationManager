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
            // Modify the status enum to include 'running'
            $table->enum('status', ['draft', 'scheduled', 'sent', 'running', 'completed', 'cancelled'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to previous status enum
            $table->enum('status', ['draft', 'scheduled', 'sent', 'completed', 'cancelled'])->default('draft')->change();
        });
    }
};
