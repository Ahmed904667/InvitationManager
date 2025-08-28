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

    public function sendWhatsAppMessage(string $to, string $message): bool
    {
        try {
            // Format the phone number for WhatsApp
            $formattedTo = $this->formatPhoneNumber($to);
            
            // Send WhatsApp message with clickable link
            $message = $this->client->messages->create(
                "whatsapp:{$formattedTo}",
                [
                    'from' => config('services.twilio.whatsapp_from'),
                    'body' => $message
                ]
            );

            Log::info('WhatsApp message sent successfully', [
                'to' => $formattedTo,
                'message_sid' => $message->sid,
                'status' => $message->status
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function sendSMSMessage(string $to, string $message): bool
    {
        try {
            // Format the phone number for SMS
            $formattedTo = $this->formatPhoneNumber($to);
            
            // Send SMS message
            $message = $this->client->messages->create(
                $formattedTo,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
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
} 