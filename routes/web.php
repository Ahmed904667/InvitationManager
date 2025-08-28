<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;

// Landing page for non-authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'organizer' => redirect()->route('organizer.dashboard'),
            'scanner' => redirect()->route('scanner.dashboard'),
            default => redirect()->route('organizer.dashboard')
        };
    }
    
    return view('landing');
})->name('home');

// Trial form submission
Route::post('/trial', [TrialController::class, 'store'])->name('trial.store');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin'])->name('google.login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
});



// Test theme route
Route::get('/test-theme', function () {
    return view('test-theme');
})->name('test-theme');



// Include role-based route files
require __DIR__.'/admin.php';
require __DIR__.'/organizer.php';
require __DIR__.'/scanner.php';

// Public invitation routes
Route::get('/invite/{token}', function(string $token) {
    $invitation = \App\Shared\Models\Invitation::where('token', $token)->firstOrFail();
    
    // Check if invitation is expired
    if ($invitation->status === \App\Shared\Models\Invitation::STATUS_EXPIRED) {
        return view('invitations.expired', compact('invitation'));
    }
    
    $event = \App\Shared\Models\Event::findOrFail($invitation->event_id);
    $guest = \App\Shared\Models\Guest::findOrFail($invitation->guest_id);

    // Build map link if address exists
    $addressForMap = $event->venue_address ?: ($event->location ?: '');
    $mapLink = $addressForMap ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addressForMap) : null;
    $inviteUrl = route('public.invite.show', ['token' => $token]);

    return view('invitations.show', compact('event', 'guest', 'invitation', 'mapLink', 'inviteUrl'));
})->name('public.invite.show');

Route::post('/invite/{token}/rsvp', [App\Http\Controllers\RsvpController::class, 'submit'])->name('public.invite.rsvp');

// Optional: ICS download for calendar
Route::get('/invite/{token}/event.ics', function(string $token) {
    $invitation = \App\Shared\Models\Invitation::where('token', $token)->firstOrFail();
    $event = \App\Shared\Models\Event::findOrFail($invitation->event_id);
    $start = \Carbon\Carbon::parse($event->start_date)->utc();
    $end = !empty($event->end_date) ? \Carbon\Carbon::parse($event->end_date)->utc() : (clone $start)->addHour();
    $summary = $event->name ?? 'Event';
    $description = ($event->description ?? '') . "\n\n" . route('public.invite.show', ['token' => $token]);
    $location = $event->venue_address ?: ($event->location ?: '');
    $uid = $token . '@' . parse_url(config('app.url'), PHP_URL_HOST);

    $ics = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Guest Manager//EN\n".
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
        $reminderTime = \Carbon\Carbon::parse($validated['reminder_time']);
        
        // The frontend sends local time, so we need to interpret it in the user's timezone
        // and then convert to UTC for storage
        $localTime = $reminderTime->setTimezone($userTimezone);
        $utcTime = $localTime->utc();
        
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
        
        // Format time in user's timezone for display
        $formattedTime = $utcTime->setTimezone($userTimezone)->format('l, F j, Y \a\t g:i A');
        $platformText = $validated['platform'] === 'email' ? 'email' : 'WhatsApp';
        
        return response()->json([
            'success' => true,
            'message' => "Reminder scheduled for {$formattedTime} via {$platformText}!"
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
