<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Reminder;
use App\Jobs\SendEventReminder;
use Carbon\Carbon;

class ManageReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:manage 
                            {action : Action to perform (list, send-due, retry-failed, cancel, stats)}
                            {--event-id= : Filter by event ID}
                            {--status= : Filter by status (pending, sent, failed, cancelled)}
                            {--platform= : Filter by platform (email, whatsapp)}
                            {--days=7 : Number of days to look back for stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage event reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list':
                $this->listReminders();
                break;
            case 'send-due':
                $this->sendDueReminders();
                break;
            case 'retry-failed':
                $this->retryFailedReminders();
                break;
            case 'cancel':
                $this->cancelReminders();
                break;
            case 'stats':
                $this->showStats();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: list, send-due, retry-failed, cancel, stats');
                return 1;
        }
        
        return 0;
    }

    /**
     * List reminders with filters
     */
    private function listReminders(): void
    {
        $query = Reminder::with(['event', 'guest', 'invitation']);
        
        // Apply filters
        if ($eventId = $this->option('event-id')) {
            $query->where('event_id', $eventId);
        }
        
        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }
        
        if ($platform = $this->option('platform')) {
            $query->where('platform', $platform);
        }
        
        $reminders = $query->orderBy('scheduled_for', 'desc')->get();
        
        if ($reminders->isEmpty()) {
            $this->info('No reminders found.');
            return;
        }
        
        $this->table(
            ['ID', 'Event', 'Guest', 'Platform', 'Scheduled For (UTC)', 'Guest Timezone', 'Status', 'Attempts'],
            $reminders->map(function ($reminder) {
                return [
                    $reminder->id,
                    $reminder->event_name,
                    $reminder->guest_name,
                    $reminder->platform_display_name,
                    $reminder->scheduled_for->format('Y-m-d H:i:s'),
                    $reminder->guest_timezone ?? 'UTC',
                    $reminder->status_display_name,
                    $reminder->attempts
                ];
            })
        );
    }

    /**
     * Send due reminders
     */
    private function sendDueReminders(): void
    {
        $dueReminders = Reminder::due()->pending()->get();
        
        if ($dueReminders->isEmpty()) {
            $this->info('No due reminders found.');
            return;
        }
        
        $this->info("Found {$dueReminders->count()} due reminders.");
        
        $bar = $this->output->createProgressBar($dueReminders->count());
        $bar->start();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($dueReminders as $reminder) {
            try {
                // Dispatch job immediately
                SendEventReminder::dispatch($reminder->id);
                $sent++;
            } catch (\Exception $e) {
                $reminder->markAsFailed($e->getMessage());
                $failed++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Sent: {$sent}, Failed: {$failed}");
    }

    /**
     * Retry failed reminders
     */
    private function retryFailedReminders(): void
    {
        $failedReminders = Reminder::where('status', 'failed')
            ->where('attempts', '<', 3)
            ->get();
        
        if ($failedReminders->isEmpty()) {
            $this->info('No failed reminders to retry.');
            return;
        }
        
        $this->info("Found {$failedReminders->count()} failed reminders to retry.");
        
        $bar = $this->output->createProgressBar($failedReminders->count());
        $bar->start();
        
        $retried = 0;
        
        foreach ($failedReminders as $reminder) {
            try {
                // Reset status and retry
                $reminder->update(['status' => 'pending']);
                SendEventReminder::dispatch($reminder->id);
                $retried++;
            } catch (\Exception $e) {
                $reminder->markAsFailed($e->getMessage());
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Retried: {$retried} reminders");
    }

    /**
     * Cancel reminders
     */
    private function cancelReminders(): void
    {
        $query = Reminder::where('status', 'pending');
        
        // Apply filters
        if ($eventId = $this->option('event-id')) {
            $query->where('event_id', $eventId);
        }
        
        if ($platform = $this->option('platform')) {
            $query->where('platform', $platform);
        }
        
        $reminders = $query->get();
        
        if ($reminders->isEmpty()) {
            $this->info('No pending reminders found to cancel.');
            return;
        }
        
        $this->info("Found {$reminders->count()} pending reminders to cancel.");
        
        if (!$this->confirm('Are you sure you want to cancel these reminders?')) {
            $this->info('Cancelled.');
            return;
        }
        
        $bar = $this->output->createProgressBar($reminders->count());
        $bar->start();
        
        foreach ($reminders as $reminder) {
            $reminder->markAsCancelled();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Cancelled {$reminders->count()} reminders.");
    }

    /**
     * Show reminder statistics
     */
    private function showStats(): void
    {
        $days = (int) $this->option('days');
        $since = Carbon::now()->subDays($days);
        
        $this->info("Reminder Statistics (last {$days} days):");
        $this->newLine();
        
        // Total reminders
        $total = Reminder::where('created_at', '>=', $since)->count();
        $this->info("Total reminders created: {$total}");
        
        // Status breakdown
        $statuses = Reminder::where('created_at', '>=', $since)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        $this->info('Status breakdown:');
        foreach ($statuses as $status => $count) {
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $this->line("  {$status}: {$count} ({$percentage}%)");
        }
        
        // Platform breakdown
        $platforms = Reminder::where('created_at', '>=', $since)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform');
        
        $this->newLine();
        $this->info('Platform breakdown:');
        foreach ($platforms as $platform => $count) {
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $this->line("  {$platform}: {$count} ({$percentage}%)");
        }
        
        // Recent activity
        $this->newLine();
        $this->info('Recent activity:');
        $recent = Reminder::where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recent as $reminder) {
            $this->line("  {$reminder->created_at->format('Y-m-d H:i')} - {$reminder->event_name} ({$reminder->guest_name}) - {$reminder->status}");
        }
    }
}
