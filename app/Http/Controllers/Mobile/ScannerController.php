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
        
        // Get all guests for this event with their check-in status from event_guest table
        $guestsQuery = Guest::where(function($query) use ($event) {
            // Guests via guest lists
            $query->whereHas('guestList', function($guestListQuery) use ($event) {
                $guestListQuery->whereHas('events', function($eventQuery) use ($event) {
                    $eventQuery->where('events.id', $event->id);
                });
            })
            // OR guests directly associated with the event (like new guests)
            ->orWhereHas('eventGuests', function($eventGuestQuery) use ($event) {
                $eventGuestQuery->where('event_id', $event->id)
                    ->where('status', \App\EventGuest::STATUS_ACTIVE);
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
        
        // Add check-in status from event_guest table to each guest
        $guests->each(function($guest) use ($event) {
            $eventGuest = \App\EventGuest::where('event_id', $event->id)
                ->where('guest_id', $guest->id)
                ->where('status', \App\EventGuest::STATUS_ACTIVE)
                ->first();
            
            if ($eventGuest) {
                $guest->checked_in = $eventGuest->checked_in;
                $guest->checked_in_at = $eventGuest->checked_in_at;
                $guest->scanner_name = $eventGuest->scanner_name;
            } else {
                $guest->checked_in = false;
                $guest->checked_in_at = null;
                $guest->scanner_name = null;
            }
        });
        
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
            // Check if this is an expired invitation to provide better error message
            $errorMessage = 'Guest not found';
            
            if (filter_var($qrData, FILTER_VALIDATE_URL)) {
                $urlParts = parse_url($qrData);
                $path = $urlParts['path'] ?? '';
                
                if (preg_match('/\/invite\/([a-zA-Z0-9]+)$/', $path, $matches)) {
                    $invitationToken = $matches[1];
                    $expiredInvitation = \App\Shared\Models\Invitation::where('token', $invitationToken)
                        ->where('event_id', $scanner->event->id)
                        ->where('status', 'expired')
                        ->first();
                        
                    if ($expiredInvitation) {
                        $errorMessage = 'This invitation has expired and is no longer valid';
                    }
                }
            }
            
            \Log::warning('QR Scanner - Guest not found', [
                'scanner_token' => $token,
                'event_id' => $scanner->event->id,
                'qr_data' => $qrData,
                'error_message' => $errorMessage
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
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
            'last_used' => $scanner->last_used_at ? $scanner->toScannerTimezone($scanner->last_used_at)->toISOString() : null,
            'event_name' => $scanner->event->name
        ];
        
        $recentCheckIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->with('guest')
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get();
        
        $organizerTimezone = $scanner->event->user->timezone ?? 'UTC';
        
        return view('mobile.scanner.profile', compact('scanner', 'stats', 'recentCheckIns', 'organizerTimezone'));
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
                    ->whereNotIn('status', ['expired']) // Don't allow expired invitations
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
                    // Check if invitation exists but is expired
                    $expiredInvitation = \App\Shared\Models\Invitation::where('token', $invitationToken)
                        ->where('event_id', $event->id)
                        ->first();
                        
                    if ($expiredInvitation && $expiredInvitation->status === 'expired') {
                        \Log::warning('QR Scanner - Invitation found but expired', [
                            'token' => $invitationToken,
                            'scanner_event_id' => $event->id,
                            'invitation_status' => $expiredInvitation->status,
                            'expired_at' => $expiredInvitation->expired_at
                        ]);
                    } else {
                        \Log::warning('QR Scanner - No invitation found for token in this event', [
                            'token' => $invitationToken,
                            'scanner_event_id' => $event->id
                        ]);
                    }
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
                ->whereNotIn('status', ['expired']) // Don't allow expired invitations
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
            ->where(function($query) use ($event) {
                // Guests via guest lists
                $query->whereHas('guestList', function($guestListQuery) use ($event) {
                    $guestListQuery->whereHas('events', function($eventQuery) use ($event) {
                        $eventQuery->where('events.id', $event->id);
                    });
                })
                // OR guests directly associated with the event
                ->orWhereHas('eventGuests', function($eventGuestQuery) use ($event) {
                    $eventGuestQuery->where('event_id', $event->id)
                        ->where('status', \App\EventGuest::STATUS_ACTIVE);
                });
            })
            ->first();
            
        if ($guest) {
            return $guest;
        }
        
        // Method 5: Phone number - only for guests in this specific event
        $guest = Guest::where('phone', $qrData)
            ->where(function($query) use ($event) {
                // Guests via guest lists
                $query->whereHas('guestList', function($guestListQuery) use ($event) {
                    $guestListQuery->whereHas('events', function($eventQuery) use ($event) {
                        $eventQuery->where('events.id', $event->id);
                    });
                })
                // OR guests directly associated with the event
                ->orWhereHas('eventGuests', function($eventGuestQuery) use ($event) {
                    $eventGuestQuery->where('event_id', $event->id)
                        ->where('status', \App\EventGuest::STATUS_ACTIVE);
                });
            })
            ->first();
            
        if ($guest) {
            return $guest;
        }
        
        // Method 6: Name (exact match) - only for guests in this specific event
        $guest = Guest::where('name', $qrData)
            ->where(function($query) use ($event) {
                // Guests via guest lists
                $query->whereHas('guestList', function($guestListQuery) use ($event) {
                    $guestListQuery->whereHas('events', function($eventQuery) use ($event) {
                        $eventQuery->where('events.id', $event->id);
                    });
                })
                // OR guests directly associated with the event
                ->orWhereHas('eventGuests', function($eventGuestQuery) use ($event) {
                    $eventGuestQuery->where('event_id', $event->id)
                        ->where('status', \App\EventGuest::STATUS_ACTIVE);
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
        // Check via guest list (for guests that have a guest list)
        if ($guest->guestList) {
            return $guest->guestList->events()->where('events.id', $event->id)->exists();
        }
        
        // For guests without a guest list (like new guests added directly to events),
        // check via event_guest table
        return \App\EventGuest::where('event_id', $event->id)
            ->where('guest_id', $guest->id)
            ->where('status', \App\EventGuest::STATUS_ACTIVE)
            ->exists();
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
            ->map(function($eventGuest) use ($scanner) {
                // Convert UTC time to scanner's timezone for display
                $scannerTime = $scanner->toScannerTimezone($eventGuest->checked_in_at);
                return [
                    'guest_name' => $eventGuest->guest ? $eventGuest->guest->name : 'Unknown Guest',
                    'time' => $scannerTime->diffForHumans()
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
            ->map(function($eventGuest) use ($scanner) {
                // Convert UTC time to scanner's timezone for display
                $scannerTime = $scanner->toScannerTimezone($eventGuest->checked_in_at);
                return [
                    'name' => $eventGuest->guest ? $eventGuest->guest->name : 'Unknown Guest',
                    'checked_in_at' => $scannerTime->toISOString(),
                    'time_ago' => $scannerTime->diffForHumans()
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
            // Convert UTC time to scanner's timezone for export
            $scannerTime = $scanner->toScannerTimezone($eventGuest->checked_in_at);
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $eventGuest->guest ? $eventGuest->guest->name : 'Unknown Guest',
                $eventGuest->guest ? ($eventGuest->guest->email ?? '') : '',
                $eventGuest->guest ? ($eventGuest->guest->phone ?? '') : '',
                $scannerTime->format('Y-m-d H:i:s'),
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
                    'id' => $eventGuest->guest ? $eventGuest->guest->id : null,
                    'name' => $eventGuest->guest ? $eventGuest->guest->name : 'Unknown Guest',
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
        
        // Get organizer's timezone instead of scanner's timezone
        $organizerTimezone = $scanner->event->user->timezone ?? 'UTC';
        $currentTime = now()->setTimezone($organizerTimezone);
        
        // Get total check-ins for this scanner
        $totalCheckins = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->count();
        
        // Get recent check-ins count (last 24 hours)
        $recentCheckins = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->where('checked_in_at', '>=', now()->subHours(24))
            ->count();
        
        // Find peak hour using organizer's timezone
        $peakHour = $this->calculatePeakHourWithOrganizerTimezone($scanner, $organizerTimezone);
        
        // Debug logging for analytics
        \Log::info('Analytics Debug', [
            'scanner_id' => $scanner->id,
            'organizer_timezone' => $organizerTimezone,
            'total_checkins' => $totalCheckins,
            'recent_checkins' => $recentCheckins,
            'peak_hour' => $peakHour,
            'current_time' => now()->toDateTimeString(),
            'current_time_organizer' => now()->setTimezone($organizerTimezone)->toDateTimeString()
        ]);
        
        return response()->json([
            'success' => true,
            'analytics' => [
                'total_checkins' => $totalCheckins,
                'today_checkins' => $recentCheckins,
                'peak_hour' => $peakHour,
                'organizer_timezone' => $organizerTimezone
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
            
            // Convert scanner timezone times to UTC for database comparison
            $startTimeUTC = $startTime->utc();
            $endTimeUTC = $endTime->utc();
            
            $count = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
                ->where('checked_in', true)
                ->whereBetween('checked_in_at', [$startTimeUTC, $endTimeUTC])
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
     * Calculate peak hour for the scanner using organizer's timezone
     */
    private function calculatePeakHourWithOrganizerTimezone($scanner, $organizerTimezone)
    {
        $hourlyCounts = [];
        
        // Use the same simple 12-hour rolling window as chart data
        $twelveHoursAgo = now()->subHours(12);
        
        // Get check-ins from last 12 hours (same as chart data)
        $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->where('checked_in_at', '>=', $twelveHoursAgo)
            ->orderBy('checked_in_at', 'asc')
            ->get();
        
        // Create 12 hourly buckets (same logic as chart data)
        for ($i = 11; $i >= 0; $i--) {
            $hourStart = now()->subHours($i)->startOfHour();
            $hourEnd = $hourStart->copy()->endOfHour();
            
            // Count check-ins in this hour (same logic as chart data)
            $count = $checkIns->filter(function ($checkIn) use ($hourStart, $hourEnd) {
                return $checkIn->checked_in_at >= $hourStart && $checkIn->checked_in_at <= $hourEnd;
            })->count();
            
            $hourlyCounts[$hourStart->setTimezone($organizerTimezone)->hour] = $count;
        }
        
        if (empty($hourlyCounts) || max($hourlyCounts) == 0) {
            return 'N/A';
        }
        
        $maxCount = max($hourlyCounts);
        $peakHour = array_keys($hourlyCounts, $maxCount)[0];
        
        // Convert the hour to proper 12-hour format in organizer's timezone
        $peakTime = now()->setTimezone($organizerTimezone)->setHour($peakHour)->setMinute(0);
        $formattedTime = $peakTime->format('g A');
        
        // Debug logging for peak hour calculation
        \Log::info('Peak Hour Debug', [
            'scanner_id' => $scanner->id,
            'organizer_timezone' => $organizerTimezone,
            'current_time' => now()->setTimezone($organizerTimezone)->toDateTimeString(),
            'twelve_hours_ago' => $twelveHoursAgo->toDateTimeString(),
            'checkins_count' => $checkIns->count(),
            'hourly_counts' => $hourlyCounts,
            'max_count' => $maxCount,
            'peak_hour' => $peakHour,
            'formatted_time' => $formattedTime,
            'peak_time_utc' => $peakTime->utc()->toDateTimeString(),
            'peak_time_organizer' => $peakTime->toDateTimeString(),
            'checkins_data' => $checkIns->map(function($checkIn) use ($organizerTimezone) {
                return [
                    'id' => $checkIn->id,
                    'checked_in_at' => $checkIn->checked_in_at->toDateTimeString(),
                    'checked_in_at_organizer' => $checkIn->checked_in_at->setTimezone($organizerTimezone)->toDateTimeString()
                ];
            })->toArray()
        ]);
        
        return $formattedTime;
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

    /**
     * Get chart data for check-ins over last 12 hours
     */
    public function getChartData(Request $request, $token)
    {
        $scanner = Scanner::where('token', $token)->firstOrFail();
        $organizerTimezone = $scanner->event->user->timezone ?? 'UTC';
        
        // Get check-ins from last 12 hours
        $twelveHoursAgo = now()->subHours(12);
        
        $checkIns = \App\EventGuest::where('scanned_by_scanner_id', $scanner->id)
            ->where('checked_in', true)
            ->where('checked_in_at', '>=', $twelveHoursAgo)
            ->orderBy('checked_in_at', 'asc')
            ->get();
        
        // Create hourly buckets for the last 12 hours
        $hourlyData = [];
        $labels = [];
        $timestamps = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $hourStart = now()->subHours($i)->startOfHour();
            $hourEnd = $hourStart->copy()->endOfHour();
            
            // Count check-ins in this hour
            $count = $checkIns->filter(function ($checkIn) use ($hourStart, $hourEnd) {
                return $checkIn->checked_in_at >= $hourStart && $checkIn->checked_in_at <= $hourEnd;
            })->count();
            
            $hourlyData[] = $count;
            
            // Create label in scanner timezone
            $label = $hourStart->setTimezone($organizerTimezone)->format('g A');
            $labels[] = $label;
            
            // Store the actual timestamp for JavaScript conversion
            $timestamps[] = $hourStart->toISOString();
        }
        
        // Debug logging for chart data
        \Log::info('Chart Data Debug', [
            'scanner_id' => $scanner->id,
            'organizer_timezone' => $organizerTimezone,
            'twelve_hours_ago' => $twelveHoursAgo->toDateTimeString(),
            'checkins_count' => $checkIns->count(),
            'labels' => $labels,
            'hourly_data' => $hourlyData,
            'current_time' => now()->toDateTimeString()
        ]);
        
        return response()->json([
            'success' => true,
            'chartData' => [
                'labels' => $labels,
                'data' => $hourlyData,
                'timestamps' => $timestamps
            ]
        ]);
    }
}
