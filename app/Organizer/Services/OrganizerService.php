<?php

namespace App\Organizer\Services;

use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use App\Shared\Models\GuestGroup;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class OrganizerService
{
    public function getDashboardStats(): array
    {
        $user = Auth::user();
        
        return [
            'total_guest_lists' => $user->guestLists()->count(),
            'total_guests' => $user->guestLists()->withCount('guests')->get()->sum('guests_count'),
            'recent_guest_lists' => $user->guestLists()->withCount('guests')->latest()->limit(5)->get(),
            'upcoming_events' => $user->guestLists()->withCount('guests')->where('event_date', '>=', Carbon::today())->get(),
            'recent_activity' => $this->getRecentActivity($user),
        ];
    }

    public function getMyGuestLists()
    {
        $user = Auth::user();
        return $user->guestLists()->latest()->paginate(10);
    }

    public function createGuestList(array $data): GuestList
    {
        $user = Auth::user();
        
        $guestList = new GuestList();
        $guestList->user_id = $user->id;
        $guestList->name = $data['name'];
        $guestList->description = $data['description'] ?? null;
        $guestList->event_date = $data['event_date'] ?? null;
        $guestList->max_guests = $data['max_guests'] ?? null;
        $guestList->settings = $data['settings'] ?? $guestList->getDefaultSettings();
        $guestList->save();

        return $guestList;
    }

    public function updateGuestList(GuestList $guestList, array $data): void
    {
        $guestList->update($data);
    }

    public function deleteGuestList(GuestList $guestList): void
    {
        $guestList->delete();
    }

    public function getGuestListGuests(GuestList $guestList)
    {
        return $guestList->guests()->with('group')->latest()->get();
    }

    public function getGuestListStats(GuestList $guestList): array
    {
        return [
            'total_guests' => $guestList->guests()->count(),
            'checked_in_guests' => $guestList->guests()->where('checked_in', true)->count(),
            'groups_count' => $guestList->guestGroups()->count(),
            'recent_additions' => $guestList->guests()->latest()->limit(5)->get(),
        ];
    }

    public function addGuest(GuestList $guestList, array $data): Guest
    {
        $guest = new Guest();
        $guest->guest_list_id = $guestList->id;
        $guest->name = $data['name'];
        $guest->email = $data['email'] ?? null;
        $guest->phone = $data['phone'] ?? null;
        $guest->group_id = $data['group_id'] ?? null;
        $guest->language = $data['language'] ?? null;
        $guest->notes = $data['notes'] ?? null;
        $guest->save();

        return $guest;
    }

    /**
     * Add guest without validation (for imports)
     */
    public function addGuestWithoutValidation(GuestList $guestList, array $data): Guest
    {
        $guest = new Guest();
        $guest->guest_list_id = $guestList->id;
        $guest->name = $data['name'];
        $guest->email = $data['email'] ?? null;
        $guest->phone = $data['phone'] ?? null;
        $guest->group_id = $data['group_id'] ?? null;
        $guest->language = $data['language'] ?? null;
        $guest->notes = $data['notes'] ?? null;
        $guest->save();

        return $guest;
    }

    public function updateGuest(Guest $guest, array $data): void
    {
        $guest->update($data);
    }

    public function deleteGuest(Guest $guest): void
    {
        $guest->delete();
    }

    public function importGuests(GuestList $guestList, $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows

                try {
                    $guest = new Guest();
                    $guest->guest_list_id = $guestList->id;
                    $guest->name = $row[0] ?? '';
                    $guest->email = $row[1] ?? null;
                    $guest->phone = $row[2] ?? null;
                    $guest->notes = $row[3] ?? null;

                    // Handle group if provided
                    if (!empty($row[4])) {
                        $groupName = trim($row[4]);
                        $group = $guestList->guestGroups()->where('name', $groupName)->first();
                        
                        if (!$group) {
                            $group = new GuestGroup();
                            $group->guest_list_id = $guestList->id;
                            $group->name = $groupName;
                            $group->save();
                        }
                        
                        $guest->group_id = $group->id;
                    }

                    $guest->save();
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "Successfully imported {$imported} guests.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return [
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ];
        }
    }

    public function exportGuests(GuestList $guestList)
    {
        $guests = $guestList->guests()->with('group')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Phone');
        $sheet->setCellValue('D1', 'Notes');
        $sheet->setCellValue('E1', 'Group');
        $sheet->setCellValue('F1', 'Checked In');

        $row = 2;
        foreach ($guests as $guest) {
            $sheet->setCellValue('A' . $row, $guest->name);
            $sheet->setCellValue('B' . $row, $guest->email);
            $sheet->setCellValue('C' . $row, $guest->phone);
            $sheet->setCellValue('D' . $row, $guest->notes);
            $sheet->setCellValue('E' . $row, $guest->group ? $guest->group->name : '');
            $sheet->setCellValue('F' . $row, $guest->checked_in ? 'Yes' : 'No');
            $row++;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'guest-list-' . $guestList->id . '-' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function getReports(): array
    {
        $user = Auth::user();
        
        return [
            'guest_list_summary' => $this->getGuestListSummary($user),
            'guest_statistics' => $this->getGuestStatistics($user),
            'recent_activity' => $this->getRecentActivity($user),
        ];
    }

    private function getRecentActivity(User $user): array
    {
        // Implementation for recent activity
        return [];
    }

    private function getGuestListSummary(User $user): array
    {
        return [
            'total_lists' => $user->guestLists()->count(),
            'active_lists' => $user->guestLists()->where('event_date', '>=', Carbon::today())->count(),
            'completed_lists' => $user->guestLists()->where('event_date', '<', Carbon::today())->count(),
        ];
    }

    private function getGuestStatistics(User $user): array
    {
        return [
            'total_guests' => $user->guestLists()->withCount('guests')->get()->sum('guests_count'),
            'checked_in_guests' => $user->guestLists()->guests()->where('checked_in', true)->count(),
            'average_guests_per_list' => $user->guestLists()->withCount('guests')->get()->avg('guests_count'),
        ];
    }

    /**
     * Calculate check-in rate across all guest lists for the current user
     */
    public function getCheckInRate(): float
    {
        $user = Auth::user();
        $totalGuests = $user->guestLists()->withCount('guests')->get()->sum('guests_count');
        $totalCheckedIn = Guest::whereIn('guest_list_id', $user->guestLists()->pluck('id'))->where('checked_in', true)->count();
        return $totalGuests > 0 ? round(($totalCheckedIn / $totalGuests) * 100, 2) : 0;
    }

    /**
     * Get display data for a guest list
     */
    public function getGuestListDisplayData(GuestList $guestList): array
    {
        $guests = $this->getGuestListGuests($guestList);
        $stats = $this->getGuestListStats($guestList);
        $allGuests = $guestList->guests()->with('group')->orderBy('name')->get();
        $guestGroups = $guestList->guestGroups()->orderBy('name')->get();
        
        $lastFiveGuests = $guestList->guests()->latest('id')->take(5)->get()->map(function($guest) {
            return [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
                'phone' => $guest->phone,
                'group_id' => $guest->group_id,
                'group_name' => $guest->guestGroup ? $guest->guestGroup->name : '',
                'language' => $guest->language ?? '',
                'checked_in_at' => $guest->checked_in_at,
            ];
        })->toArray();

        return compact('guestList', 'guests', 'stats', 'lastFiveGuests', 'allGuests', 'guestGroups');
    }

    /**
     * Update guest list settings
     */
    public function updateGuestListSettings(GuestList $guestList, array $data): array
    {
        $fields = [
            'email' => $data['enable_email'] ?? false,
            'phone' => $data['enable_phone'] ?? false,
            'group' => $data['enable_group'] ?? false,
            'language' => $data['enable_language'] ?? false,
        ];
        
        $settings = $guestList->settings ?? $guestList->getDefaultSettings();
        $settings['fields'] = array_merge($settings['fields'] ?? [], $fields);
        $settings['default_country_code'] = $data['default_country_code'] ?? $settings['default_country_code'] ?? '+1';
        $settings['default_language'] = $data['default_language'] ?? $settings['default_language'] ?? 'en';
        
        $guestList->settings = $settings;

        if (isset($data['name'])) {
            $guestList->name = $data['name'];
        }
        if (isset($data['description'])) {
            $guestList->description = $data['description'];
        }
        
        $guestList->save();
        $guestList->refresh();

        return [
            'success' => true,
            'name' => $guestList->name,
            'description' => $guestList->description,
            'settings' => $guestList->settings,
        ];
    }

    /**
     * Get guest groups for a guest list
     */
    public function getGuestGroups(GuestList $guestList)
    {
        return $guestList->guestGroups()->get(['id', 'name', 'description']);
    }

    /**
     * Add a new guest group
     */
    public function addGuestGroup(GuestList $guestList, array $data): GuestGroup
    {
        return $guestList->guestGroups()->create($data);
    }

    /**
     * Update a guest group
     */
    public function updateGuestGroup(GuestGroup $group, array $data): void
    {
        $group->update($data);
    }

    /**
     * Delete a guest group
     */
    public function deleteGuestGroup(GuestGroup $group): void
    {
        // Delete all guests in this group
        $group->guests()->delete();
        
        // Delete the group
        $group->delete();
    }

    /**
     * Bulk change group for selected guests
     */
    public function bulkChangeGuestGroup(GuestList $guestList, array $data): array
    {
        $updated = 0;
        foreach ($data['guest_ids'] as $guestId) {
            $guest = Guest::where('id', $guestId)->where('guest_list_id', $guestList->id)->first();
            if ($guest) {
                $guest->group_id = $data['group_id'];
                $guest->save();
                $updated++;
            }
        }

        return [
            'success' => true,
            'message' => "Changed group for {$updated} guest(s)."
        ];
    }

    /**
     * Bulk delete guests
     */
    public function bulkDeleteGuests(GuestList $guestList, array $guestIds): array
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($guestIds as $guestId) {
            $guest = Guest::find($guestId);
            if ($guest && $guest->guest_list_id == $guestList->id) {
                try {
                    $this->deleteGuest($guest);
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                }
            } else {
                $failCount++;
            }
        }

        $message = "Successfully deleted {$successCount} guest(s).";
        if ($failCount > 0) {
            $message .= " Failed to delete {$failCount} guest(s).";
        }

        return [
            'success' => $failCount === 0,
            'message' => $message,
            'deleted_count' => $successCount
        ];
    }
} 