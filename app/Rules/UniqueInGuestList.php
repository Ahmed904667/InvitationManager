<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Shared\Models\Guest;

class UniqueInGuestList implements Rule
{
    protected $guestListId;
    protected $excludeGuestId;
    protected $field;

    public function __construct($guestListId, $field = 'email', $excludeGuestId = null)
    {
        $this->guestListId = $guestListId;
        $this->field = $field;
        $this->excludeGuestId = $excludeGuestId;
    }

    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // Allow empty values
        }

        $query = Guest::where('guest_list_id', $this->guestListId)
                     ->where($this->field, $value);

        if ($this->excludeGuestId) {
            $query->where('id', '!=', $this->excludeGuestId);
        }

        return !$query->exists();
    }

    public function message()
    {
        $fieldName = ucfirst($this->field);
        return "This {$fieldName} is already registered in this guest list.";
    }
} 