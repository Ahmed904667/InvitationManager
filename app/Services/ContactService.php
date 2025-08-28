<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Mail\TrialRequestMail;

class ContactService
{
    /**
     * Detect if contact is email or WhatsApp number
     */
    public function detectContactMethod(string $contact): string
    {
        // Remove any spaces, dashes, or special characters for WhatsApp
        $cleanContact = preg_replace('/[\s\-\(\)]/', '', $contact);
        
        // Check if it's an email
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        
        // Check if it's a WhatsApp number - must start with + for country code
        // Matches: +1234567890, +44123456789, etc.
        if (preg_match('/^\+\d{10,15}$/', $cleanContact)) {
            return 'whatsapp';
        }
        
        // Default to email if we can't determine
        return 'email';
    }
    
    /**
     * Send message via email
     */
    public function sendEmail(string $email, string $name, string $eventType): bool
    {
        try {
            // Send email using the Mailable class
            Mail::to($email)->send(new TrialRequestMail($name, $eventType, $email));
            
            Log::info('Trial email sent successfully', [
                'email' => $email,
                'name' => $name,
                'eventType' => $eventType
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send trial email: ' . $e->getMessage(), [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Send message via WhatsApp using Twilio
     */
    public function sendWhatsApp(string $phone, string $name, string $eventType): bool
    {
        try {
            // Create WhatsApp message
            $message = $this->getWhatsAppTemplate($name, $eventType);
            
            // Use Twilio service to send WhatsApp message
            $twilioService = app(TwilioService::class);
            $success = $twilioService->sendWhatsAppMessage($phone, $message);
            
            if ($success) {
                Log::info('WhatsApp message sent successfully via Twilio', [
                    'phone' => $phone,
                    'name' => $name,
                    'eventType' => $eventType
                ]);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message: ' . $e->getMessage(), [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Clean phone number for WhatsApp
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $clean = preg_replace('/[^\d+]/', '', $phone);
        
        // Ensure it starts with + for country code
        if (!str_starts_with($clean, '+')) {
            // If no + prefix, assume it's a US number and add +1
            $clean = '+1' . $clean;
        }
        
        return $clean;
    }
    
    /**
     * Get WhatsApp template
     */
    public function getWhatsAppTemplate(string $name, string $eventType): string
    {
        // Use different URLs based on environment
        $appUrl = $this->getAppUrl();
        
        return "Hi {$name}! 👋\n\nThank you for your interest in Guest Manager for your {$eventType} event!\n\nWe're excited to help you create an amazing experience for your guests.\n\n🌐 *Visit our platform:*\n{$appUrl}\n\n*If the link above doesn't work, copy and paste it into your browser*\n\nBest regards,\nGuest Manager Team";
    }

    /**
     * Get the appropriate app URL based on environment
     */
    private function getAppUrl(): string
    {
        // Check if we want to force localhost
        $useLocalhost = env('USE_LOCALHOST', 'false');
        if ($useLocalhost === 'true' || $useLocalhost === true || $useLocalhost === 1) {
            return 'http://localhost:8000';
        }
        
        // Use NGROK_URL if available and USE_LOCALHOST is false
        $ngrokUrl = env('NGROK_URL');
        if (!empty($ngrokUrl)) {
            return $ngrokUrl;
        }
        
        // Use the configured APP_URL (ngrok or production)
        return env('APP_URL', 'http://localhost:8000');
    }

    /**
     * Get the current active URL (public method for admin panel)
     */
    public function getCurrentActiveUrl(): string
    {
        return $this->getAppUrl();
    }

    /**
     * Get current URL settings for admin panel
     */
    public function getUrlSettings(): array
    {
        $useLocalhost = env('USE_LOCALHOST', 'false');
        $isLocalhost = $useLocalhost === 'true' || $useLocalhost === true || $useLocalhost === 1;
        
        return [
            'use_localhost' => $isLocalhost,
            'app_url' => env('APP_URL', 'http://localhost:8000'),
            'ngrok_url' => env('NGROK_URL', ''),
            'current_active_url' => $this->getCurrentActiveUrl(),
        ];
    }
    
    /**
     * Send message via detected method
     */
    public function sendMessage(string $contact, string $name, string $eventType): array
    {
        $method = $this->detectContactMethod($contact);
        
        // Clean phone number if it's WhatsApp
        $cleanContact = $contact;
        if ($method === 'whatsapp') {
            $cleanContact = $this->cleanPhoneNumber($contact);
        }
        
        $success = match($method) {
            'email' => $this->sendEmail($cleanContact, $name, $eventType),
            'whatsapp' => $this->sendWhatsApp($cleanContact, $name, $eventType),
            default => false
        };
        
        return [
            'method' => $method,
            'success' => $success,
            'contact' => $cleanContact
        ];
    }
} 