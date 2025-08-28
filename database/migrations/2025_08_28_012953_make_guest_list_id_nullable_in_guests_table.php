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
            // Drop the existing foreign key constraint
            $table->dropForeign(['guest_list_id']);
            
            // Make the column nullable
            $table->foreignId('guest_list_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('guest_list_id')->references('id')->on('guest_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['guest_list_id']);
            
            // Make the column non-nullable again
            $table->foreignId('guest_list_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('guest_list_id')->references('id')->on('guest_lists')->onDelete('cascade');
        });
    }
};
