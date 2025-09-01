<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TwilioService;
use App\Shared\Models\Notification;

class UpdateNotificationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update-status 
                            {notification_id : The notification ID to update}
                            {status : The new status (queued, sent, delivered, read, failed, undelivered, canceled)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually update notification status for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationId = $this->argument('notification_id');
        $status = $this->argument('status');

        // Find the notification
        $notification = Notification::find($notificationId);
        
        if (!$notification) {
            $this->error("Notification with ID {$notificationId} not found.");
            return 1;
        }

        if (!$notification->external_id) {
            $this->error("Notification {$notificationId} has no external ID (message_sid).");
            return 1;
        }

        $this->info("Updating notification {$notificationId} status from '{$notification->status}' to '{$status}'...");

        // Use the TwilioService to update the status
        $twilioService = app(TwilioService::class);
        $success = $twilioService->updateNotificationStatusManually($notification->external_id, $status);

        if ($success) {
            // Refresh the notification to get the updated status
            $notification->refresh();
            $this->info("✅ Successfully updated notification status to: {$notification->status}");
        } else {
            $this->error("❌ Failed to update notification status.");
            return 1;
        }

        return 0;
    }
}
