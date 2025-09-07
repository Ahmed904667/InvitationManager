<?php

namespace App\Organizer\Controllers;

use App\Http\Controllers\Controller;
use App\Organizer\Services\OrganizerNotificationService;
use App\Organizer\Services\OrganizerSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class OrganizerSettingsController extends Controller
{
    protected $notificationService;
    protected $settingsService;

    public function __construct(
        OrganizerNotificationService $notificationService,
        OrganizerSettingsService $settingsService
    ) {
        $this->notificationService = $notificationService;
        $this->settingsService = $settingsService;
    }

    /**
     * Display the organizer settings page
     */
    public function index()
    {
        Gate::authorize('organizer-access');

        $user = Auth::user();
        $notificationSettings = $this->notificationService->getNotificationSettings($user);
        $preferredListSettings = $this->settingsService->getPreferredListSettings($user);

        return view('organizer.settings.index', compact('notificationSettings', 'preferredListSettings'));
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

    /**
     * Update preferred list settings
     */
    public function updatePreferredListSettings(Request $request)
    {
        Gate::authorize('organizer-access');

        $validated = $request->validate([
            'enable_guest_fields' => 'array',
            'enable_guest_fields.*' => 'boolean',
            'defaults' => 'array',
            'defaults.country_code' => 'string|max:10',
            'defaults.language' => 'string|max:5',
            'auto_archive_events' => 'boolean',
            'auto_archive_days' => 'integer|min:1|max:365',
            'max_guests_per_list' => 'integer|min:1|max:10000'
        ]);

        $user = Auth::user();
        $success = $this->settingsService->updatePreferredListSettings($user, $validated);

        if ($success) {
            return back()->with('success', 'Preferred list settings updated successfully!');
        }

        return back()->withErrors(['general' => 'Failed to update preferred list settings.']);
    }
}
