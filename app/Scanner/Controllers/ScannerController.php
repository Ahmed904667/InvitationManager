<?php

namespace App\Scanner\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use App\Scanner\Services\ScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ScannerController extends Controller
{
    protected $scannerService;

    public function __construct(ScannerService $scannerService)
    {
        $this->scannerService = $scannerService;
    }

    public function dashboard()
    {
        Gate::authorize('scanner-access');

        $stats = $this->scannerService->getDashboardStats();
        
        return view('scanner.dashboard', compact('stats'));
    }

    public function availableEvents()
    {
        Gate::authorize('view-events');

        $events = $this->scannerService->getAvailableEvents();
        
        return view('scanner.events.index', compact('events'));
    }

    public function scanEvent(GuestList $guestList)
    {
        Gate::authorize('scan-event', $guestList);

        $stats = $this->scannerService->getEventStats($guestList);
        
        return view('scanner.events.scan', compact('guestList', 'stats'));
    }

    public function scanGuest(Request $request, GuestList $guestList)
    {
        Gate::authorize('scan-guest', $guestList);

        $request->validate([
            'identifier' => 'required|string|max:255'
        ]);

        $result = $this->scannerService->scanGuest($guestList, $request->identifier);

        return response()->json($result);
    }

    public function manualCheckIn(Request $request, GuestList $guestList)
    {
        Gate::authorize('manual-checkin', $guestList);

        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $result = $this->scannerService->manualCheckIn($guestList, $request->guest_id, $request->notes);

        return response()->json($result);
    }

    public function searchGuest(Request $request, GuestList $guestList)
    {
        Gate::authorize('search-guest', $guestList);

        $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);

        $guests = $this->scannerService->searchGuests($guestList, $request->query);

        return response()->json([
            'success' => true,
            'guests' => $guests
        ]);
    }

    public function guestDetails(GuestList $guestList, Guest $guest)
    {
        Gate::authorize('view-guest-details', $guestList);

        $details = $this->scannerService->getGuestDetails($guest);
        
        return view('scanner.guests.details', compact('guestList', 'guest', 'details'));
    }

    public function checkInHistory(GuestList $guestList)
    {
        Gate::authorize('view-checkin-history', $guestList);

        $history = $this->scannerService->getCheckInHistory($guestList);
        
        return view('scanner.events.history', compact('guestList', 'history'));
    }

    public function exportCheckIns(GuestList $guestList)
    {
        Gate::authorize('export-checkins', $guestList);

        return $this->scannerService->exportCheckIns($guestList);
    }

    public function bulkCheckIn(Request $request, GuestList $guestList)
    {
        Gate::authorize('bulk-checkin', $guestList);

        $request->validate([
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'exists:guests,id'
        ]);

        $result = $this->scannerService->bulkCheckIn($guestList, $request->guest_ids);

        return response()->json($result);
    }

    public function undoCheckIn(Request $request, GuestList $guestList, Guest $guest)
    {
        Gate::authorize('undo-checkin', $guestList);

        $result = $this->scannerService->undoCheckIn($guest);

        return response()->json($result);
    }

    public function scannerSettings()
    {
        Gate::authorize('scanner-settings');

        $settings = $this->scannerService->getScannerSettings();
        
        return view('scanner.settings', compact('settings'));
    }

    public function updateScannerSettings(Request $request)
    {
        Gate::authorize('scanner-settings');

        $validated = $request->validate([
            'auto_focus' => 'boolean',
            'sound_enabled' => 'boolean',
            'vibration_enabled' => 'boolean',
            'flash_enabled' => 'boolean',
            'scan_timeout' => 'integer|min:1|max:60'
        ]);

        $this->scannerService->updateScannerSettings($validated);

        return redirect()->route('scanner.settings')
            ->with('success', 'Scanner settings updated successfully!');
    }

    public function offlineMode()
    {
        Gate::authorize('offline-mode');

        $offlineData = $this->scannerService->getOfflineData();
        
        return view('scanner.offline', compact('offlineData'));
    }

    public function syncOfflineData()
    {
        Gate::authorize('sync-offline');

        $result = $this->scannerService->syncOfflineData();

        return response()->json($result);
    }
} 