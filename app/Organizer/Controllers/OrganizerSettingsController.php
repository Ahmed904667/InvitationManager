<?php

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Organizer\Services\OrganizerNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class OrganizerSettingsController extends Controller
{
    protected $notificationService;

    public function __construct(
        OrganizerNotificationService $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the organizer settings page
     */
    public function index()
    {
        Gate::authorize('organizer-access');

        $user = Auth::user();
        $notificationSettings = $this->notificationService->getNotificationSettings($user);

        return view('organizer.settings.index', compact('notificationSettings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        Gate::authorize('organizer-access');

        $validated = $request->validate([
            'mute_notifications' => 'boolean',
            'preferred_platform' => 'required|in:email,whatsapp'
        ]);

        $user = Auth::user();

        // If WhatsApp is selected, ensure user has a phone number
        if ($validated['preferred_platform'] === 'whatsapp' && empty($user->phone)) {
            return back()->withErrors(['preferred_platform' => 'You must have a phone number to use WhatsApp notifications.']);
        }

        $success = $this->notificationService->updateNotificationSettings($user, $validated);

        if ($success) {
            return back()->with('success', 'Notification settings updated successfully!');
        }

        return back()->withErrors(['general' => 'Failed to update notification settings.']);
    }

}
