<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Event;
use App\Jobs\SendScheduledEventInvitations;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessScheduledEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:process-scheduled 
                            {action : Action to perform (list, send-due, retry-failed, stats)}
                            {--event-id= : Filter by event ID}
                            {--status= : Filter by status (draft, scheduled, sent, cancelled)}
                            {--days=7 : Number of days to look back for stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled event invitations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list':
                $this->listScheduledEvents();
                break;
            case 'send-due':
                $this->sendDueEvents();
                break;
            case 'retry-failed':
                $this->retryFailedEvents();
                break;
            case 'stats':
                $this->showStats();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: list, send-due, retry-failed, stats');
                return 1;
        }
        
        return 0;
    }

    /**
     * List scheduled events with filters
     */
    private function listScheduledEvents(): void
    {
        $query = Event::with(['guestLists.guests']);
        
        // Apply filters
        if ($eventId = $this->option('event-id')) {
            $query->where('id', $eventId);
        }
        
        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }
        
        $events = $query->orderBy('scheduled_at', 'desc')->get();
        
        if ($events->isEmpty()) {
            $this->info('No scheduled events found.');
            return;
        }
        
        $this->table(
            ['ID', 'Name', 'Status', 'Scheduled For', 'Event Date', 'Guest Count', 'Platforms'],
            $events->map(function ($event) {
                $guestCount = $event->guestLists->sum(function($list) {
                    return $list->guests->count();
                });
                
                $platforms = $event->invitation_platforms ? implode(', ', $event->invitation_platforms) : 'email';
                
                return [
                    $event->id,
                    $event->name,
                    $event->status,
                    $event->scheduled_at ? $event->scheduled_at->format('Y-m-d H:i:s') : 'N/A',
                    $event->start_date->format('Y-m-d H:i:s'),
                    $guestCount,
                    $platforms
                ];
            })
        );
    }

    /**
     * Send due scheduled events
     */
    private function sendDueEvents(): void
    {
        // Find events that are due to be sent (scheduled time has passed)
        // Only process events that are exactly due or slightly overdue (within 1 minute)
        // This prevents the command from processing events that were scheduled for the future
        // and ensures proper timing for scheduled events
        $now = now();
        $dueEvents = Event::where('status', 'scheduled')
            ->where('send_type', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->where('scheduled_at', '>=', $now->copy()->subMinute()) // Only process events scheduled within the last minute
            ->get();
        
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
                    Log::info('⏭️ [COMMAND] Skipping event - invitations already sent', [
                        'event_id' => $event->id,
                        'event_name' => $event->name,
                        'scheduled_at' => $event->scheduled_at
                    ]);
                    continue;
                }
                
                // Dispatch job immediately
                SendScheduledEventInvitations::dispatch($event->id);
                $sent++;
                
                Log::info('📅 [COMMAND] Dispatched job for due scheduled event', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'scheduled_at' => $event->scheduled_at
                ]);
                
            } catch (\Exception $e) {
                Log::error('❌ [COMMAND] Failed to dispatch job for scheduled event', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Dispatched: {$sent}, Failed: {$failed}");
    }

    /**
     * Retry failed events (reset to scheduled status)
     */
    private function retryFailedEvents(): void
    {
        $failedEvents = Event::where('status', 'draft')
            ->where('send_type', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();
        
        if ($failedEvents->isEmpty()) {
            $this->info('No failed scheduled events to retry.');
            return;
        }
        
        $this->info("Found {$failedEvents->count()} failed scheduled events to retry.");
        
        $bar = $this->output->createProgressBar($failedEvents->count());
        $bar->start();
        
        $retried = 0;
        
        foreach ($failedEvents as $event) {
            try {
                // Check if event already has invitations sent to prevent duplicates
                $existingInvitations = \App\Shared\Models\Invitation::where('event_id', $event->id)->exists();
                
                if ($existingInvitations) {
                    Log::info('⏭️ [COMMAND] Skipping retry - invitations already sent', [
                        'event_id' => $event->id,
                        'event_name' => $event->name
                    ]);
                    continue;
                }
                
                // Reset status and retry
                $event->update(['status' => 'scheduled']);
                SendScheduledEventInvitations::dispatch($event->id);
                $retried++;
                
                Log::info('📅 [COMMAND] Retried failed scheduled event', [
                    'event_id' => $event->id,
                    'event_name' => $event->name
                ]);
                
            } catch (\Exception $e) {
                Log::error('❌ [COMMAND] Failed to retry scheduled event', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Retried: {$retried} events");
    }

    /**
     * Show statistics
     */
    private function showStats(): void
    {
        $days = $this->option('days');
        $startDate = now()->subDays($days);
        
        $totalEvents = Event::where('created_at', '>=', $startDate)->count();
        $scheduledEvents = Event::where('status', 'scheduled')
            ->where('send_type', 'scheduled')
            ->where('created_at', '>=', $startDate)
            ->count();
        $sentEvents = Event::where('status', 'sent')
            ->where('created_at', '>=', $startDate)
            ->count();
        $draftEvents = Event::where('status', 'draft')
            ->where('created_at', '>=', $startDate)
            ->count();
        
        $dueEvents = Event::where('status', 'scheduled')
            ->where('send_type', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->count();
        
        $this->info("Event Statistics (Last {$days} days):");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Events', $totalEvents],
                ['Scheduled Events', $scheduledEvents],
                ['Sent Events', $sentEvents],
                ['Draft Events', $draftEvents],
                ['Due Events (Ready to Send)', $dueEvents],
            ]
        );
        
        // Show upcoming scheduled events
        $upcomingEvents = Event::where('status', 'scheduled')
            ->where('send_type', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();
        
        if (!$upcomingEvents->isEmpty()) {
            $this->info("\nUpcoming Scheduled Events:");
            $this->table(
                ['Name', 'Scheduled For', 'Event Date'],
                $upcomingEvents->map(function ($event) {
                    return [
                        $event->name,
                        $event->scheduled_at->format('Y-m-d H:i:s'),
                        $event->start_date->format('Y-m-d H:i:s')
                    ];
                })
            );
        }
    }
}
