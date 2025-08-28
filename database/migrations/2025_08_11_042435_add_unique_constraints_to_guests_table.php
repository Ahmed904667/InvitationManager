<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up duplicate emails within each guest list
        $this->cleanupDuplicates('email');
        
        // Clean up duplicate phones within each guest list
        $this->cleanupDuplicates('phone');

        // For SQLite, we need to handle NULL values differently
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Create partial indexes that exclude NULL values
            DB::statement('CREATE UNIQUE INDEX guests_guest_list_email_unique ON guests (guest_list_id, email) WHERE email IS NOT NULL AND email != ""');
            DB::statement('CREATE UNIQUE INDEX guests_guest_list_phone_unique ON guests (guest_list_id, phone) WHERE phone IS NOT NULL AND phone != ""');
        } else {
            Schema::table('guests', function (Blueprint $table) {
                // Add unique constraints for email and phone within each guest list
                // This ensures no duplicate emails or phones within the same guest list
                $table->unique(['guest_list_id', 'email'], 'guests_guest_list_email_unique');
                $table->unique(['guest_list_id', 'phone'], 'guests_guest_list_phone_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS guests_guest_list_email_unique');
            DB::statement('DROP INDEX IF EXISTS guests_guest_list_phone_unique');
        } else {
            Schema::table('guests', function (Blueprint $table) {
                $table->dropUnique('guests_guest_list_email_unique');
                $table->dropUnique('guests_guest_list_phone_unique');
            });
        }
    }

    /**
     * Clean up duplicate values for a given field within each guest list
     */
    private function cleanupDuplicates(string $field): void
    {
        // Get all guest lists
        $guestLists = DB::table('guests')
            ->select('guest_list_id')
            ->distinct()
            ->pluck('guest_list_id');

        foreach ($guestLists as $guestListId) {
            // Find duplicates for this guest list and field
            $duplicates = DB::table('guests')
                ->select($field, DB::raw('COUNT(*) as count'))
                ->where('guest_list_id', $guestListId)
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->groupBy($field)
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                // Keep the first record and delete the rest
                $records = DB::table('guests')
                    ->where('guest_list_id', $guestListId)
                    ->where($field, $duplicate->$field)
                    ->orderBy('id')
                    ->get();

                // Delete all but the first record
                $idsToDelete = $records->skip(1)->pluck('id');
                if ($idsToDelete->isNotEmpty()) {
                    DB::table('guests')->whereIn('id', $idsToDelete)->delete();
                }
            }
        }
    }
};
