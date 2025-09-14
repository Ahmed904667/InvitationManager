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
            // Drop the old unique constraints using the correct index names
            $table->dropIndex('guests_guest_list_email_unique');
            $table->dropIndex('guests_guest_list_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Restore the old unique constraints
            $table->unique(['guest_list_id', 'email'], 'guests_guest_list_email_unique');
            $table->unique(['guest_list_id', 'phone'], 'guests_guest_list_phone_unique');
        });
    }
};
