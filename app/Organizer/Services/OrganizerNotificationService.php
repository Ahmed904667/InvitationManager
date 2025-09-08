<?php

namespace App\Organizer\Services;

use App\Shared\Models\User;
use App\Shared\Models\Event;
use App\Shared\Models\GuestList;
use App\Shared\Models\Notification;
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
            'event_date' => $event->start_date
        ]);

        return $this->sendNotification($organizer, 'check_in', $notificationData);
    }

    /**
     * Send event start reminder notification
     */
    public function sendEventStartReminder(User $organizer, Event $event, int $minutesBefore = 30): bool
    {
        $notificationData = [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'event_date' => $event->start_date,
            'event_location' => $event->location,
            'minutes_before' => $minutesBefore,
            'total_guests' => $event->eventGuests()->where('status', \App\EventGuest::STATUS_ACTIVE)->count(),
            'rsvp_yes' => $event->invitations()->where('rsvp_status', 'yes')->count(),
            'rsvp_maybe' => $event->invitations()->where('rsvp_status', 'maybe')->count(),
        ];

        return $this->sendNotification($organizer, 'event_start_reminder', $notificationData);
    }

    /**
     * Send RSVP response notification
     */
    public function sendRsvpNotification(User $organizer, Event $event, \App\Shared\Models\Guest $guest, string $rsvpStatus, ?string $rsvpNote = null): bool
    {
        $notificationData = [
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'event_name' => $event->name,
            'event_date' => $event->start_date,
            'guest_name' => $guest->name,
            'guest_email' => $guest->email,
            'rsvp_status' => $rsvpStatus,
            'rsvp_note' => $rsvpNote,
            'total_rsvp_yes' => $event->invitations()->where('rsvp_status', 'yes')->count(),
            'total_rsvp_maybe' => $event->invitations()->where('rsvp_status', 'maybe')->count(),
            'total_rsvp_no' => $event->invitations()->where('rsvp_status', 'no')->count(),
        ];

        return $this->sendNotification($organizer, 'rsvp_response', $notificationData);
    }

    /**
     * Check if notifications are muted for the organizer
     */
    private function isNotificationsMuted(User $organizer): bool
    {
        $settings = $organizer->notification_settings ?? [];
        return $settings['mute_notifications'] ?? false;
    }

    /**
     * Get the organizer's preferred notification platform
     */
    private function getPreferredPlatform(User $organizer): string
    {
        $settings = $organizer->notification_settings ?? [];
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
            $subject = $this->getEmailSubject($type, $data);
            $message = $this->getEmailMessage($type, $data);

            // Send email using Laravel's Mail facade
            Mail::raw($message, function($mail) use ($organizer, $subject) {
                $mail->to($organizer->email)
                     ->subject($subject);
            });

            // Create notification record
            $this->createNotificationRecord($organizer, $type, 'email', $message, $data);

            Log::info('Email notification sent to organizer', [
                'organizer_id' => $organizer->id,
                'type' => $type,
                'subject' => $subject
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'organizer_id' => $organizer->id,
                'type' => $type,
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
            $message = $this->getWhatsAppMessage($type, $data);

            // Use Twilio service to send WhatsApp message
            $twilioService = app(\App\Services\TwilioService::class);
            $result = $twilioService->sendWhatsAppMessage($organizer->phone, $message);

            if ($result['success']) {
                // Create notification record
                $this->createNotificationRecord($organizer, $type, 'whatsapp', $message, $data, $result['message_sid'] ?? null);

                Log::info('WhatsApp notification sent to organizer', [
                    'organizer_id' => $organizer->id,
                    'phone' => $organizer->phone,
                    'type' => $type,
                    'message_sid' => $result['message_sid'] ?? null
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp notification via Twilio', [
                    'organizer_id' => $organizer->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                return false;
            }
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
        $settings = $organizer->notification_settings ?? [];
        
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
            $currentSettings = $organizer->notification_settings ?? [];
            $updatedSettings = array_merge($currentSettings, $settings);
            
            $organizer->notification_settings = $updatedSettings;
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

    /**
     * Get email subject for notification type
     */
    private function getEmailSubject(string $type, array $data): string
    {
        switch ($type) {
            case 'event_start_reminder':
                $minutes = $data['minutes_before'] ?? 30;
                return "🚀 Event Starting Soon: {$data['event_name']} ({$minutes} minutes)";
            
            case 'rsvp_response':
                $status = ucfirst($data['rsvp_status']);
                return "📋 New RSVP Response: {$data['guest_name']} - {$status}";
            
            case 'check_in':
                return "✅ Guest Checked In: {$data['guest_name']} at {$data['event_name']}";
            
            case 'event_update':
                return "📝 Event Update: {$data['event_name']}";
            
            case 'guest_list_update':
                return "👥 Guest List Update: {$data['guest_list_name']}";
            
            default:
                return "🔔 Guest Manager Notification";
        }
    }

    /**
     * Get email message for notification type
     */
    private function getEmailMessage(string $type, array $data): string
    {
        switch ($type) {
            case 'event_start_reminder':
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y \a\t g:i A');
                $minutes = $data['minutes_before'] ?? 30;
                
                $message = "Hi there!\n\n";
                $message .= "🚀 Your event is starting soon!\n\n";
                $message .= "📅 Event: {$data['event_name']}\n";
                $message .= "🕐 Date & Time: {$eventDate}\n";
                if (!empty($data['event_location'])) {
                    $message .= "📍 Location: {$data['event_location']}\n";
                }
                $message .= "⏰ Starting in: {$minutes} minutes\n\n";
                $message .= "📊 Quick Stats:\n";
                $message .= "• Total Guests: {$data['total_guests']}\n";
                $message .= "• RSVP Yes: {$data['rsvp_yes']}\n";
                $message .= "• RSVP Maybe: {$data['rsvp_maybe']}\n\n";
                $message .= "Everything is ready to go! Have a great event!\n\n";
                $message .= "Best regards,\nGuest Manager";
                
                return $message;
            
            case 'rsvp_response':
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y \a\t g:i A');
                $status = ucfirst($data['rsvp_status']);
                $statusEmoji = $this->getRsvpStatusEmoji($data['rsvp_status']);
                
                $message = "Hi there!\n\n";
                $message .= "📋 New RSVP Response Received!\n\n";
                $message .= "👤 Guest: {$data['guest_name']}\n";
                $message .= "📧 Email: {$data['guest_email']}\n";
                $message .= "📅 Event: {$data['event_name']}\n";
                $message .= "🕐 Date: {$eventDate}\n";
                $message .= "{$statusEmoji} Response: {$status}\n";
                
                if (!empty($data['rsvp_note'])) {
                    $message .= "💬 Note: {$data['rsvp_note']}\n";
                }
                
                $message .= "\n📊 Current RSVP Summary:\n";
                $message .= "• Yes: {$data['total_rsvp_yes']}\n";
                $message .= "• Maybe: {$data['total_rsvp_maybe']}\n";
                $message .= "• No: {$data['total_rsvp_no']}\n\n";
                $message .= "Best regards,\nGuest Manager";
                
                return $message;
            
            case 'check_in':
                $checkInTime = \Carbon\Carbon::now()->format('g:i A');
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y');
                
                $message = "Hi there!\n\n";
                $message .= "✅ Guest Checked In!\n\n";
                $message .= "👤 Guest: {$data['guest_name']}\n";
                $message .= "📅 Event: {$data['event_name']}\n";
                $message .= "📅 Date: {$eventDate}\n";
                $message .= "🕐 Check-in Time: {$checkInTime}\n\n";
                $message .= "Your event is in full swing!\n\n";
                $message .= "Best regards,\nGuest Manager";
                
                return $message;
            
            default:
                return "You have a new notification from Guest Manager.";
        }
    }

    /**
     * Get WhatsApp message for notification type
     */
    private function getWhatsAppMessage(string $type, array $data): string
    {
        switch ($type) {
            case 'event_start_reminder':
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y \a\t g:i A');
                $minutes = $data['minutes_before'] ?? 30;
                
                $message = "🚀 *Event Starting Soon!*\n\n";
                $message .= "📅 *{$data['event_name']}*\n";
                $message .= "🕐 *{$eventDate}*\n";
                if (!empty($data['event_location'])) {
                    $message .= "📍 *{$data['event_location']}*\n";
                }
                $message .= "⏰ *Starting in {$minutes} minutes*\n\n";
                $message .= "📊 *Quick Stats:*\n";
                $message .= "• Total Guests: {$data['total_guests']}\n";
                $message .= "• RSVP Yes: {$data['rsvp_yes']}\n";
                $message .= "• RSVP Maybe: {$data['rsvp_maybe']}\n\n";
                $message .= "Everything is ready to go! Have a great event! 🎉";
                
                return $message;
            
            case 'rsvp_response':
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y \a\t g:i A');
                $status = ucfirst($data['rsvp_status']);
                $statusEmoji = $this->getRsvpStatusEmoji($data['rsvp_status']);
                
                $message = "📋 *New RSVP Response!*\n\n";
                $message .= "👤 *{$data['guest_name']}*\n";
                $message .= "📧 {$data['guest_email']}\n";
                $message .= "📅 *{$data['event_name']}*\n";
                $message .= "🕐 *{$eventDate}*\n";
                $message .= "{$statusEmoji} *Response: {$status}*\n";
                
                if (!empty($data['rsvp_note'])) {
                    $message .= "💬 *Note:* {$data['rsvp_note']}\n";
                }
                
                $message .= "\n📊 *Current RSVP Summary:*\n";
                $message .= "• Yes: {$data['total_rsvp_yes']}\n";
                $message .= "• Maybe: {$data['total_rsvp_maybe']}\n";
                $message .= "• No: {$data['total_rsvp_no']}";
                
                return $message;
            
            case 'check_in':
                $checkInTime = \Carbon\Carbon::now()->format('g:i A');
                $eventDate = \Carbon\Carbon::parse($data['event_date'])->format('l, F j, Y');
                
                $message = "✅ *Guest Checked In!*\n\n";
                $message .= "👤 *{$data['guest_name']}*\n";
                $message .= "📅 *{$data['event_name']}*\n";
                $message .= "📅 *{$eventDate}*\n";
                $message .= "🕐 *Check-in Time: {$checkInTime}*\n\n";
                $message .= "Your event is in full swing! 🎉";
                
                return $message;
            
            default:
                return "🔔 You have a new notification from Guest Manager.";
        }
    }

    /**
     * Get emoji for RSVP status
     */
    private function getRsvpStatusEmoji(string $status): string
    {
        switch ($status) {
            case 'yes':
                return '✅';
            case 'maybe':
                return '❓';
            case 'no':
                return '❌';
            default:
                return '📋';
        }
    }

    /**
     * Create notification record in database
     */
    private function createNotificationRecord(User $organizer, string $type, string $channel, string $message, array $data, ?string $externalId = null): void
    {
        try {
            $notificationType = $this->mapNotificationType($type);
            
            Notification::create([
                'event_id' => $data['event_id'] ?? null,
                'guest_id' => $data['guest_id'] ?? null,
                'user_id' => $organizer->id,
                'type' => $notificationType,
                'channel' => $channel,
                'message' => $message,
                'status' => $channel === 'email' ? Notification::STATUS_DELIVERED : Notification::STATUS_QUEUED,
                'external_id' => $externalId,
                'sent_at' => now(),
                'delivery_details' => [
                    'organizer_id' => $organizer->id,
                    'organizer_name' => $organizer->name,
                    'notification_type' => $type,
                    'data' => $data
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification record', [
                'organizer_id' => $organizer->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Map notification type to database enum value
     */
    private function mapNotificationType(string $type): string
    {
        switch ($type) {
            case 'event_start_reminder':
                return Notification::TYPE_EVENT_START_REMINDER;
            case 'rsvp_response':
                return Notification::TYPE_RSVP_RESPONSE;
            case 'check_in':
                return Notification::TYPE_CUSTOM; // Use custom for check-ins
            case 'event_update':
                return Notification::TYPE_EVENT_UPDATE;
            case 'guest_list_update':
                return Notification::TYPE_CUSTOM; // Use custom for guest list updates
            default:
                return Notification::TYPE_CUSTOM;
        }
    }
}
