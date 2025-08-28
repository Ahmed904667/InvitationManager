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
        
        // Format event date in guest's timezone
        $guestTimezone = $reminder->guest_timezone ?? 'UTC';
        $eventDate = $reminder->event_date->setTimezone($guestTimezone)->format('l, F j, Y \a\t g:i A');
        
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
    }

    /**
     * Send WhatsApp reminder
     */
    private function sendWhatsAppReminder(Reminder $reminder): void
    {
        $phone = $reminder->guest_phone;
        $guestName = $reminder->guest_name;
        $eventName = $reminder->event_name;
        
        // Format event date in guest's timezone
        $guestTimezone = $reminder->guest_timezone ?? 'UTC';
        $eventDate = $reminder->event_date->setTimezone($guestTimezone)->format('l, F j, Y \a\t g:i A');
        
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
        $twilioService->sendWhatsAppMessage($phone, $message);
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