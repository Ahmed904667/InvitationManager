<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Shared\Models\Invitation;
use App\Services\TwilioService;

class SyncInvitationSIDs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:sync-sids {--force : Force update even if external_id exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all WhatsApp invitations with their Twilio SIDs from logs and update statuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting invitation SID synchronization...');
        
        // Get all WhatsApp invitations
        $query = Invitation::where('channel', 'whatsapp');
        
        if (!$this->option('force')) {
            $query->whereNull('external_id');
        }
        
        $invitations = $query->get();
        
        $this->info("Found {$invitations->count()} WhatsApp invitations to process.");
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($invitations as $invitation) {
            try {
                $this->line("Processing invitation ID: {$invitation->id} (Token: {$invitation->token})");
                
                // Try to find SID from logs around the creation time
                $sid = $this->findSIDFromLogs($invitation);
                
                if ($sid) {
                    $invitation->update(['external_id' => $sid]);
                    $this->info("  ✓ Found SID: {$sid}");
                    
                    // Now get current status from Twilio
                    $twilioService = app(TwilioService::class);
                    $messageStatus = $twilioService->getMessageStatus($sid);
                    
                    if ($messageStatus) {
                        $this->updateInvitationStatus($invitation, $messageStatus);
                        $this->info("  ✓ Updated status to: {$invitation->fresh()->status}");
                        $updatedCount++;
                    } else {
                        $this->warn("  ⚠ Could not retrieve status from Twilio for SID: {$sid}");
                    }
                } else {
                    $this->warn("  ⚠ No SID found in logs for invitation ID: {$invitation->id}");
                }
                
            } catch (\Exception $e) {
                $errorMsg = "Error processing invitation {$invitation->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $this->error("  ✗ {$errorMsg}");
            }
        }
        
        $this->newLine();
        $this->info("Synchronization completed!");
        $this->info("Successfully updated: {$updatedCount} invitations");
        
        if (!empty($errors)) {
            $this->warn("Errors encountered: " . count($errors));
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->error("  - {$error}");
            }
            if (count($errors) > 5) {
                $this->warn("  ... and " . (count($errors) - 5) . " more errors");
            }
        }
        
        return 0;
    }
    
    private function findSIDFromLogs($invitation)
    {
        // Look for SID in logs around the invitation creation time
        $createdAt = $invitation->created_at;
        $timeRange = 30; // seconds before and after
        
        $startTime = $createdAt->copy()->subSeconds($timeRange);
        $endTime = $createdAt->copy()->addSeconds($timeRange);
        
        // Search for WhatsApp message sent successfully logs
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return null;
        }
        
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (strpos($line, 'WhatsApp message sent successfully') !== false) {
                // Extract timestamp from log line
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $logTime = \Carbon\Carbon::parse($matches[1]);
                    
                    if ($logTime->between($startTime, $endTime)) {
                        // Extract SID from log line
                        if (preg_match('/"message_sid":"([^"]+)"/', $line, $sidMatches)) {
                            return $sidMatches[1];
                        }
                    }
                }
            }
        }
        
        return null;
    }
    
    private function updateInvitationStatus($invitation, $twilioMessage)
    {
        $existingDetails = $invitation->delivery_details ?? [];
        $deliveryDetails = array_merge($existingDetails, [
            'twilio_status' => $twilioMessage->status,
            'updated_at' => now()->toISOString(),
            'last_checked' => now()->toISOString()
        ]);
        
        switch (strtolower($twilioMessage->status)) {
            case 'queued':
            case 'sending':
            case 'accepted':
            case 'scheduled':
            case 'partially_delivered':
            case 'receiving':
            case 'received':
                $invitation->update(['status' => 'queued', 'delivery_details' => $deliveryDetails]);
                break;
            case 'sent':
                $invitation->update(['status' => 'delivered', 'sent_at' => now(), 'delivery_details' => $deliveryDetails]);
                break;
            case 'delivered':
                $invitation->update(['status' => 'delivered', 'sent_at' => now(), 'delivery_details' => $deliveryDetails]);
                break;
            case 'read':
                if ($invitation->channel === 'whatsapp') {
                    $invitation->update(['status' => 'read', 'delivery_details' => $deliveryDetails]);
                } else {
                    $invitation->update(['delivery_details' => $deliveryDetails]);
                }
                break;
            case 'undelivered':
            case 'failed':
            case 'canceled':
                $invitation->update(['status' => 'failed', 'delivery_details' => $deliveryDetails]);
                break;
            default:
                $invitation->update(['delivery_details' => $deliveryDetails]);
                break;
        }
    }
}
