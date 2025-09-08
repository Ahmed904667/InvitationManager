<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\Event;
use App\Jobs\SendEventStartReminder;
use Carbon\Carbon;

class ScheduleEventStartReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:schedule-start-reminders {--minutes=30 : Minutes before event to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule event start reminder notifications for organizers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutesBefore = (int) $this->option('minutes');
        $this->info("Scheduling event start reminders for events starting in {$minutesBefore} minutes...");

        // Find events that start in the specified number of minutes
        $targetTime = Carbon::now()->addMinutes($minutesBefore);
        $timeWindow = 5; // 5-minute window to account for scheduling delays

        $events = Event::where('status', '!=', 'completed')
            ->where('status', '!=', 'draft')
            ->whereBetween('start_date', [
                $targetTime->copy()->subMinutes($timeWindow),
                $targetTime->copy()->addMinutes($timeWindow)
            ])
            ->with('user')
            ->get();

        $scheduled = 0;
        $skipped = 0;

        foreach ($events as $event) {
            // Check if organizer has notifications enabled
            $organizer = $event->user;
            if (!$organizer) {
                $this->warn("Event '{$event->name}' has no organizer - skipping");
                $skipped++;
                continue;
            }

            // Check if notifications are muted
            $notificationSettings = $organizer->notification_settings ?? [];
            if ($notificationSettings['mute_notifications'] ?? false) {
                $this->info("Organizer {$organizer->name} has notifications muted - skipping event '{$event->name}'");
                $skipped++;
                continue;
            }

            // Schedule the reminder job
            $delayMinutes = $minutesBefore - Carbon::now()->diffInMinutes($event->start_date, false);
            
            if ($delayMinutes > 0) {
                SendEventStartReminder::dispatch($event->id, $minutesBefore)
                    ->delay(now()->addMinutes($delayMinutes));

                $this->info("Scheduled reminder for '{$event->name}' (organizer: {$organizer->name}) in {$delayMinutes} minutes");
                $scheduled++;
            } else {
                $this->warn("Event '{$event->name}' is too close to start time - skipping");
                $skipped++;
            }
        }

        $this->info("Scheduling complete: {$scheduled} reminders scheduled, {$skipped} skipped");

        Log::info('Event start reminders scheduled', [
            'minutes_before' => $minutesBefore,
            'scheduled' => $scheduled,
            'skipped' => $skipped,
            'total_events_found' => $events->count()
        ]);

        return 0;
    }
}