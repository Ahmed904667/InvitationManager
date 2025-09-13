<?php

namespace App\Organizer\Services;

use App\Shared\Models\GuestList;
use App\Shared\Models\Guest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EventDuplicateGuestService
{
    /**
     * Find duplicate guests across multiple guest lists for an event
     */
    public function findDuplicateGuests(array $guestListIds, array $excludedGuestIds = []): array
    {
        if (empty($guestListIds)) {
            return [];
        }

        // Get all guests from the selected guest lists
        $query = Guest::whereIn('guest_list_id', $guestListIds)
            ->whereHas('guestList', function($query) {
                $query->where('user_id', Auth::id());
            });

        // Exclude already removed guests if provided
        if (!empty($excludedGuestIds)) {
            $query->whereNotIn('id', $excludedGuestIds);
        }

        $guests = $query->with(['guestList:id,name'])->get();
            
        \Log::info('🔍 [DUPLICATE_SERVICE] Retrieved guests for duplicate check', [
            'user_id' => Auth::id(),
            'guest_list_ids' => $guestListIds,
            'guests_count' => $guests->count(),
            'guests_data' => $guests->map(function($guest) {
                return [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'email' => $guest->email,
                    'phone' => $guest->phone,
                    'guest_list_id' => $guest->guest_list_id,
                    'guest_list_name' => $guest->guestList->name ?? 'Unknown'
                ];
            })->toArray()
        ]);

        $duplicates = [];

        // Find duplicates by email
        $emailDuplicates = $this->findDuplicatesByField($guests, 'email');
        if (!empty($emailDuplicates)) {
            $duplicates['email'] = $emailDuplicates;
        }

        // Find duplicates by phone
        $phoneDuplicates = $this->findDuplicatesByField($guests, 'phone');
        if (!empty($phoneDuplicates)) {
            $duplicates['phone'] = $phoneDuplicates;
        }

        // Note: Name duplicates are not checked - only email and phone duplicates are validated

        return $duplicates;
    }

    /**
     * Find duplicates by a specific field across all guest lists
     */
    private function findDuplicatesByField(Collection $guests, string $field): array
    {
        $duplicates = [];
        
        // Group guests by the field value, excluding null/empty values
        $grouped = $guests->whereNotNull($field)
                         ->where($field, '!=', '')
                         ->groupBy($field)
                         ->filter(function ($group) {
                             return $group->count() > 1;
                         });

        foreach ($grouped as $value => $group) {
            $duplicates[] = [
                'value' => $value,
                'field' => $field,
                'count' => $group->count(),
                'guests' => $group->map(function ($guest) {
                    return [
                        'id' => $guest->id,
                        'name' => $guest->name,
                        'email' => $guest->email,
                        'phone' => $guest->phone,
                        'guest_list_id' => $guest->guest_list_id,
                        'guest_list_name' => $guest->guestList->name ?? 'Unknown List',
                    ];
                })->toArray()
            ];
        }

        return $duplicates;
    }

    /**
     * Remove specific guests from the event (not from guest lists)
     * This creates a list of guest IDs to exclude from the event
     */
    public function createGuestExclusionList(array $guestIdsToRemove): array
    {
        return $guestIdsToRemove;
    }

    /**
     * Get all guests from selected lists excluding the ones to remove
     */
    public function getFilteredGuests(array $guestListIds, array $excludedGuestIds = []): Collection
    {
        $query = Guest::whereIn('guest_list_id', $guestListIds)
            ->whereHas('guestList', function($query) {
                $query->where('user_id', Auth::id());
            });

        if (!empty($excludedGuestIds)) {
            $query->whereNotIn('id', $excludedGuestIds);
        }

        return $query->with(['guestList:id,name'])->get();
    }

    /**
     * Get summary of duplicate guests for display
     */
    public function getDuplicateSummary(array $duplicates): array
    {
        $summary = [
            'total_duplicates' => 0,
            'by_field' => [
                'email' => 0,
                'phone' => 0,
            ],
            'affected_guests' => 0,
        ];

        foreach ($duplicates as $field => $fieldDuplicates) {
            $summary['by_field'][$field] = count($fieldDuplicates);
            $summary['total_duplicates'] += count($fieldDuplicates);
            
            foreach ($fieldDuplicates as $duplicate) {
                $summary['affected_guests'] += $duplicate['count'];
            }
        }

        return $summary;
    }

    /**
     * Check if there are any duplicates
     */
    public function hasDuplicates(array $guestListIds): bool
    {
        $duplicates = $this->findDuplicateGuests($guestListIds);
        return !empty($duplicates);
    }
}
