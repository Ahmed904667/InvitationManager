<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:start-worker 
                            {--sleep=3 : Number of seconds to wait when no job is available}
                            {--tries=3 : Number of times to attempt a job before logging it failed}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--memory=128 : The memory limit in megabytes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a continuous queue worker for automated message processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting automated queue worker for scheduled messages...');
        $this->info('This will continuously process scheduled events and reminders automatically.');
        $this->info('Press Ctrl+C to stop the worker.');
        $this->newLine();

        // Start the queue worker with the specified options
        $command = sprintf(
            'php artisan queue:work --sleep=%d --tries=%d --timeout=%d --memory=%d',
            $this->option('sleep'),
            $this->option('tries'),
            $this->option('timeout'),
            $this->option('memory')
        );

        $this->info("Running: {$command}");
        $this->newLine();

        // Execute the queue worker
        passthru($command);
    }
}
