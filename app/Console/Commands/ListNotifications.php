<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Notification;

class ListNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:list 
                            {--event-id= : Filter by event ID}
                            {--status= : Filter by status}
                            {--limit=20 : Number of notifications to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List notifications with their current statuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Notification::with(['event', 'guest', 'user']);

        // Apply filters
        if ($eventId = $this->option('event-id')) {
            $query->where('event_id', $eventId);
        }

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $limit = (int) $this->option('limit');
        $notifications = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        if ($notifications->isEmpty()) {
            $this->info('No notifications found.');
            return 0;
        }

        $this->info("Found {$notifications->count()} notifications:");
        $this->newLine();

        $headers = ['ID', 'Event', 'Guest', 'Type', 'Channel', 'Status', 'External ID', 'Created'];
        $rows = [];

        foreach ($notifications as $notification) {
            $rows[] = [
                $notification->id,
                $notification->event->name ?? 'N/A',
                $notification->guest->name ?? 'N/A',
                $notification->type,
                $notification->channel,
                $notification->status,
                $notification->external_id ?? 'N/A',
                $notification->created_at->format('M j, Y g:i A')
            ];
        }

        $this->table($headers, $rows);

        // Show status summary
        $this->newLine();
        $this->info('Status Summary:');
        $statusCounts = $notifications->groupBy('status')->map->count();
        foreach ($statusCounts as $status => $count) {
            $this->line("  {$status}: {$count}");
        }

        return 0;
    }
}
