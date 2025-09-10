<?php

namespace App\Http\Controllers;

use App\Mail\TrialRequestMail;
use App\Trial;
use App\Services\InvitationMessageService;
use App\Services\TrialEventGenerationService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TrialController extends Controller
{
    public function store(Request $request)
    {
        // Robust server-side validation
        $validator = Validator::make($request->all(), [
            'contact' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (strpos($value, '@') !== false) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Please enter a valid email address.');
                    }
                } else {
                    $clean = preg_replace('/[\s\-\(\)]/', '', $value);
                    if (!preg_match('/^\+\d{10,15}$/', $clean)) {
                        $fail('Please enter a valid phone number with country code (e.g., +1234567890).');
                    }
                }
            }],
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'event_type' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[\p{L}\s\-\'\.\d]+$/u'],
        ], [
            'name.regex' => 'Name contains invalid characters.',
            'event_type.regex' => 'Event type contains invalid characters.',
        ]);

        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $firstError = $allErrors[0] ?? 'Validation error.';
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => [$firstError]
            ], 422);
        }

        // Limit: 3 trials per IP per day
        $ip = $request->ip();
        $today = now()->startOfDay();
        $trialCount = \App\Trial::where('ip_address', $ip)
            ->where('created_at', '>=', $today)
            ->where('status', 'pending') // or whatever status means 'success' in your app
            ->count();

        Log::info('Trial count for IP', ['ip' => $ip, 'count' => $trialCount]);
        
        if ($trialCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of trial requests 😔. You can try again tomorrow 😁.',
                'errors' => ['You have reached the maximum number of trial requests 😔.']
            ], 429);
        }

        try {
            $invitationMessageService = new InvitationMessageService();
            $result = $invitationMessageService->generateInvitationMessage(
                $request->contact,
                $request->name,
                $request->event_type
            );

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            // Generate sample event data using AI
            $eventGenerationService = new TrialEventGenerationService();
            $sampleEventData = $eventGenerationService->generateSampleEventData(
                $request->event_type,
                $request->name
            );

            $trial = Trial::create([
                'contact' => $request->contact,
                'name' => $request->name,
                'eventType' => $request->event_type,
                'contact_method' => $result['contact_method'],
                'invitation_message' => $result['invitation_message'],
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'sample_event_data' => $sampleEventData,
                'invite_token' => Str::random(32)
            ]);

            if ($result['contact_method'] === 'email') {
                Mail::to($request->contact)->send(new TrialRequestMail(
                    $request->name,
                    $request->event_type,
                    $request->contact,
                    $result['invitation_message'],
                    $result['subject'] ?? null,
                    $trial->invite_url
                ));
                // Update status to message_sent after email sent
                $trial->status = 'message_sent';
                $trial->save();
            } else {
                $twilioService = new TwilioService();
                // Add invite link to WhatsApp message
                $whatsappMessage = $result['invitation_message'] . "\n\n🔗 View your invitation: " . $trial->invite_url;
                $whatsappSent = $twilioService->sendWhatsAppMessage(
                    $request->contact,
                    $whatsappMessage
                );
                if ($whatsappSent) {
                    // Update status to message_sent after WhatsApp sent
                    $trial->status = 'message_sent';
                    $trial->save();
                } else {
                    Log::error('Failed to send WhatsApp message', [
                        'contact' => $request->contact,
                        'message' => $whatsappMessage
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Your trial request has been sent successfully!',
                'contact_method' => $result['contact_method'],
                'ai_service' => $result['ai_service'],
                'invite_url' => $trial->invite_url
            ]);

        } catch (\Exception $e) {
            Log::error('Trial request failed', [
                'error' => $e->getMessage(),
                'contact' => $request->contact ?? null
            ]);

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred. Please try again.']
            ], 500);
        }
    }

    /**
     * Show the trial invitation page
     */
    public function showInvite(string $token)
    {
        $trial = Trial::where('invite_token', $token)->first();

        if (!$trial) {
            abort(404, 'Trial invitation not found');
        }

        if (!$trial->isInviteValid()) {
            return view('trial.expired', compact('trial'));
        }

        $sampleEventData = $trial->sample_event_data ?? [];
        
        // Create a mock event object for the invitation template
        $event = (object) [
            'id' => 'trial-' . $trial->id,
            'name' => $sampleEventData['name'] ?? 'Sample ' . ucfirst($trial->eventType) . ' Event',
            'description' => $sampleEventData['description'] ?? 'This is a sample event to demonstrate our guest management system.',
            'start_date' => $sampleEventData['start_date'] ?? now()->addDays(7)->toISOString(),
            'end_date' => $sampleEventData['end_date'] ?? now()->addDays(7)->addHours(3)->toISOString(),
            'venue_name' => $sampleEventData['venue_name'] ?? 'Sample Venue',
            'venue_address' => $sampleEventData['venue_address'] ?? '123 Sample Street, Sample City, SC 12345',
            'location' => $sampleEventData['venue_address'] ?? '123 Sample Street, Sample City, SC 12345',
            'rsvp_enabled' => true,
            'qr_checkin_enabled' => true,
            'user' => (object) [
                'timezone' => 'UTC'
            ]
        ];

        // Create a mock invitation object
        $invitation = (object) [
            'id' => 'trial-invite-' . $trial->id,
            'token' => $token,
            'rsvp_status' => $trial->rsvp_status ?? 'none',
            'rsvp_at' => $trial->rsvp_at,
            'rsvp_note' => $trial->rsvp_note,
            'guest' => (object) [
                'name' => $trial->name,
                'email' => $trial->contact,
                'phone' => null,
                'timezone' => 'UTC'
            ]
        ];

        // Create a mock guest object
        $guest = (object) [
            'name' => $trial->name,
            'email' => $trial->contact,
            'phone' => null,
            'timezone' => 'UTC'
        ];

        // Set up variables for the template
        $inviteUrl = route('trial.invite', ['token' => $token]);
        $mapLink = 'https://www.google.com/maps?q=' . urlencode($event->venue_address);
        $isPreview = false;
        
        // Get existing reminders for this trial invitation
        $reminders = \App\Shared\Models\Reminder::where('invitation_id', 'trial-invite-' . $trial->id)
            ->where('status', 'pending')
            ->orderBy('scheduled_for')
            ->get();

        return view('trial.invite', compact(
            'event', 
            'invitation', 
            'guest', 
            'inviteUrl', 
            'mapLink', 
            'isPreview', 
            'reminders'
        ));
    }

    /**
     * Schedule a reminder for trial invitation
     */
    public function scheduleReminder(string $token, Request $request)
    {
        $trial = Trial::where('invite_token', $token)->first();

        if (!$trial) {
            return response()->json([
                'success' => false,
                'message' => 'Trial invitation not found'
            ], 404);
        }

        if (!$trial->isInviteValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Trial invitation has expired'
            ], 410);
        }

        $validated = $request->validate([
            'reminder_time' => 'required|date|after:now',
            'platform' => 'required|in:email,whatsapp',
        ]);

        // Check if reminder already exists for this trial and time
        $existingReminder = \App\Shared\Models\Reminder::where('invitation_id', 'trial-invite-' . $trial->id)
            ->where('scheduled_for', $validated['reminder_time'])
            ->where('platform', $validated['platform'])
            ->where('status', 'pending')
            ->first();

        if ($existingReminder) {
            return response()->json([
                'success' => false,
                'message' => 'A reminder for this time and platform already exists.'
            ], 409);
        }

        // Get guest timezone (default to UTC for trials)
        $userTimezone = 'UTC';
        $platformText = $validated['platform'] === 'email' ? 'Email' : 'WhatsApp';

        // Convert to UTC for storage
        $localTime = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s', $validated['reminder_time'], $userTimezone);
        $utcTime = $localTime->utc();

        // Create reminder record in database
        $reminder = \App\Shared\Models\Reminder::create([
            'invitation_id' => 'trial-invite-' . $trial->id,
            'event_id' => 'trial-' . $trial->id,
            'guest_id' => null, // No real guest for trials
            'scheduled_for' => $utcTime,
            'platform' => $validated['platform'],
            'status' => 'pending',
            'event_name' => $trial->sample_event_data['name'] ?? 'Sample Event',
            'event_date' => $trial->sample_event_data['start_date'] ?? now()->addDays(7),
            'guest_name' => $trial->name,
            'guest_email' => $trial->contact,
            'guest_phone' => null,
            'guest_timezone' => $userTimezone,
            'message_content' => $trial->invitation_message,
            'subject' => 'Reminder: ' . ($trial->sample_event_data['name'] ?? 'Sample Event'),
            'attempts' => 0,
        ]);

        // Schedule the actual reminder job using UTC time
        try {
            // For trials, we'll just log the reminder instead of actually sending it
            \Illuminate\Support\Facades\Log::info('Trial reminder scheduled', [
                'trial_id' => $trial->id,
                'reminder_id' => $reminder->id,
                'scheduled_for' => $utcTime->toISOString(),
                'platform' => $validated['platform'],
                'guest_name' => $trial->name,
                'guest_contact' => $trial->contact
            ]);

            // Mark as sent immediately for trials (since we're not actually sending)
            $reminder->update(['status' => 'sent', 'sent_at' => now()]);

            $formattedLocalTime = $localTime->format('l, F j, Y \a\t g:i A');

            return response()->json([
                'success' => true,
                'message' => "Reminder scheduled for {$formattedLocalTime} via {$platformText}! (Demo mode - reminder logged)",
                'reminder' => $reminder
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to schedule trial reminder', [
                'trial_id' => $trial->id,
                'error' => $e->getMessage(),
                'reminder_data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule reminder. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a trial reminder
     */
    public function deleteReminder(string $token, $reminderId)
    {
        $trial = Trial::where('invite_token', $token)->first();

        if (!$trial) {
            return response()->json([
                'success' => false,
                'message' => 'Trial invitation not found'
            ], 404);
        }

        // Find the reminder
        $reminder = \App\Shared\Models\Reminder::where('id', $reminderId)
            ->where('invitation_id', 'trial-invite-' . $trial->id)
            ->first();

        if (!$reminder) {
            return response()->json([
                'success' => false,
                'message' => 'Reminder not found'
            ], 404);
        }

        // Check if reminder can be deleted (only pending reminders)
        if ($reminder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reminders can be deleted.'
            ], 400);
        }

        // Delete the reminder
        $reminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully.'
        ]);
    }

    /**
     * Handle RSVP submission for trial invitation
     */
    public function submitRsvp(string $token, Request $request)
    {
        $trial = Trial::where('invite_token', $token)->first();

        if (!$trial) {
            return back()->with('error', 'Trial invitation not found.');
        }

        if (!$trial->isInviteValid()) {
            return back()->with('error', 'Trial invitation has expired.');
        }

        // Validate the request
        $validated = $request->validate([
            'rsvp_status' => 'required|in:yes,no,maybe',
            'rsvp_note' => 'nullable|string|max:500',
        ]);

        // Check if RSVP is enabled for this trial event
        $sampleEventData = $trial->sample_event_data ?? [];
        if (!($sampleEventData['rsvp_enabled'] ?? true)) {
            return back()->with('error', 'RSVP is not enabled for this event.');
        }

        // Update the trial with RSVP information
        $trial->update([
            'rsvp_status' => $validated['rsvp_status'],
            'rsvp_note' => $validated['rsvp_note'] ?? null,
            'rsvp_at' => now(),
        ]);

        // Log the RSVP response for demo purposes
        Log::info('📋 [TRIAL RSVP] Guest responded to trial invitation', [
            'trial_id' => $trial->id,
            'event_type' => $trial->eventType,
            'guest_name' => $trial->name,
            'guest_contact' => $trial->contact,
            'rsvp_status' => $validated['rsvp_status'],
            'rsvp_note' => $validated['rsvp_note'],
            'response_time' => now()->toISOString(),
        ]);

        // Determine success message
        $message = 'Thank you for your RSVP response! (Demo mode)';

        // Add status-specific messages
        switch ($validated['rsvp_status']) {
            case 'yes':
                $message .= ' We look forward to seeing you at the event!';
                break;
            case 'maybe':
                $message .= ' We hope you can make it!';
                break;
            case 'no':
                $message .= ' We\'ll miss you, but thank you for letting us know.';
                break;
        }

        return back()->with('success', $message);
    }
} 