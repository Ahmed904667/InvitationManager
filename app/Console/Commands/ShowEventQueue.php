<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Event;
use Carbon\Carbon;

class ShowEventQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:queue {--status= : Filter by status (draft, sent, scheduled, completed)} {--upcoming : Show only upcoming events} {--past : Show only past/completed events} {--watch : Watch queue in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all events and when they will be marked as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        $upcoming = $this->option('upcoming');
        $past = $this->option('past');
        $watch = $this->option('watch');
        
        if ($watch) {
            return $this->watchQueue($status, $upcoming, $past);
        }
        
        return $this->showQueue($status, $upcoming, $past);
    }
    
    /**
     * Show the queue once
     */
    private function showQueue($status, $upcoming, $past)
    {
        $query = Event::query();
        
        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($upcoming) {
            $query->where('start_date', '>', now());
        }
        
        if ($past) {
            $query->where(function ($q) {
                $q->where('status', 'completed')
                  ->orWhere('end_date', '<', now())
                  ->orWhere(function ($subQ) {
                      $subQ->whereNull('end_date')
                           ->where('start_date', '<', now()->startOfDay());
                  });
            });
        }
        
        $events = $query->orderBy('start_date')->get();
        
        if ($events->isEmpty()) {
            $this->info('📭 No events found matching your criteria.');
            return 0;
        }
        
        $this->info('📅 Event Queue - Completion Schedule');
        $this->info('=====================================');
        $this->newLine();
        
        $now = now();
        $headers = ['ID', 'Name', 'Status', 'Start Date', 'End Date', 'Completion Status', 'Time Until Completion'];
        $rows = [];
        
        foreach ($events as $event) {
            $completionStatus = $this->getCompletionStatus($event, $now);
            $timeUntilCompletion = $this->getTimeUntilCompletion($event, $now);
            
            $rows[] = [
                $event->id,
                $this->truncateName($event->name, 30),
                $this->getStatusIcon($event->status) . ' ' . $event->status,
                $event->start_date->format('M d, Y H:i'),
                $event->end_date ? $event->end_date->format('M d, Y H:i') : 'No end date',
                $completionStatus,
                $timeUntilCompletion
            ];
        }
        
        $this->table($headers, $rows);
        
        // Summary
        $this->newLine();
        $this->info('📊 Summary:');
        $this->line('   • Total Events: ' . $events->count());
        $this->line('   • Completed: ' . $events->where('status', 'completed')->count());
        $this->line('   • Active: ' . $events->whereNotIn('status', ['completed'])->count());
        $this->line('   • Due Soon (next 24h): ' . $events->filter(function ($event) use ($now) {
            if ($event->status === 'completed') return false;
            if ($event->end_date) {
                return $event->end_date->isBetween($now, $now->addDay());
            }
            return $event->start_date->isBetween($now, $now->addDay());
        })->count());
        
        $this->newLine();
        $this->info('💡 Quick Commands:');
        $this->line('   • Complete event: php artisan event:complete {event_id}');
        $this->line('   • Force complete: php artisan event:complete {event_id} --force');
        $this->line('   • Check completion: php artisan events:mark-completed --dry-run');
        $this->line('   • Watch queue: php artisan events:queue --watch');
        
        return 0;
    }
    
    /**
     * Watch the queue in real-time
     */
    private function watchQueue($status, $upcoming, $past)
    {
        $this->info('👀 Watching event queue in real-time...');
        $this->info('Press Ctrl+C to stop watching.');
        $this->newLine();
        
        while (true) {
            // Clear screen
            $this->output->write("\033[2J\033[1;1H");
            
            // Show current time
            $this->info('🕐 ' . now()->format('Y-m-d H:i:s') . ' - Event Queue Status');
            $this->info('=====================================');
            $this->newLine();
            
            // Show queue
            $this->showQueue($status, $upcoming, $past);
            
            // Wait 30 seconds
            sleep(30);
        }
    }
    
    /**
     * Get completion status for an event
     */
    private function getCompletionStatus(Event $event, Carbon $now): string
    {
        if ($event->status === 'completed') {
            return '✅ Completed';
        }
        
        if ($event->isCompleted()) {
            return '🔄 Due for completion';
        }
        
        if ($event->end_date) {
            if ($event->end_date->isBetween($now, $now->addHour())) {
                return '⏰ Due within 1 hour';
            }
            if ($event->end_date->isBetween($now, $now->addDay())) {
                return '📅 Due within 24 hours';
            }
        }
        
        return '⏳ Not due yet';
    }
    
    /**
     * Get time until completion
     */
    private function getTimeUntilCompletion(Event $event, Carbon $now): string
    {
        if ($event->status === 'completed') {
            return 'Already completed';
        }
        
        if ($event->end_date) {
            if ($now->isAfter($event->end_date)) {
                return 'Overdue';
            }
            return $now->diffForHumans($event->end_date);
        }
        
        // For events without end date, use start date
        if ($event->start_date->startOfDay()->isBefore($now->startOfDay())) {
            return 'Overdue (no end date)';
        }
        
        return $event->start_date->startOfDay()->diffForHumans();
    }
    
    /**
     * Get status icon
     */
    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'draft' => '📝',
            'sent' => '📤',
            'scheduled' => '⏰',
            'completed' => '✅',
            default => '❓'
        };
    }
    
    /**
     * Truncate name for display
     */
    private function truncateName(string $name, int $length): string
    {
        return strlen($name) > $length ? substr($name, 0, $length - 3) . '...' : $name;
    }
}
