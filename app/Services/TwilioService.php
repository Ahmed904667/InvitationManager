<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    private Client $client;
    private string $fromNumber;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
        $this->fromNumber = str_replace('whatsapp:', '', config('services.twilio.whatsapp_from'));
    }

    public function sendWhatsAppMessage(string $to, string $message): array
    {
        try {
            // Format the phone number for WhatsApp
            $formattedTo = $this->formatPhoneNumber($to);
            
            // Prepare message parameters
            $messageParams = [
                'from' => config('services.twilio.whatsapp_from'),
                'body' => $message
            ];
            
            // Try to get webhook URL for status updates, but don't fail if it's not available
            try {
                $webhookUrl = $this->getWebhookUrl();
                if ($webhookUrl) {
                    $messageParams['statusCallback'] = $webhookUrl;
                    $messageParams['statusCallbackMethod'] = 'POST';
                    Log::info('WhatsApp webhook configured', [
                        'webhook_url' => $webhookUrl
                    ]);
                } else {
                    Log::info('WhatsApp webhook not configured (localhost or no valid URL)');
                }
            } catch (\Exception $webhookError) {
                Log::warning('Failed to configure WhatsApp webhook, continuing without it', [
                    'error' => $webhookError->getMessage()
                ]);
                // Continue without webhook - don't fail the entire message
            }
            
            // Send WhatsApp message
            $twilioMessage = $this->client->messages->create(
                "whatsapp:{$formattedTo}",
                $messageParams
            );

            Log::info('WhatsApp message sent successfully', [
                'to' => $formattedTo,
                'message_sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status
            ]);

            return [
                'success' => true,
                'message_sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
                'to' => $formattedTo
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function sendSMSMessage(string $to, string $message): bool
    {
        try {
            // Format the phone number for SMS
            $formattedTo = $this->formatPhoneNumber($to);
            
            // Prepare message parameters
            $messageParams = [
                'from' => $this->fromNumber,
                'body' => $message
            ];
            
            // Try to get webhook URL for status updates, but don't fail if it's not available
            try {
                $webhookUrl = $this->getWebhookUrl();
                if ($webhookUrl) {
                    $messageParams['statusCallback'] = $webhookUrl;
                    $messageParams['statusCallbackMethod'] = 'POST';
                    Log::info('SMS webhook configured', [
                        'webhook_url' => $webhookUrl
                    ]);
                } else {
                    Log::info('SMS webhook not configured (localhost or no valid URL)');
                }
            } catch (\Exception $webhookError) {
                Log::warning('Failed to configure SMS webhook, continuing without it', [
                    'error' => $webhookError->getMessage()
                ]);
                // Continue without webhook - don't fail the entire message
            }
            
            // Send SMS message
            $message = $this->client->messages->create(
                $formattedTo,
                $messageParams
            );

            Log::info('SMS message sent successfully', [
                'to' => $formattedTo,
                'message_sid' => $message->sid,
                'status' => $message->status
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS message', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // If it starts with +, keep it as is
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        
        // If it starts with 0, replace with country code
        if (strlen($phone) === 10 && $phone[0] === '0') {
            return '+966' . substr($phone, 1);
        }
        
        // If it doesn't have country code, assume Saudi Arabia (+966)
        if (strlen($phone) === 9) {
            return '+966' . $phone;
        }
        
        // If it's 10 digits and doesn't start with 0, assume it's a US number
        if (strlen($phone) === 10) {
            return '+1' . $phone;
        }
        
        // If it's already 11+ digits, add + prefix
        if (strlen($phone) >= 11) {
            return '+' . $phone;
        }
        
        // Default: assume Saudi Arabia
        return '+966' . $phone;
    }

    public function validatePhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        return strlen($formatted) >= 12 && strlen($formatted) <= 15;
    }
    
    /**
     * Get webhook URL for Twilio status callbacks
     * Returns null if localhost (Twilio doesn't accept localhost URLs)
     */
    private function getWebhookUrl(): ?string
    {
        // Check if we have a custom webhook URL configured
        $customWebhookUrl = config('services.twilio.webhook_url');
        if ($customWebhookUrl) {
            return $customWebhookUrl;
        }
        
        // Try to generate the webhook URL, but handle route resolution errors gracefully
        try {
            // Use app() helper to ensure we're in the right context
            $webhookUrl = app('url')->to('/webhooks/twilio/status');
            
            // Check if it's localhost (Twilio doesn't accept localhost URLs)
            if (str_contains($webhookUrl, 'localhost') || str_contains($webhookUrl, '127.0.0.1')) {
                Log::info('Skipping webhook for localhost environment', [
                    'webhook_url' => $webhookUrl
                ]);
                return null;
            }
            
            return $webhookUrl;
        } catch (\Exception $e) {
            Log::warning('Failed to resolve webhook route, continuing without webhook', [
                'error' => $e->getMessage(),
                'route_name' => 'webhooks.twilio.status'
            ]);
            return null;
        }
    }
    
    /**
     * Manually update notification status for local development
     * This can be called when webhooks are not available
     */
    public function updateNotificationStatusManually(string $messageSid, string $status): bool
    {
        try {
            $notification = \App\Shared\Models\Notification::where('external_id', $messageSid)->first();
            
            if (!$notification) {
                Log::warning('Notification not found for manual status update', [
                    'message_sid' => $messageSid,
                    'status' => $status
                ]);
                return false;
            }
            
            // Create a mock Twilio message object
            $mockMessage = (object) [
                'status' => $status,
                'errorCode' => null,
                'errorMessage' => null
            ];
            
            // Use the existing webhook controller logic
            $webhookController = new \App\Http\Controllers\TwilioWebhookController();
            $reflection = new \ReflectionClass($webhookController);
            $method = $reflection->getMethod('updateNotificationStatus');
            $method->setAccessible(true);
            $method->invoke($webhookController, $notification, $mockMessage);
            
            Log::info('Manual notification status update completed', [
                'message_sid' => $messageSid,
                'status' => $status,
                'notification_id' => $notification->id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to manually update notification status', [
                'message_sid' => $messageSid,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get the current status of a message from Twilio
     */
    public function getMessageStatus(string $messageSid): ?object
    {
        try {
            $message = $this->client->messages($messageSid)->fetch();
            
            Log::info('Retrieved message status from Twilio', [
                'message_sid' => $messageSid,
                'status' => $message->status,
                'error_code' => $message->errorCode ?? null,
                'error_message' => $message->errorMessage ?? null
            ]);
            
            return $message;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve message status from Twilio', [
                'message_sid' => $messageSid,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
} 