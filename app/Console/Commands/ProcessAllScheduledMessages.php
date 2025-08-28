<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Event;
use App\Shared\Models\Reminder;
use App\Jobs\SendScheduledEventInvitations;
use App\Jobs\SendEventReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessAllScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:process-scheduled {--force : Force processing even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all scheduled messages (events and reminders)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Processing scheduled messages...');
        
        // Process scheduled events
        $this->processScheduledEvents();
        
        // Process scheduled reminders
        $this->processScheduledReminders();
        
        $this->info('✅ Scheduled message processing completed!');
        
        return 0;
    }

    /**
     * Process scheduled events
     */
    private function processScheduledEvents(): void
    {
        $this->info('📅 Processing scheduled events...');
        
        $query = Event::where('status', 'scheduled')
            ->where('send_type', 'scheduled')
            ->whereNotNull('scheduled_at');
        
        if (!$this->option('force')) {
            $query->where('scheduled_at', '<=', now());
        }
        
        $dueEvents = $query->get();
        
        if ($dueEvents->isEmpty()) {
            $this->info('No due scheduled events found.');
            return;
        }
        
        $this->info("Found {$dueEvents->count()} due scheduled events.");
        
        $bar = $this->output->createProgressBar($dueEvents->count());
        $bar->start();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($dueEvents as $event) {
            try {
                // Check if event already has invitations sent to prevent duplicates
                $existingInvitations = \App\Shared\Models\Invitation::where('event_id', $event->id)->exists();
                
                if ($existingInvitations) {
                    Log::info('⏭️ [SCHEDULER] Skipping event - invitations already sent', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'scheduled_at' => $event->scheduled_at
                    ]);
                    continue;
                }
                
                // Dispatch job immediately
                SendScheduledEventInvitations::dispatch($event->id);
                $sent++;
                
                Log::info('📅 [SCHEDULER] Dispatched job for scheduled event', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'scheduled_at' => $event->scheduled_at
                ]);
                
            } catch (\Exception $e) {
                Log::error('❌ [SCHEDULER] Failed to dispatch job for scheduled event', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Events - Dispatched: {$sent}, Failed: {$failed}");
    }

    /**
     * Process scheduled reminders
     */
    private function processScheduledReminders(): void
    {
        $this->info('⏰ Processing scheduled reminders...');
        
        $query = Reminder::where('status', 'pending');
        
        if (!$this->option('force')) {
            $query->where('scheduled_for', '<=', now());
        }
        
        $dueReminders = $query->get();
        
        if ($dueReminders->isEmpty()) {
            $this->info('No due scheduled reminders found.');
            return;
        }
        
        $this->info("Found {$dueReminders->count()} due scheduled reminders.");
        
        $bar = $this->output->createProgressBar($dueReminders->count());
        $bar->start();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($dueReminders as $reminder) {
            try {
                // Dispatch job immediately
                SendEventReminder::dispatch($reminder->id);
                $sent++;
                
                Log::info('⏰ [SCHEDULER] Dispatched job for scheduled reminder', [
                    'reminder_id' => $reminder->id,
                    'event_name' => $reminder->event_name,
                    'guest_name' => $reminder->guest_name,
                    'scheduled_for' => $reminder->scheduled_for
                ]);
                
            } catch (\Exception $e) {
                Log::error('❌ [SCHEDULER] Failed to dispatch job for scheduled reminder', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Reminders - Dispatched: {$sent}, Failed: {$failed}");
    }
}
