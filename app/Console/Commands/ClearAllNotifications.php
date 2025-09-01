<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Notification;

class ClearAllNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clear-all 
                            {--force : Skip confirmation prompt}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all notifications from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalNotifications = Notification::count();
        
        if ($totalNotifications === 0) {
            $this->info('No notifications found in the database.');
            return 0;
        }

        $this->warn("⚠️  This will delete ALL {$totalNotifications} notifications from the database!");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No notifications will be deleted.');
            $this->showNotificationSummary();
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete ALL notifications? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info("Deleting {$totalNotifications} notifications...");
        
        try {
            // Show progress bar for large datasets
            if ($totalNotifications > 1000) {
                $bar = $this->output->createProgressBar($totalNotifications);
                $bar->start();
                
                // Delete in chunks to avoid memory issues
                Notification::chunk(1000, function ($notifications) use ($bar) {
                    foreach ($notifications as $notification) {
                        $notification->delete();
                        $bar->advance();
                    }
                });
                
                $bar->finish();
                $this->newLine();
            } else {
                // For smaller datasets, delete all at once
                Notification::truncate();
            }
            
            $this->info("✅ Successfully deleted {$totalNotifications} notifications!");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to delete notifications: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function showNotificationSummary()
    {
        $this->info('Notification Summary:');
        
        $statusCounts = Notification::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        foreach ($statusCounts as $status => $count) {
            $this->line("  {$status}: {$count}");
        }
        
        $this->newLine();
        $this->info('Channel Summary:');
        
        $channelCounts = Notification::selectRaw('channel, count(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel');
        
        foreach ($channelCounts as $channel => $count) {
            $this->line("  {$channel}: {$count}");
        }
    }
}
