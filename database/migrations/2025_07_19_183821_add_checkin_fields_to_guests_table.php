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
            $table->text('notes')->nullable()->after('group_id');
            $table->boolean('checked_in')->default(false)->after('notes');
            $table->timestamp('checked_in_at')->nullable()->after('checked_in');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->onDelete('set null')->after('checked_in_at');
            $table->text('check_in_notes')->nullable()->after('checked_in_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropColumn([
                'notes',
                'checked_in',
                'checked_in_at',
                'checked_in_by',
                'check_in_notes'
            ]);
        });
    }
};
