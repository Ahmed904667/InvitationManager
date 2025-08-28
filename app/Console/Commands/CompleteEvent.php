<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Event;

class CompleteEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:complete {event_id} {--force : Force completion even if not due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually mark an event as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $force = $this->option('force');
        
        $event = Event::find($eventId);
        
        if (!$event) {
            $this->error("❌ Event with ID {$eventId} not found.");
            return 1;
        }
        
        $this->info("📅 Event: {$event->name}");
        $this->info("   Status: {$event->status}");
        $this->info("   Start Date: {$event->start_date}");
        $this->info("   End Date: " . ($event->end_date ?? 'Not set'));
        $this->info("   Current Time: " . now());
        
        if ($event->status === 'completed') {
            $this->warn("⚠️  Event is already completed.");
            return 0;
        }
        
        if (!$force) {
            $shouldComplete = $event->isCompleted();
            $this->info("   Should Complete: " . ($shouldComplete ? 'Yes' : 'No'));
            
            if (!$shouldComplete) {
                if (!$this->confirm('Event is not due yet. Force completion?')) {
                    $this->info("❌ Operation cancelled.");
                    return 0;
                }
            }
        }
        
        $event->markAsCompleted();
        
        $this->info("✅ Event marked as completed!");
        
        return 0;
    }
}
