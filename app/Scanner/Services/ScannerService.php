<?php

namespace App\Scanner\Services;

use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScannerService
{
    public function getDashboardStats(): array
    {
        $user = Auth::user();
        
        return [
            'available_events' => $this->getAvailableEvents()->count(),
            'today_checkins' => $this->getTodayCheckIns($user),
            'total_checkins' => $this->getTotalCheckIns($user),
            'recent_activity' => $this->getRecentActivity($user),
        ];
    }

    public function getAvailableEvents()
    {
        return GuestList::where('event_date', '>=', Carbon::today())
            ->orWhereNull('event_date')
            ->get();
    }

    public function getEventStats(GuestList $guestList): array
    {
        return [
            'total_guests' => $guestList->guests()->count(),
            'checked_in_guests' => $guestList->guests()->where('checked_in', true)->count(),
            'check_in_rate' => $guestList->guests()->count() > 0 
                ? round(($guestList->guests()->where('checked_in', true)->count() / $guestList->guests()->count()) * 100, 2)
                : 0,
            'recent_checkins' => $this->getRecentCheckIns($guestList),
        ];
    }

    public function scanGuest(GuestList $guestList, string $identifier): array
    {
        // Try to find guest by various identifiers
        $guest = $guestList->guests()
            ->where(function($query) use ($identifier) {
                $query->where('id', $identifier)
                      ->orWhere('email', $identifier)
                      ->orWhere('phone', $identifier)
                      ->orWhere('name', 'LIKE', "%{$identifier}%");
            })
            ->first();

        if (!$guest) {
            return [
                'success' => false,
                'message' => 'Guest not found',
                'guest' => null
            ];
        }

        if ($guest->checked_in) {
            return [
                'success' => false,
                'message' => 'Guest already checked in',
                'guest' => $guest->load('group')
            ];
        }

        // Check in the guest
        $guest->checked_in = true;
        $guest->checked_in_at = Carbon::now();
        $guest->checked_in_by = Auth::id();
        $guest->save();

        return [
            'success' => true,
            'message' => 'Guest checked in successfully',
            'guest' => $guest->load('group')
        ];
    }

    public function manualCheckIn(GuestList $guestList, int $guestId, ?string $notes = null): array
    {
        $guest = $guestList->guests()->find($guestId);

        if (!$guest) {
            return [
                'success' => false,
                'message' => 'Guest not found'
            ];
        }

        if ($guest->checked_in) {
            return [
                'success' => false,
                'message' => 'Guest already checked in'
            ];
        }

        // Check in the guest
        $guest->checked_in = true;
        $guest->checked_in_at = Carbon::now();
        $guest->checked_in_by = Auth::id();
        $guest->check_in_notes = $notes;
        $guest->save();

        return [
            'success' => true,
            'message' => 'Guest checked in successfully',
            'guest' => $guest->load('group')
        ];
    }

    public function searchGuests(GuestList $guestList, string $query)
    {
        return $guestList->guests()
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            })
            ->with('group')
            ->limit(10)
            ->get();
    }

    public function getGuestDetails(Guest $guest): array
    {
        return [
            'guest' => $guest->load('group'),
            'check_in_history' => $this->getGuestCheckInHistory($guest),
            'related_guests' => $this->getRelatedGuests($guest),
        ];
    }

    public function getCheckInHistory(GuestList $guestList)
    {
        return $guestList->guests()
            ->where('checked_in', true)
            ->with(['group', 'checkedInBy'])
            ->orderBy('checked_in_at', 'desc')
            ->paginate(20);
    }

    public function exportCheckIns(GuestList $guestList)
    {
        $checkIns = $guestList->guests()
            ->where('checked_in', true)
            ->with(['group', 'checkedInBy'])
            ->orderBy('checked_in_at', 'desc')
            ->get();

        $filename = 'check-ins-' . $guestList->id . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $file = fopen('php://output', 'w');
        
        // Headers
        fputcsv($file, ['Name', 'Email', 'Phone', 'Group', 'Checked In At', 'Checked In By', 'Notes']);
        
        foreach ($checkIns as $guest) {
            fputcsv($file, [
                $guest->name,
                $guest->email,
                $guest->phone,
                $guest->group ? $guest->group->name : '',
                $guest->checked_in_at ? $guest->checked_in_at->format('Y-m-d H:i:s') : '',
                $guest->checkedInBy ? $guest->checkedInBy->name : '',
                $guest->check_in_notes ?? ''
            ]);
        }
        
        fclose($file);
        exit;
    }

    public function bulkCheckIn(GuestList $guestList, array $guestIds): array
    {
        $guests = $guestList->guests()->whereIn('id', $guestIds)->get();
        $checkedIn = 0;
        $alreadyCheckedIn = 0;
        $errors = [];

        foreach ($guests as $guest) {
            if ($guest->checked_in) {
                $alreadyCheckedIn++;
                continue;
            }

            try {
                $guest->checked_in = true;
                $guest->checked_in_at = Carbon::now();
                $guest->checked_in_by = Auth::id();
                $guest->save();
                $checkedIn++;
            } catch (\Exception $e) {
                $errors[] = "Failed to check in {$guest->name}: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'message' => "Bulk check-in completed. Checked in: {$checkedIn}, Already checked in: {$alreadyCheckedIn}",
            'checked_in' => $checkedIn,
            'already_checked_in' => $alreadyCheckedIn,
            'errors' => $errors
        ];
    }

    public function undoCheckIn(Guest $guest): array
    {
        if (!$guest->checked_in) {
            return [
                'success' => false,
                'message' => 'Guest is not checked in'
            ];
        }

        $guest->checked_in = false;
        $guest->checked_in_at = null;
        $guest->checked_in_by = null;
        $guest->check_in_notes = null;
        $guest->save();

        return [
            'success' => true,
            'message' => 'Check-in undone successfully'
        ];
    }

    public function getScannerSettings(): array
    {
        $user = Auth::user();
        
        return [
            'auto_focus' => $user->scanner_settings['auto_focus'] ?? true,
            'sound_enabled' => $user->scanner_settings['sound_enabled'] ?? true,
            'vibration_enabled' => $user->scanner_settings['vibration_enabled'] ?? true,
            'flash_enabled' => $user->scanner_settings['flash_enabled'] ?? false,
            'scan_timeout' => $user->scanner_settings['scan_timeout'] ?? 30,
        ];
    }

    public function updateScannerSettings(array $settings): void
    {
        $user = Auth::user();
        $user->scanner_settings = $settings;
        $user->save();
    }

    public function getOfflineData(): array
    {
        $user = Auth::user();
        
        return [
            'events' => $this->getAvailableEvents(),
            'last_sync' => $user->last_offline_sync ?? null,
            'pending_checkins' => $this->getPendingCheckIns($user),
        ];
    }

    public function syncOfflineData(): array
    {
        $user = Auth::user();
        $pendingCheckIns = $this->getPendingCheckIns($user);
        
        $synced = 0;
        $errors = [];

        foreach ($pendingCheckIns as $checkIn) {
            try {
                // Sync the check-in to the server
                $guest = Guest::find($checkIn['guest_id']);
                if ($guest && !$guest->checked_in) {
                    $guest->checked_in = true;
                    $guest->checked_in_at = Carbon::parse($checkIn['checked_in_at']);
                    $guest->checked_in_by = $user->id;
                    $guest->check_in_notes = $checkIn['notes'] ?? null;
                    $guest->save();
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to sync check-in for guest {$checkIn['guest_id']}: " . $e->getMessage();
            }
        }

        $user->last_offline_sync = Carbon::now();
        $user->save();

        return [
            'success' => true,
            'message' => "Synced {$synced} check-ins",
            'synced' => $synced,
            'errors' => $errors
        ];
    }

    private function getTodayCheckIns(User $user): int
    {
        return DB::table('guests')
            ->where('checked_in_by', $user->id)
            ->whereDate('checked_in_at', Carbon::today())
            ->count();
    }

    private function getTotalCheckIns(User $user): int
    {
        return DB::table('guests')
            ->where('checked_in_by', $user->id)
            ->count();
    }

    private function getRecentActivity(User $user): array
    {
        return DB::table('guests')
            ->where('checked_in_by', $user->id)
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getRecentCheckIns(GuestList $guestList): array
    {
        return $guestList->guests()
            ->where('checked_in', true)
            ->with(['group', 'checkedInBy'])
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getGuestCheckInHistory(Guest $guest): array
    {
        // Implementation for guest check-in history
        return [];
    }

    private function getRelatedGuests(Guest $guest): array
    {
        // Implementation for related guests
        return [];
    }

    private function getPendingCheckIns(User $user): array
    {
        // Implementation for pending offline check-ins
        return [];
    }
} 