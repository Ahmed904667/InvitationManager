<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrialController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\HelpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;

// Landing page for non-authenticated users
Route::get('/', [LandingController::class, 'index'])->name('home');

// Legal pages
Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

// Platform statistics routes
Route::get('/api/stats', [LandingController::class, 'getStats'])->name('api.stats');
Route::get('/api/stats/detailed', [LandingController::class, 'getDetailedStats'])->name('api.stats.detailed');
Route::get('/api/stats/period', [LandingController::class, 'getStatsForPeriod'])->name('api.stats.period');


// Trial form submission
Route::post('/trial', [TrialController::class, 'store'])->name('trial.store');

// Trial invitation page
Route::get('/trial/invite/{token}', [TrialController::class, 'showInvite'])->name('trial.invite');

// Trial reminder scheduling route
Route::post('/trial/invite/{token}/reminder', [TrialController::class, 'scheduleReminder'])->name('trial.invite.reminder');

// Delete trial reminder route
Route::delete('/trial/invite/{token}/reminder/{reminderId}', [TrialController::class, 'deleteReminder'])->name('trial.invite.reminder.delete');

// Trial RSVP submission route
Route::post('/trial/invite/{token}/rsvp', [TrialController::class, 'submitRsvp'])->name('trial.invite.rsvp');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin'])->name('google.login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// OTP Registration Routes
Route::post('/register/send-otp', [AuthController::class, 'sendRegistrationOTP'])->name('register.send-otp');
Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOTP'])->name('register.verify-otp');
Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOTP'])->name('register.verify-otp.submit');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Google OAuth Routes
Route::get('/google/redirect', [AuthController::class, 'googleRedirect'])->name('google.redirect');
Route::get('/google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');

Route::get('/clear-google-token', function() {
    session()->forget('google_token');
    return 'Google token cleared!';
});

// Dashboard redirect for authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'organizer' => redirect()->route('organizer.dashboard'),
            'scanner' => redirect()->route('scanner.dashboard'),
            default => redirect()->route('organizer.dashboard')
        };
    })->name('dashboard');

    // Account deletion routes
    Route::get('/account/delete', [AccountDeletionController::class, 'showRequestForm'])->name('account.delete.request');
    Route::post('/account/delete', [AccountDeletionController::class, 'requestDeletion'])->name('account.delete.submit');
    
    // Help & Support
    Route::get('/help', [HelpController::class, 'support'])->name('help.support');
});

// Account deletion confirmation (no auth required)
Route::get('/account/delete/confirm/{token}', [AccountDeletionController::class, 'confirmDeletion'])->name('account.delete.confirm');


// Test theme route
Route::get('/test-theme', function () {
    return view('test-theme');
})->name('test-theme');

// Test camera route
Route::get('/test-camera', function () {
    return view('test-camera');
})->name('test-camera');





// Include role-based route files
require __DIR__.'/admin.php';
require __DIR__.'/organizer.php';
require __DIR__.'/scanner.php';

// Include mobile scanner routes (no auth required, token-based access)
require __DIR__.'/mobile.php';

// Public invitation routes
Route::get('/invite/{token}', function(string $token) {
    // Handle preview mode for organizers
    if (str_starts_with($token, 'preview-')) {
        $invitationId = str_replace('preview-', '', $token);
        $invitation = \App\Shared\Models\Invitation::findOrFail($invitationId);
        
        // For preview mode, we don't check expiration status
        $event = \App\Shared\Models\Event::with('user')->findOrFail($invitation->event_id);
        $guest = \App\Shared\Models\Guest::findOrFail($invitation->guest_id);

        // Get existing reminders for this invitation (for preview mode)
        $reminders = \App\Shared\Models\Reminder::where('invitation_id', $invitation->id)
            ->where('status', 'pending')
            ->orderBy('scheduled_for')
            ->get();

        // Build map link if address exists
        $addressForMap = $event->venue_address ?: ($event->location ?: '');
        $mapLink = $addressForMap ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addressForMap) : null;
        $inviteUrl = route('public.invite.show', ['token' => $token]);
        
        // Add preview mode flag
        $isPreview = true;

        return view('invitations.show', compact('event', 'guest', 'invitation', 'mapLink', 'inviteUrl', 'isPreview', 'reminders'));
    }
    
    // Regular invitation handling
    $invitation = \App\Shared\Models\Invitation::where('token', $token)->first();
    
    if (!$invitation) {
        abort(404, 'Invitation not found');
    }
    
    $event = \App\Shared\Models\Event::with('user')->find($invitation->event_id);
    
    if (!$event) {
        abort(404, 'Event not found');
    }
    
    // Check if invitation is expired
    if ($invitation->status === \App\Shared\Models\Invitation::STATUS_EXPIRED) {
        return view('invitations.expired', compact('invitation'));
    }
    
    // Check if invitation is canceled or event is canceled
    if ($invitation->status === 'canceled' || $event->cancelled_at) {
        return view('invitations.canceled', compact('event', 'invitation'));
    }
    
    // Check if event is completed
    if ($event->status === 'completed' || $event->isCompleted()) {
        return view('invitations.completed', compact('event', 'invitation'));
    }
    
    $guest = \App\Shared\Models\Guest::findOrFail($invitation->guest_id);

    // Get existing reminders for this invitation
    $reminders = \App\Shared\Models\Reminder::where('invitation_id', $invitation->id)
        ->where('status', 'pending')
        ->orderBy('scheduled_for')
        ->get();

    // Build map link if address exists
    $addressForMap = $event->venue_address ?: ($event->location ?: '');
    $mapLink = $addressForMap ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addressForMap) : null;
    $inviteUrl = route('public.invite.show', ['token' => $token]);
    
    // Regular mode (not preview)
    $isPreview = false;

    return view('invitations.show', compact('event', 'guest', 'invitation', 'mapLink', 'inviteUrl', 'isPreview', 'reminders'));
})->name('public.invite.show');

