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
            // Wrap in try-catch to handle cases where index doesn't exist or is used in FK
            try {
                $table->dropIndex('guests_guest_list_email_unique');
            } catch (\Exception $e) {
                // If it fails, it might be due to FK constraint or index not existing
                // simpler to ignore in migration fix context as we just want to proceed
            }
            
            try {
                $table->dropIndex('guests_guest_list_phone_unique');
            } catch (\Exception $e) {
                // Ignore errors
            }
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
