<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'max_guests',
        'settings',
        'health'
    ];

    protected $casts = [
        'settings' => 'array',
        'health' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guestGroups()
    {
        return $this->hasMany(GuestGroup::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_guest_list');
    }



    public function getDefaultSettings()
    {
        return [
            'fields' => [
                'email' => true,
                'phone' => false,
                'group' => false,
                'notes' => false
            ],
            'notifications' => [
                'email_reminders' => false,
                'sms_reminders' => false
            ]
        ];
    }

    /**
     * Calculate and store health information for this guest list
     */
    public function calculateAndStoreHealth(): array
    {
        $totalGuests = $this->guests()->count();
        
        if ($totalGuests === 0) {
            $health = [
                'status' => 'not valid',
                'color' => 'danger',
                'message' => 'No guests yet - add guests to validate list',
                'issues' => ['No guests in list'],
                'total_guests' => 0,
                'total_issues' => 1
            ];
        } else {
            $issues = [];
            $settings = $this->settings ?? [];
            $isValid = true;

            // Check for duplicate emails
            $duplicateEmails = $this->guests()
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->groupBy('email')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            if ($duplicateEmails > 0) {
                $issues[] = "{$duplicateEmails} duplicate email(s) found";
                $isValid = false;
            }

            // Check for duplicate phones
            $duplicatePhones = $this->guests()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->groupBy('phone')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            if ($duplicatePhones > 0) {
                $issues[] = "{$duplicatePhones} duplicate phone number(s) found";
                $isValid = false;
            }

            // Check for missing required fields based on settings
            $requiredFields = [];

            if (isset($settings['fields']['email']) && $settings['fields']['email']) {
                $requiredFields[] = 'email';
            }
            if (isset($settings['fields']['phone']) && $settings['fields']['phone']) {
                $requiredFields[] = 'phone';
            }
            if (isset($settings['fields']['group']) && $settings['fields']['group']) {
                $requiredFields[] = 'group_id';
            }
            if (isset($settings['fields']['language']) && $settings['fields']['language']) {
                $requiredFields[] = 'language';
            }

            foreach ($requiredFields as $field) {
                $missingCount = $this->guests()
                    ->where(function($query) use ($field) {
                        if ($field === 'group_id') {
                            $query->whereNull($field)->orWhere($field, '');
                        } else {
                            $query->whereNull($field)->orWhere($field, '');
                        }
                    })
                    ->count();
                
                if ($missingCount > 0) {
                    $fieldName = $field === 'group_id' ? 'group' : $field;
                    $issues[] = "{$missingCount} guest(s) missing {$fieldName}";
                    $isValid = false;
                }
            }

            // Check for invalid phone formats (missing country code)
            $invalidPhones = $this->guests()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereRaw('phone NOT LIKE "+%"')
                ->whereRaw('LENGTH(REPLACE(phone, " ", "")) >= 7')
                ->count();
            
            if ($invalidPhones > 0) {
                $issues[] = "{$invalidPhones} phone number(s) missing country code";
                $isValid = false;
            }

            // Determine status and color
            if ($isValid) {
                $status = 'excellent';
                $color = 'success';
                $message = 'List is in excellent condition!';
            } else {
                $status = 'not valid';
                $color = 'danger';
                $message = 'List has issues that need to be addressed.';
            }

            $health = [
                'status' => $status,
                'color' => $color,
                'message' => $message,
                'issues' => $issues,
                'total_guests' => $totalGuests,
                'total_issues' => count($issues)
            ];
        }

        // Store the health data
        $this->health = $health;
        $this->save();

        return $health;
    }

    /**
     * Get health information (calculate if not stored)
     */
    public function getHealth(): array
    {
        if (!$this->health) {
            return $this->calculateAndStoreHealth();
        }
        return $this->health;
    }

}
