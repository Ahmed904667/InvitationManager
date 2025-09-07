<?php

namespace App\Organizer\Services;

use App\Shared\Models\User;
use Illuminate\Support\Facades\Log;

class OrganizerSettingsService
{
    /**
     * Get organizer's preferred list settings
     */
    public function getPreferredListSettings(User $organizer): array
    {
        $settings = $organizer->organizer_settings ?? [];
        
        // Ensure all required keys exist with default values
        $enableGuestFields = $settings['enable_guest_fields'] ?? [];
        
        return [
            'enable_guest_fields' => [
                'email' => $enableGuestFields['email'] ?? true,
                'phone' => $enableGuestFields['phone'] ?? false,
                'group' => $enableGuestFields['group'] ?? false,
                'preferred_language' => $enableGuestFields['preferred_language'] ?? false
            ],
            'defaults' => [
                'country_code' => $settings['defaults']['country_code'] ?? '+1',
                'language' => $settings['defaults']['language'] ?? 'en'
            ],
            'auto_archive_events' => $settings['auto_archive_events'] ?? false,
            'auto_archive_days' => $settings['auto_archive_days'] ?? 30,
            'max_guests_per_list' => $settings['max_guests_per_list'] ?? 1000
        ];
    }

    /**
     * Update organizer's preferred list settings
     */
    public function updatePreferredListSettings(User $organizer, array $settings): bool
    {
        try {
            $currentSettings = $organizer->organizer_settings ?? [];
            $updatedSettings = array_merge($currentSettings, $settings);
            
            $organizer->organizer_settings = $updatedSettings;
            $organizer->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update preferred list settings', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get all organizer settings
     */
    public function getAllSettings(User $organizer): array
    {
        return [
            'preferred_list' => $this->getPreferredListSettings($organizer),
            'notifications' => $this->getNotificationSettings($organizer)
        ];
    }

    /**
     * Get notification settings (delegates to OrganizerNotificationService)
     */
    private function getNotificationSettings(User $organizer): array
    {
        // This would typically be injected, but for now we'll create a new instance
        $notificationService = app(OrganizerNotificationService::class);
        return $notificationService->getNotificationSettings($organizer);
    }

    /**
     * Reset organizer settings to defaults
     */
    public function resetToDefaults(User $organizer): bool
    {
        try {
            $organizer->organizer_settings = null;
            $organizer->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reset organizer settings', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
