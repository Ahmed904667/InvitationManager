<?php

namespace App\Console\Commands;

use App\Jobs\UpdateEventStatuses;
use Illuminate\Console\Command;

class UpdateEventStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update event statuses based on start and end times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching event status update job...');
        
        UpdateEventStatuses::dispatch();
        
        $this->info('Event status update job dispatched successfully!');
    }
}
