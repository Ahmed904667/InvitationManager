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
            // Modify the status enum to include 'completed'
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled', 'completed'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to original status enum
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft')->change();
        });
    }
};