Route::post('/invite/{token}/rsvp', [App\Http\Controllers\RsvpController::class, 'submit'])->name('public.invite.rsvp');

// Optional: ICS download for calendar
Route::get('/invite/{token}/event.ics', function(string $token) {
    $invitation = \App\Shared\Models\Invitation::where('token', $token)->firstOrFail();
    $event = \App\Shared\Models\Event::findOrFail($invitation->event_id);
    
    // Check if event is cancelled or completed
    if ($event->status === 'cancelled' || $event->cancelled_at) {
        abort(410, 'Event has been cancelled');
    }
    
    if ($event->status === 'completed' || $event->isCompleted()) {
        abort(410, 'Event has been completed');
    }
    $start = \Carbon\Carbon::parse($event->start_date)->utc();
    $end = !empty($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->utc() : (clone $start)->addHour();
    $summary = $event->name ?? 'Event';
    $description = ($event->description ?? '') . "\n\n" . route('public.invite.show', ['token' => $token]);
    $location = $event->venue_address ?: ($event->location ?: '');
    $uid = $token . '@' . parse_url(config('app.url'), PHP_URL_HOST);

    $ics = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Invaro//EN\n".
           "BEGIN:VEVENT\n".
           "UID:".$uid."\n".
           "DTSTAMP:".$start->format('Ymd\THis\Z')."\n".
           "DTSTART:".$start->format('Ymd\THis\Z')."\n".
           "DTEND:".$end->format('Ymd\THis\Z')."\n".
           "SUMMARY:".addcslashes($summary, ",;\\")."\n".
           "DESCRIPTION:".addcslashes($description, ",;\\\n")."\n".
           "LOCATION:".addcslashes($location, ",;\\")."\n".
           "END:VEVENT\nEND:VCALENDAR";

    return response($ics, 200, [
        'Content-Type' => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="event.ics"'
    ]);
})->name('public.invite.ics');

// Reminder scheduling route
Route::post('/invite/{token}/reminder', function(string $token, Illuminate\Http\Request $request) {
    $invitation = \App\Shared\Models\Invitation::where('token', $token)->firstOrFail();
    $event = \App\Shared\Models\Event::findOrFail($invitation->event_id);
    $guest = \App\Shared\Models\Guest::findOrFail($invitation->guest_id);
    
    // Check if event is cancelled or completed
    if ($event->status === 'cancelled' || $event->cancelled_at) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot schedule reminders for cancelled events.'
        ], 410);
    }
    
    if ($event->status === 'completed' || $event->isCompleted()) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot schedule reminders for completed events.'
        ], 410);
    }
    
    $validated = $request->validate([
        'reminder_time' => 'required|date|after:now',
        'timezone' => 'required|string|max:50',
        'platform' => 'required|in:email,whatsapp',
        'event_name' => 'required|string',
        'event_date' => 'required|date',
        'guest_name' => 'required|string',
        'guest_email' => 'nullable|email',
        'guest_phone' => 'nullable|string'
    ]);
    
    try {
        // Check if reminder already exists for this invitation and time
        $existingReminder = \App\Shared\Models\Reminder::where('invitation_id', $invitation->id)
            ->where('scheduled_for', $validated['reminder_time'])
            ->where('platform', $validated['platform'])
            ->whereIn('status', ['pending', 'sent'])
            ->first();
            
        if ($existingReminder) {
            return response()->json([
                'success' => false,
                'message' => 'A reminder for this time and platform already exists.'
            ], 400);
        }
        
        // Handle timezone conversion properly
        $userTimezone = $validated['timezone'];
        
        // If guest timezone is UTC, we need to determine the actual timezone from the request
        // For now, we'll assume the frontend sends the time in the user's browser timezone
        if ($userTimezone === 'UTC') {
            // Try to get timezone from request headers or use a default
            $userTimezone = $request->header('X-Timezone') ?? 'Asia/Kuala_Lumpur'; // Default to Malaysia timezone
        }
        
        // The frontend sends the time in the guest's timezone
        // Parse it as if it's in the guest's timezone, then convert to UTC
        $localTime = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s', $validated['reminder_time'], $userTimezone);
        $utcTime = $localTime->copy()->utc();
        
        // Create reminder record in database
        $reminder = \App\Shared\Models\Reminder::create([
            'invitation_id' => $invitation->id,
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'scheduled_for' => $utcTime,
            'platform' => $validated['platform'],
            'status' => 'pending',
            'event_name' => $validated['event_name'],
            'event_date' => $validated['event_date'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'guest_timezone' => $userTimezone,
            'job_id' => null, // Will be set when job is dispatched
            'queue' => config('queue.default')
        ]);
        
        // Schedule the actual reminder job using UTC time
        $delay = $utcTime->diffInSeconds(now());
        
        if ($delay > 0) {
            // Dispatch a job to send the reminder
            $job = \Illuminate\Support\Facades\Queue::later(
                $utcTime,
                new \App\Jobs\SendEventReminder($reminder->id)
            );
            
            // Update reminder with job ID
            $reminder->update(['job_id' => $job]);
        }
        
        // Format time in user's timezone for display (use $localTime which is already in guest's timezone)
        $formattedLocalTime = $localTime->format('l, F j, Y \a\t g:i A');
        $formattedUtcTime = $utcTime->format('l, F j, Y \a\t g:i A');
        $platformText = $validated['platform'] === 'email' ? 'email' : 'WhatsApp';
        
        return response()->json([
            'success' => true,
            'message' => "Reminder scheduled for {$formattedLocalTime} via {$platformText}!"
        ]);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Failed to schedule reminder', [
            'error' => $e->getMessage(),
            'invitation_token' => $token,
            'reminder_data' => $validated
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to schedule reminder. Please try again.'
        ], 500);
    }
})->name('public.invite.reminder');

