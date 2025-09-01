<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Notification;
use App\Services\TwilioService;

class UpdateWhatsAppNotificationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update-whatsapp-statuses 
                            {--force : Skip confirmation prompt}
                            {--dry-run : Show what would be updated without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update WhatsApp notification statuses by checking with Twilio API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twilioService = app(TwilioService::class);
        
        // Find all queued WhatsApp notifications
        $queuedNotifications = Notification::where('channel', 'whatsapp')
            ->where('status', 'queued')
            ->get();
        
        if ($queuedNotifications->isEmpty()) {
            $this->info('No queued WhatsApp notifications found.');
            return 0;
        }
        
        $this->info("Found {$queuedNotifications->count()} queued WhatsApp notifications.");
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No notifications will be updated.');
            $this->showNotificationSummary($queuedNotifications);
            return 0;
        }
        
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to update these notification statuses?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        $this->info('Updating notification statuses...');
        
        $bar = $this->output->createProgressBar($queuedNotifications->count());
        $bar->start();
        
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($queuedNotifications as $notification) {
            try {
                // Check if we have an external_id (Twilio message SID)
                if (!$notification->external_id) {
                    // Try to extract from delivery_details
                    $deliveryDetails = $notification->delivery_details ?? [];
                    $messageSid = $deliveryDetails['twilio_response']['message_sid'] ?? null;
                    
                    if ($messageSid) {
                        $notification->update(['external_id' => $messageSid]);
                    } else {
                        $this->warn("No message SID found for notification ID: {$notification->id}");
                        $bar->advance();
                        continue;
                    }
                }
                
                // Get current status from Twilio
                $messageStatus = $twilioService->getMessageStatus($notification->external_id);
                
                if ($messageStatus) {
                    $oldStatus = $notification->status;
                    
                    // Update status based on Twilio response
                    switch (strtolower($messageStatus->status)) {
                        case 'delivered':
                        case 'sent':
                            $notification->markAsDelivered($notification->external_id);
                            break;
                        case 'failed':
                        case 'undelivered':
                            $notification->markAsFailed('Message delivery failed');
                            break;
                        case 'read':
                            if ($notification->channel === 'whatsapp') {
                                $notification->markAsRead();
                            }
                            break;
                        default:
                            // Keep current status for intermediate statuses
                            break;
                    }
                    
                    if ($oldStatus !== $notification->status) {
                        $updatedCount++;
                        $this->line("\nUpdated notification ID {$notification->id}: {$oldStatus} → {$notification->status}");
                    }
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("\nFailed to update notification ID {$notification->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("✅ Successfully updated {$updatedCount} notifications.");
        if ($errorCount > 0) {
            $this->warn("⚠️  Failed to update {$errorCount} notifications.");
        }
        
        return 0;
    }
    
    private function showNotificationSummary($notifications)
    {
        $this->newLine();
        $this->info('Notification Summary:');
        
        foreach ($notifications as $notification) {
            $deliveryDetails = $notification->delivery_details ?? [];
            $messageSid = $deliveryDetails['twilio_response']['message_sid'] ?? 'Not found';
            $twilioStatus = $deliveryDetails['twilio_status'] ?? 'Unknown';
            
            $this->line("  ID: {$notification->id}, Message SID: {$messageSid}, Twilio Status: {$twilioStatus}");
        }
    }
}
