<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Scanner;
use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Shared\Models\GuestGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    /**
     * Access scanner interface via token - redirects directly to scan page
     */
    public function access(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $event = $scanner->event;
        
        if (!$event->canUseScanner()) {
            abort(403, 'Scanner not available for this event');
        }
        
        // Update scanner last used
        $scanner->updateLastUsed();
        
        // Redirect directly to scan page
        return redirect()->route('mobile.scanner.scan', ['token' => $token]);
    }

    /**
     * Scanner profile selection page
     */
    public function profiles(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $event = $scanner->event;
        $scanners = $event->activeScanners()->orderBy('name')->get();
        
        return view('mobile.scanner.profiles', compact('scanner', 'event', 'scanners'));
    }

    /**
     * Create new scanner profile
     */
    public function createProfile(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        
        $newScanner = $scanner->event->createScanner($request->name);
        
        return redirect()->route('mobile.scanner.scan', ['token' => $newScanner->token])
            ->with('success', 'Scanner profile created successfully');
    }

    /**
     * Switch to different scanner profile
     */
    public function switchProfile(Request $request, $token, $newToken)
    {
        $newScanner = Scanner::where('token', $newToken)->where('is_active', true)->first();
        
        if (!$newScanner) {
            abort(404, 'Scanner profile not found');
        }
        
        return redirect()->route('mobile.scanner.scan', ['token' => $newToken]);
    }

    /**
     * Main scanning interface
     */
    public function scan(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $event = $scanner->event;
        
        return view('mobile.scanner.scan', compact('scanner', 'event'));
    }

    /**
     * Guest list interface
     */
    public function guests(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $event = $scanner->event;
        $search = $request->get('search', '');
        
        // Get all guests for this event
        $guestsQuery = Guest::whereHas('guestList', function($query) use ($event) {
            $query->whereHas('events', function($subQuery) use ($event) {
                $subQuery->where('events.id', $event->id);
            });
        });
        
        if ($search) {
            $guestsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $guests = $guestsQuery->with(['guestGroup', 'guestList'])->orderBy('name')->get();
        
        // Group guests by guest group if enabled
        $groupedGuests = [];
        $settings = $event->guestLists->first()->settings ?? [];
        $showGroups = $settings['fields']['group'] ?? false;
        
        if ($showGroups) {
            $groupedGuests = $guests->groupBy(function($guest) {
                return $guest->guestGroup ? $guest->guestGroup->name : 'No Group';
            });
        } else {
            $groupedGuests['All Guests'] = $guests;
        }
        
        return view('mobile.scanner.guests', compact('scanner', 'event', 'groupedGuests', 'search', 'showGroups'));
    }

    /**
     * Process QR code scan
     */
    public function processQR(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $request->validate([
            'qr_data' => 'required|string'
        ]);
        
        $qrData = $request->qr_data;
        
        // Log the QR data for debugging
        \Log::info('QR Scanner - Processing QR data', [
            'scanner_token' => $token,
            'event_id' => $scanner->event->id,
            'qr_data' => $qrData,
            'qr_data_type' => gettype($qrData),
            'qr_data_length' => strlen($qrData),
            'is_url' => filter_var($qrData, FILTER_VALIDATE_URL) !== false
        ]);
        
        // Try to find guest by QR data (could be guest ID, email, or custom QR)
        $guest = $this->findGuestByQRData($qrData, $scanner->event);
        
        if (!$guest) {
            \Log::warning('QR Scanner - Guest not found', [
                'scanner_token' => $token,
                'event_id' => $scanner->event->id,
                'qr_data' => $qrData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Guest not found',
                'debug_info' => [
                    'qr_data' => $qrData,
                    'qr_data_length' => strlen($qrData),
                    'is_url' => filter_var($qrData, FILTER_VALIDATE_URL) !== false,
                    'event_id' => $scanner->event->id
                ]
            ]);
        }
        
        \Log::info('QR Scanner - Guest found', [
            'scanner_token' => $token,
            'event_id' => $scanner->event->id,
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'qr_data' => $qrData
        ]);
        
        // Get event settings for contact display
        $eventSettings = $this->getEventContactSettings($scanner->event);
        
        // Check if guest is checked in for this specific event
        $eventGuest = \App\EventGuest::where('event_id', $scanner->event->id)
            ->where('guest_id', $guest->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->first();
        
        $isCheckedIn = $eventGuest ? $eventGuest->isCheckedIn() : false;
        $checkedInAt = $eventGuest && $eventGuest->checked_in_at ? $eventGuest->checked_in_at->format('Y-m-d H:i:s') : null;
        $scannerName = $eventGuest ? $eventGuest->scanner_name : null;
        
        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $guest->id,
                'name' => $guest->name,
                'group' => $guest->guestGroup ? $guest->guestGroup->name : null,
                'contacts' => $guest->getContactInfo($eventSettings),
                'checked_in' => $isCheckedIn,
                'checked_in_at' => $checkedInAt,
                'scanner_name' => $scannerName
            ]
        ]);
    }

    /**
     * Check in guest
     */
    public function checkIn(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $guest = Guest::findOrFail($request->guest_id);
        
        // Find the event-guest relationship
        $eventGuest = \App\EventGuest::where('event_id', $scanner->event->id)
            ->where('guest_id', $guest->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->first();
        
        if (!$eventGuest) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found in this event'
            ]);
        }
        
        if ($eventGuest->isCheckedIn()) {
            return response()->json([
                'success' => false,
                'message' => 'Guest is already checked in for this event'
            ]);
        }
        
        try {
            $eventGuest->checkIn($scanner, $request->notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Guest checked in successfully',
                'guest' => [
                    'name' => $guest->name,
                    'checked_in_at' => $scanner->toScannerTimezone($eventGuest->checked_in_at)->format('Y-m-d H:i:s'),
                    'scanner_name' => $eventGuest->scanner_name
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check in guest: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Scanner profile/stats page
     */
    public function profile(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            abort(404, 'Scanner not found or inactive');
        }
        
        $stats = [
            'total_checkins' => \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->count(),
            'scanner_name' => $scanner->name,
            'last_used' => $scanner->last_used_at?->diffForHumans(),
            'event_name' => $scanner->event->name
        ];
        
        $recentCheckIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('mobile.scanner.profile', compact('scanner', 'stats', 'recentCheckIns'));
    }

    /**
     * Find guest by QR data
     */
    private function findGuestByQRData(string $qrData, Event $event): ?Guest
    {
        // Try different methods to find the guest
        \Log::info('QR Scanner - Attempting to find guest', [
            'qr_data' => $qrData,
            'event_id' => $event->id,
            'is_url' => filter_var($qrData, FILTER_VALIDATE_URL) !== false
        ]);
        
        // Method 1: Invitation URL (most common for QR codes)
        if (filter_var($qrData, FILTER_VALIDATE_URL)) {
            // Extract token from invitation URL
            // Handles formats like: http://localhost:8000/invite/3676728fb39dd5908a9e
            $urlParts = parse_url($qrData);
            $path = $urlParts['path'] ?? '';
            
            if (preg_match('/\/invite\/([a-zA-Z0-9]+)$/', $path, $matches)) {
                $invitationToken = $matches[1];
                
                \Log::info('QR Scanner - Extracted invitation token from URL', [
                    'url' => $qrData,
                    'path' => $path,
                    'token' => $invitationToken,
                    'event_id' => $event->id
                ]);
                
                // Find guest through invitation token - MUST be for the same event as scanner
                $invitation = \App\Shared\Models\Invitation::where('token', $invitationToken)
                    ->where('event_id', $event->id)
                    ->first();
                    
                if ($invitation) {
                    $guest = $invitation->guest;
                    
                    // Double-check that this guest belongs to the scanner's event
                    $isGuestInEvent = $this->isGuestInEvent($guest, $event);
                    
                    if ($isGuestInEvent) {
                        \Log::info('QR Scanner - Found guest via invitation token for correct event', [
                            'token' => $invitationToken,
                            'guest_id' => $guest->id,
                            'guest_name' => $guest->name,
                            'invitation_event_id' => $invitation->event_id,
                            'scanner_event_id' => $event->id
                        ]);
                        return $guest;
                    } else {
                        \Log::warning('QR Scanner - Guest found but not in scanner event', [
                            'token' => $invitationToken,
                            'guest_id' => $guest->id,
                            'guest_name' => $guest->name,
                            'invitation_event_id' => $invitation->event_id,
                            'scanner_event_id' => $event->id
                        ]);
                    }
                } else {
                    \Log::warning('QR Scanner - No invitation found for token in this event', [
                        'token' => $invitationToken,
                        'scanner_event_id' => $event->id
                    ]);
                }
            } else {
                \Log::warning('QR Scanner - URL did not match invitation pattern', [
                    'url' => $qrData,
                    'path' => $path
                ]);
            }
        }
        
        // Method 2: Direct invitation token - MUST be for the same event as scanner
        if (preg_match('/^[a-zA-Z0-9]{20,}$/', $qrData)) {
            $invitation = \App\Shared\Models\Invitation::where('token', $qrData)
                ->where('event_id', $event->id)
                ->first();
                
            if ($invitation) {
                $guest = $invitation->guest;
                
                // Double-check that this guest belongs to the scanner's event
                $isGuestInEvent = $this->isGuestInEvent($guest, $event);
                
                if ($isGuestInEvent) {
                    return $guest;
                }
            }
        }
        
        // Method 3: Direct guest ID - only if guest belongs to this specific event
        if (is_numeric($qrData)) {
            $guest = Guest::find($qrData);
            if ($guest && $this->isGuestInEvent($guest, $event)) {
                return $guest;
            }
        }
        
        // Method 4: Email - only for guests in this specific event
        $guest = Guest::where('email', $qrData)
            ->whereHas('guestList', function($query) use ($event) {
                $query->whereHas('events', function($subQuery) use ($event) {
                    $subQuery->where('events.id', $event->id);
                });
            })
            ->first();
            
        if ($guest) {
            return $guest;
        }
        
        // Method 5: Phone number - only for guests in this specific event
        $guest = Guest::where('phone', $qrData)
            ->whereHas('guestList', function($query) use ($event) {
                $query->whereHas('events', function($subQuery) use ($event) {
                    $subQuery->where('events.id', $event->id);
                });
            })
            ->first();
            
        if ($guest) {
            return $guest;
        }
        
        // Method 6: Name (exact match) - only for guests in this specific event
        $guest = Guest::where('name', $qrData)
            ->whereHas('guestList', function($query) use ($event) {
                $query->whereHas('events', function($subQuery) use ($event) {
                    $subQuery->where('events.id', $event->id);
                });
            })
            ->first();
            
        return $guest;
    }

    /**
     * Check if guest belongs to the event
     */
    private function isGuestInEvent(Guest $guest, Event $event): bool
    {
        return $guest->guestList->events()->where('events.id', $event->id)->exists();
    }

    /**
     * Get event contact display settings
     */
    private function getEventContactSettings(Event $event): array
    {
        $guestList = $event->guestLists->first();
        $settings = $guestList->settings ?? [];
        
        return [
            'show_email' => $settings['fields']['email'] ?? true,
            'show_phone' => $settings['fields']['phone'] ?? false
        ];
    }

    /**
     * Get scanner stats
     */
    public function getStats(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $event = $scanner->event;
        
        // Get all guests for this event (using event_guest table)
        $totalGuests = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->count();
        
        $checkedInGuests = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->where('checked_in', true)
            ->count();
        
        // Get this scanner's check-ins for this event
        $myScans = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->count();
        
        return response()->json([
            'total_guests' => $totalGuests,
            'checked_in' => $checkedInGuests,
            'my_scans' => $myScans
        ]);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $recentActivity = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($eventGuest) {
                return [
                    'guest_name' => $eventGuest->guest->name,
                    'time' => $eventGuest->checked_in_at->diffForHumans()
                ];
            });
        
        return response()->json([
            'activity' => $recentActivity
        ]);
    }

    /**
     * Get profiles list for switching
     */
    public function getProfiles(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $event = $scanner->event;
        $profiles = $event->activeScanners()->get()->map(function($s) use ($scanner) {
            return [
                'name' => $s->name,
                'token' => $s->token,
                'check_ins' => \App\EventGuest::where('scanned_by_scanner_id', $s->id)
                    ->where('checked_in', true)
                    ->count(),
                'is_current' => $s->id === $scanner->id
            ];
        });
        
        return response()->json([
            'profiles' => $profiles
        ]);
    }

    /**
     * Save scanner settings
     */
    public function saveSettings(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $settings = $scanner->settings ?? [];
        $updateData = [];
        
        // Update scanner name and timezone if provided
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        
        if ($request->has('timezone')) {
            $updateData['timezone'] = $request->timezone;
        }
        
        // Update the specific setting
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['sound_notifications', 'vibration', 'auto_continue'])) {
                $settings[$key] = (bool) $value;
            }
        }
        
        $updateData['settings'] = $settings;
        $scanner->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }

    /**
     * Get more check-ins for pagination
     */
    public function getMoreCheckIns(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $offset = $request->get('offset', 0);
        
        $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->offset($offset)
            ->limit(10)
            ->get()
            ->map(function($eventGuest) {
                return [
                    'name' => $eventGuest->guest->name,
                    'checked_in_at' => $eventGuest->checked_in_at->format('M j, g:i A'),
                    'time_ago' => $eventGuest->checked_in_at->diffForHumans()
                ];
            });
        
        return response()->json([
            'checkins' => $checkIns
        ]);
    }

    /**
     * Export scanner check-in data
     */
    public function exportData(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->get();
        
        $csv = "Guest Name,Email,Phone,Check-in Time,Scanner\n";
        
        foreach ($checkIns as $eventGuest) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $eventGuest->guest->name,
                $eventGuest->guest->email ?? '',
                $eventGuest->guest->phone ?? '',
                $eventGuest->checked_in_at->format('Y-m-d H:i:s'),
                $eventGuest->scanner_name
            );
        }
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="scanner_checkins_' . $scanner->name . '_' . date('Y-m-d') . '.csv"'
        ]);
    }

    /**
     * Get scanner stats for the scan page
     */
    public function getScanStats(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $event = $scanner->event;
        
        // Get total guests in the event (using event_guest table)
        $totalGuests = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->count();
        
        // Get checked in guests for this event
        $checkedInGuests = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->where('checked_in', true)
            ->count();
        
        // Get this scanner's check-ins for this event
        $myScans = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->count();
        
        // Get recent check-ins for this scanner (last 5)
        $recentCheckIns = \App\EventGuest::where('event_id', $event->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($eventGuest) use ($scanner) {
                // Convert UTC time to scanner's timezone for display
                $scannerTime = $scanner->toScannerTimezone($eventGuest->checked_in_at);
                return [
                    'id' => $eventGuest->guest->id,
                    'name' => $eventGuest->guest->name,
                    'time' => $scannerTime->toISOString(),
                    'date' => $scannerTime->format('M j')
                ];
            });
        
        return response()->json([
            'success' => true,
            'stats' => [
                'total_guests' => $totalGuests,
                'checked_in_guests' => $checkedInGuests,
                'my_scans' => $myScans,
                'recent_checkins' => $recentCheckIns
            ]
        ]);
    }

    /**
     * Get performance data for the chart
     */
    public function getPerformanceData(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $event = $scanner->event;
        $today = now()->startOfDay();
        
        // Get check-ins for today by hour (9 AM to 9 PM)
        $hourlyData = [];
        
        for ($hour = 9; $hour <= 21; $hour++) {
            $startTime = $today->copy()->addHours($hour);
            $endTime = $startTime->copy()->addHour();
            
            $checkIns = \App\EventGuest::where('event_id', $event->id)
                ->where('status', \App\EventGuest::STATUS_ACTIVE)
                ->where('checked_in', true)
                ->whereBetween('checked_in_at', [$startTime, $endTime])
                ->count();
            
            $hourlyData[] = $checkIns;
        }
        
        return response()->json([
            'success' => true,
            'data' => $hourlyData,
            'labels' => ['9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM']
        ]);
    }

    /**
     * Get comprehensive analytics for the profile page
     */
    public function getAnalytics(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $today = now()->startOfDay();
        
        // Get total check-ins for this scanner
        $totalCheckins = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->count();
        
        // Get check-ins from last 12 hours based on scanner's timezone
        $twelveHoursAgo = $scanner->getCurrentTime()->subHours(12);
        $recentCheckins = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->where('checked_in_at', '>=', $twelveHoursAgo)
            ->count();
        
        // Find peak hour
        $peakHour = $this->calculatePeakHour($scanner);
        
        // Get hourly data for last 12 hours based on scanner's timezone
        $hourlyData = [];
        $hourlyLabels = [];
        $currentTime = $scanner->getCurrentTime();
        $twelveHoursAgo = $currentTime->copy()->subHours(12);
        
        // Create 12 hourly buckets
        for ($i = 0; $i < 12; $i++) {
            $startTime = $twelveHoursAgo->copy()->addHours($i);
            $endTime = $startTime->copy()->addHour();
            
            $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->whereBetween('checked_in_at', [$startTime, $endTime])
                ->count();
            
            $hourlyData[] = $checkIns;
            
            // Format hour label
            $hour = $startTime->hour;
            if ($hour == 0) {
                $hourlyLabels[] = '12 AM';
            } elseif ($hour == 12) {
                $hourlyLabels[] = '12 PM';
            } elseif ($hour > 12) {
                $hourlyLabels[] = ($hour - 12) . ' PM';
            } else {
                $hourlyLabels[] = $hour . ' AM';
            }
        }
        
        // Get daily data for last 7 days based on scanner's timezone
        $dailyData = [];
        $dailyLabels = [];
        $currentTime = $scanner->getCurrentTime();
        $today = $currentTime->copy()->startOfDay();
        
        for ($day = 6; $day >= 0; $day--) {
            $date = $today->copy()->subDays($day);
            
            $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->whereDate('checked_in_at', $date)
                ->count();
            
            $dailyData[] = $checkIns;
            $dailyLabels[] = $date->format('M j');
        }
        
        return response()->json([
            'success' => true,
            'analytics' => [
                'total_checkins' => $totalCheckins,
                'today_checkins' => $recentCheckins,
                'peak_hour' => $peakHour,
                'hourly_data' => $hourlyData,
                'hourly_labels' => $hourlyLabels,
                'daily_data' => $dailyData,
                'daily_labels' => $dailyLabels
            ]
        ]);
    }

    /**
     * Calculate peak hour for the scanner (last 12 hours based on timezone)
     */
    private function calculatePeakHour($scanner)
    {
        $hourlyCounts = [];
        $currentTime = $scanner->getCurrentTime();
        $twelveHoursAgo = $currentTime->copy()->subHours(12);
        
        // Create 12 hourly buckets
        for ($i = 0; $i < 12; $i++) {
            $startTime = $twelveHoursAgo->copy()->addHours($i);
            $endTime = $startTime->copy()->addHour();
            
            $count = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->whereBetween('checked_in_at', [$startTime, $endTime])
                ->count();
            
            $hourlyCounts[$startTime->hour] = $count;
        }
        
        if (empty($hourlyCounts) || max($hourlyCounts) == 0) {
            return 'No activity';
        }
        
        $peakHour = array_keys($hourlyCounts, max($hourlyCounts))[0];
        
        if ($peakHour == 0) {
            return '12 AM';
        } elseif ($peakHour == 12) {
            return '12 PM';
        } elseif ($peakHour > 12) {
            return ($peakHour - 12) . ' PM';
        } else {
            return $peakHour . ' AM';
        }
    }

    /**
     * Detect and save timezone from client
     */
    public function detectTimezone(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->where('is_active', true)->first();
        
        if (!$scanner) {
            return response()->json(['error' => 'Scanner not found'], 404);
        }
        
        $request->validate([
            'timezone' => 'required|string|max:50'
        ]);
        
        $scanner->update(['timezone' => $request->timezone]);
        
        return response()->json([
            'success' => true,
            'message' => 'Timezone detected and saved',
            'timezone' => $request->timezone
        ]);
    }
}