// Delete reminder route
Route::delete('/invite/{token}/reminder/{reminderId}', function($token, $reminderId) {
    try {
        // Find the invitation
        $invitation = \App\Shared\Models\Invitation::where('token', $token)->firstOrFail();
        
        // Find the reminder
        $reminder = \App\Shared\Models\Reminder::where('id', $reminderId)
            ->where('invitation_id', $invitation->id)
            ->firstOrFail();
        
        // Check if reminder can be deleted (only pending reminders)
        if ($reminder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reminders can be deleted.'
            ], 400);
        }
        
        // Cancel the job if it exists
        if ($reminder->job_id) {
            try {
                \Illuminate\Support\Facades\Queue::delete($reminder->job_id);
            } catch (\Exception $e) {
                // Job might already be processed or not found, continue with deletion
                \Illuminate\Support\Facades\Log::warning('Could not cancel reminder job', [
                    'job_id' => $reminder->job_id,
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Delete the reminder
        $reminder->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully.'
        ]);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Failed to delete reminder', [
            'error' => $e->getMessage(),
            'invitation_token' => $token,
            'reminder_id' => $reminderId
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete reminder. Please try again.'
        ], 500);
    }
})->name('public.invite.reminder.delete');

// Temporary test route for health calculation
Route::get('/test-health/{id}', function($id) {
    $guestList = \App\Shared\Models\GuestList::find($id);
    if (!$guestList) {
        return response()->json(['error' => 'Guest list not found']);
    }
    
    $organizerService = new \App\Organizer\Services\OrganizerService();
    $health = $organizerService->calculateGuestListHealth($guestList);
    
    return response()->json([
        'guest_list' => $guestList->name,
        'health' => $health
    ]);
})->middleware('auth');

// Twilio webhook for WhatsApp delivery status
Route::post('/webhooks/twilio/status', [\App\Http\Controllers\TwilioWebhookController::class, 'handleStatusCallback'])->name('webhooks.twilio.status');

// Test route to simulate Twilio webhook (remove in production)
Route::get('/test/webhook/{notification_id}/{status}', function($notificationId, $status) {
    $notification = \App\Shared\Models\Notification::find($notificationId);
    if (!$notification) {
        return response()->json(['error' => 'Notification not found'], 404);
    }
    
    // Simulate Twilio webhook data
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'MessageSid' => $notification->external_id,
        'MessageStatus' => $status,
        'ErrorCode' => null,
        'ErrorMessage' => null
    ]);
    
    // Call the webhook controller
    $controller = new \App\Http\Controllers\TwilioWebhookController();
    $response = $controller->handleStatusCallback($request);
    
    // Refresh the notification to see the updated status
    $notification->refresh();
    
    return response()->json([
        'success' => true,
        'notification_id' => $notification->id,
        'old_status' => $notification->getOriginal('status'),
        'new_status' => $notification->status,
        'twilio_status' => $status
    ]);
});



