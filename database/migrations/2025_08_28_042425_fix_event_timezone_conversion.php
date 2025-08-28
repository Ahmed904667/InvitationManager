<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a column to track if dates are already in UTC
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('dates_in_utc')->default(false)->after('status');
        });

        // Convert existing event dates to UTC
        $events = DB::table('events')->get();
        
        foreach ($events as $event) {
            $user = DB::table('users')->where('id', $event->user_id)->first();
            $userTimezone = $user->timezone ?? 'UTC';
            
            $updates = [];
            
            // Convert start_date if not already in UTC
            if ($event->start_date && $userTimezone !== 'UTC') {
                try {
                    // Assume the date was stored in user's timezone
                    $startDate = Carbon::parse($event->start_date, $userTimezone);
                    $updates['start_date'] = $startDate->utc();
                } catch (\Exception $e) {
                    // If parsing fails, assume it's already in UTC
                    \Log::warning("Failed to convert start_date for event {$event->id}: " . $e->getMessage());
                }
            }
            
            // Convert end_date if not already in UTC
            if ($event->end_date && $userTimezone !== 'UTC') {
                try {
                    // Assume the date was stored in user's timezone
                    $endDate = Carbon::parse($event->end_date, $userTimezone);
                    $updates['end_date'] = $endDate->utc();
                } catch (\Exception $e) {
                    // If parsing fails, assume it's already in UTC
                    \Log::warning("Failed to convert end_date for event {$event->id}: " . $e->getMessage());
                }
            }
            
            // Mark as converted to UTC
            $updates['dates_in_utc'] = true;
            
            if (!empty($updates)) {
                DB::table('events')->where('id', $event->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('dates_in_utc');
        });
    }
};
