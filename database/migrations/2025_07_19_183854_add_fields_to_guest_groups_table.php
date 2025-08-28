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
        Schema::table('guest_groups', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('color', 7)->nullable()->after('description'); // Hex color code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_groups', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'color'
            ]);
        });
    }
};
