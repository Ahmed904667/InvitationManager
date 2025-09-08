<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\ContactService;
use App\Services\TwilioService;
use App\Shared\Models\Reminder;
use App\Shared\Models\Notification;
use App\Shared\Models\Event;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reminderId;
    public $timeout = 60;
    public $tries = 3; // Allow 3 attempts
    public $backoff = [60, 300, 600]; // Wait 1min, 5min, 10min between retries

    /**
     * Create a new job instance.
     */
    public function __construct(int $reminderId)
    {
        $this->reminderId = $reminderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find the reminder record
            $reminder = Reminder::findOrFail($this->reminderId);
            
            // Check if reminder can still be sent
            if (!$reminder->canBeSent()) {
                Log::info('Reminder cannot be sent', [
                    'reminder_id' => $this->reminderId,
                    'status' => $reminder->status,
                    'scheduled_for' => $reminder->scheduled_for
                ]);
                return;
            }
            
            // Increment attempt count
            $reminder->incrementAttempts();
            
            // Send the reminder based on platform
            if ($reminder->isEmail()) {
                $this->sendEmailReminder($reminder);
            } elseif ($reminder->isWhatsApp()) {
                $this->sendWhatsAppReminder($reminder);
            } else {
                throw new \Exception("Unsupported platform: {$reminder->platform}");
            }
            
            // Mark as sent
            $reminder->markAsSent();
            
            // For localhost environments, manually check and update notification status
            if (str_contains(config('app.url'), 'localhost') || str_contains(config('app.url'), '127.0.0.1')) {
                $this->updateNotificationStatusForLocalhost($reminder);
            }
            
            Log::info('Event reminder sent successfully', [
                'reminder_id' => $this->reminderId,
                'platform' => $reminder->platform,
                'guest_name' => $reminder->guest_name,
                'event_name' => $reminder->event_name
            ]);
            
        } catch (\Exception $e) {
            // Find reminder to mark as failed
            $reminder = Reminder::find($this->reminderId);
            if ($reminder) {
                $reminder->markAsFailed($e->getMessage());
            }
            
            Log::error('Failed to send event reminder', [
                'reminder_id' => $this->reminderId,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Send email reminder
     */
    private function sendEmailReminder(Reminder $reminder): void
    {
        $email = $reminder->guest_email;
        $guestName = $reminder->guest_name;
        $eventName = $reminder->event_name;
        
        // Get the event to access organizer's timezone
        $event = Event::find($reminder->event_id);
        $organizerTimezone = $event ? $event->user->timezone ?? 'UTC' : 'UTC';
        
        // Format event date in organizer's timezone
        $eventDate = \Carbon\Carbon::parse($reminder->event_date)->setTimezone($organizerTimezone)->format('l, F j, Y \a\t g:i A');
        
        $subject = "Reminder: {$eventName} starts soon!";
        
        $message = "Hi {$guestName}!\n\n";
        $message .= "This is a friendly reminder that your event is coming up:\n\n";
        $message .= "📅 Event: {$eventName}\n";
        $message .= "🕐 Date & Time: {$eventDate}\n\n";
        $message .= "Don't forget to mark your calendar and set aside time to attend!\n\n";
        $message .= "Best regards,\nGuest Manager";
        
        // Update reminder with message content
        $reminder->update([
            'message_content' => $message,
            'subject' => $subject
        ]);
        
        // Send email using Laravel's Mail facade
        Mail::raw($message, function($mail) use ($email, $subject) {
            $mail->to($email)
                 ->subject($subject);
        });
        
        // Create notification record for tracking
        if ($event) {
            Notification::create([
                'event_id' => $event->id,
                'guest_id' => null, // We don't have guest_id in reminder
                'user_id' => $reminder->user_id ?? 1, // Default to system user if not set
                'type' => Notification::TYPE_EVENT_REMINDER,
                'channel' => 'email',
                'message' => $message,
                'status' => Notification::STATUS_DELIVERED, // Emails are considered delivered when sent
                'sent_at' => now(),
                'delivery_details' => [
                    'reminder_id' => $reminder->id,
                    'guest_email' => $email,
                    'guest_name' => $guestName,
                    'subject' => $subject
                ]
            ]);
        }
    }

    /**
     * Send WhatsApp reminder
     */
    private function sendWhatsAppReminder(Reminder $reminder): void
    {
        $phone = $reminder->guest_phone;
        $guestName = $reminder->guest_name;
        $eventName = $reminder->event_name;
        
        // Get the event to access organizer's timezone
        $event = Event::find($reminder->event_id);
        $organizerTimezone = $event ? $event->user->timezone ?? 'UTC' : 'UTC';
        
        // Format event date in organizer's timezone
        $eventDate = \Carbon\Carbon::parse($reminder->event_date)->setTimezone($organizerTimezone)->format('l, F j, Y \a\t g:i A');
        
        $message = "Hi {$guestName}! 👋\n\n";
        $message .= "⏰ *Event Reminder*\n\n";
        $message .= "Your event is coming up:\n";
        $message .= "📅 *{$eventName}*\n";
        $message .= "🕐 *{$eventDate}*\n\n";
        $message .= "Don't forget to mark your calendar and set aside time to attend!\n\n";
        $message .= "Best regards,\nGuest Manager";
        
        // Update reminder with message content
        $reminder->update([
            'message_content' => $message
        ]);
        
        // Use Twilio service to send WhatsApp message
        $twilioService = app(TwilioService::class);
        $result = $twilioService->sendWhatsAppMessage($phone, $message);
        
        // Create notification record for tracking
        if ($result['success']) {
            if ($event) {
                Notification::create([
                    'event_id' => $event->id,
                    'guest_id' => null, // We don't have guest_id in reminder
                    'user_id' => $reminder->user_id ?? 1, // Default to system user if not set
                    'type' => Notification::TYPE_EVENT_REMINDER,
                    'channel' => 'whatsapp',
                    'message' => $message,
                    'status' => Notification::STATUS_QUEUED, // Will be updated by webhook
                    'external_id' => $result['message_sid'],
                    'sent_at' => now(),
                    'delivery_details' => [
                        'reminder_id' => $reminder->id,
                        'guest_phone' => $phone,
                        'guest_name' => $guestName,
                        'twilio_status' => $result['status'],
                        'twilio_response' => $result
                    ]
                ]);
            }
        }
    }

    /**
     * Update notification status for localhost environments where webhooks don't work
     */
    private function updateNotificationStatusForLocalhost(Reminder $reminder): void
    {
        try {
            // Find the notification for this reminder
            $notification = Notification::where('delivery_details->reminder_id', $reminder->id)
                ->where('channel', 'whatsapp')
                ->first();
            
            if ($notification && $notification->external_id) {
                // Use Twilio service to get current message status
                $twilioService = app(\App\Services\TwilioService::class);
                $messageStatus = $twilioService->getMessageStatus($notification->external_id);
                
                if ($messageStatus) {
                    // Update notification status based on Twilio status
                    switch (strtolower($messageStatus->status)) {
                        case 'delivered':
                        case 'sent':
                            $notification->markAsDelivered($notification->external_id);
                            break;
                        case 'failed':
                        case 'undelivered':
                            $notification->markAsFailed('Message delivery failed');
                            break;
                        default:
                            // Keep as queued for intermediate statuses
                            break;
                    }
                    
                    Log::info('Updated notification status for localhost', [
                        'notification_id' => $notification->id,
                        'twilio_status' => $messageStatus->status,
                        'final_status' => $notification->status
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update notification status for localhost', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Find reminder to mark as failed
        $reminder = Reminder::find($this->reminderId);
        if ($reminder) {
            $reminder->markAsFailed($exception->getMessage());
        }
        
        Log::error('Event reminder job failed', [
            'reminder_id' => $this->reminderId,
            'error' => $exception->getMessage()
        ]);
    }
} 