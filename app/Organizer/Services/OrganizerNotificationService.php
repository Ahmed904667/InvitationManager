<?php

namespace App\Organizer\Services;

use App\Shared\Models\User;
use App\Shared\Models\Event;
use App\Shared\Models\GuestList;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OTPMail;

class OrganizerNotificationService
{
    /**
     * Send notification to organizer based on their preferences
     */
    public function sendNotification(User $organizer, string $type, array $data = []): bool
    {
        // Check if notifications are muted
        if ($this->isNotificationsMuted($organizer)) {
            return false;
        }

        $preferredPlatform = $this->getPreferredPlatform($organizer);
        
        switch ($preferredPlatform) {
            case 'email':
                return $this->sendEmailNotification($organizer, $type, $data);
            case 'whatsapp':
                return $this->sendWhatsAppNotification($organizer, $type, $data);
            default:
                return false;
        }
    }

    /**
     * Send event update notification
     */
    public function sendEventUpdate(User $organizer, Event $event, string $updateType, array $data = []): bool
    {
        $notificationData = array_merge($data, [
            'event_name' => $event->name,
            'event_date' => $event->event_date,
            'update_type' => $updateType
        ]);

        return $this->sendNotification($organizer, 'event_update', $notificationData);
    }

    /**
     * Send guest list update notification
     */
    public function sendGuestListUpdate(User $organizer, GuestList $guestList, string $updateType, array $data = []): bool
    {
        $notificationData = array_merge($data, [
            'guest_list_name' => $guestList->name,
            'update_type' => $updateType,
            'guest_count' => $guestList->guests()->count()
        ]);

        return $this->sendNotification($organizer, 'guest_list_update', $notificationData);
    }

    /**
     * Send check-in notification
     */
    public function sendCheckInNotification(User $organizer, Event $event, array $checkInData): bool
    {
        $notificationData = array_merge($checkInData, [
            'event_name' => $event->name,
            'event_date' => $event->event_date
        ]);

        return $this->sendNotification($organizer, 'check_in', $notificationData);
    }

    /**
     * Check if notifications are muted for the organizer
     */
    private function isNotificationsMuted(User $organizer): bool
    {
        $settings = $organizer->organizer_settings ?? [];
        return $settings['mute_notifications'] ?? false;
    }

    /**
     * Get the organizer's preferred notification platform
     */
    private function getPreferredPlatform(User $organizer): string
    {
        $settings = $organizer->organizer_settings ?? [];
        $platform = $settings['preferred_platform'] ?? 'email';

        // If WhatsApp is selected but no phone number, fallback to email
        if ($platform === 'whatsapp' && empty($organizer->phone)) {
            return 'email';
        }

        return $platform;
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(User $organizer, string $type, array $data): bool
    {
        try {
            // Here you would implement the actual email sending logic
            // For now, we'll just log it
            Log::info('Email notification sent to organizer', [
                'organizer_id' => $organizer->id,
                'type' => $type,
                'data' => $data
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsAppNotification(User $organizer, string $type, array $data): bool
    {
        // Check if phone number exists
        if (empty($organizer->phone)) {
            Log::warning('Cannot send WhatsApp notification: no phone number', [
                'organizer_id' => $organizer->id
            ]);
            return false;
        }

        try {
            // Here you would implement the actual WhatsApp sending logic
            // For now, we'll just log it
            Log::info('WhatsApp notification sent to organizer', [
                'organizer_id' => $organizer->id,
                'phone' => $organizer->phone,
                'type' => $type,
                'data' => $data
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get organizer notification settings
     */
    public function getNotificationSettings(User $organizer): array
    {
        $settings = $organizer->organizer_settings ?? [];
        
        return [
            'mute_notifications' => $settings['mute_notifications'] ?? false,
            'preferred_platform' => $settings['preferred_platform'] ?? 'email',
            'has_phone' => !empty($organizer->phone),
            'phone' => $organizer->phone
        ];
    }

    /**
     * Update organizer notification settings
     */
    public function updateNotificationSettings(User $organizer, array $settings): bool
    {
        try {
            $currentSettings = $organizer->organizer_settings ?? [];
            $updatedSettings = array_merge($currentSettings, $settings);
            
            $organizer->organizer_settings = $updatedSettings;
            $organizer->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update notification settings', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
