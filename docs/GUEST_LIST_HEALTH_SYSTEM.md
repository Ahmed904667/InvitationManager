# Guest List Health System

## Overview

The Guest List Health System provides real-time health scoring for guest lists, helping organizers identify and fix issues with their guest data quality.

## Health Status Calculation

The health status is determined by checking for any data quality issues. A list is considered "Excellent" only if it has guests and no data quality issues. Lists without guests are marked as "Not Valid".

### Validation Criteria

1. **Duplicate Emails**
   - Multiple guests with the same email address
   - Only counts non-empty email addresses

2. **Duplicate Phone Numbers**
   - Multiple guests with the same phone number
   - Only counts non-empty phone numbers

3. **Missing Required Fields**
   - Based on guest list settings (email, phone, group, language)
   - Only applies if the field is marked as required in settings

4. **Invalid Phone Formats**
   - Phone numbers missing country code (+)
   - Only applies to phone numbers with 7+ digits

**Note**: Any single issue makes the list "Not Valid". Lists without guests are also considered "Not Valid".

### Health Status Levels

- **Excellent**: Success badge - "List is in excellent condition!"
- **Not Valid**: Danger/Red badge - "List has issues that need to be addressed."

## Implementation

### Backend

- **Model**: `GuestList::calculateAndStoreHealth()` and `GuestList::getHealth()`
- **Controller**: `GuestListController::getHealth()`
- **Route**: `GET /organizer/guest-lists/{guestList}/health`
- **Database**: `health` JSON column in `guest_lists` table
- **Command**: `php artisan guest-lists:calculate-health`

### Frontend

- **Display**: Health badges appear on the guest lists index page
- **Colors**: Success (Green), Blue, Yellow, Danger (Red) badges with appropriate icons
- **Tooltips**: Hover over badges to see detailed health messages

## API Response Format

```json
{
  "health": {
    "status": "excellent",
    "color": "success",
    "message": "List is in excellent condition!",
    "issues": [],
    "total_guests": 25,
    "total_issues": 0
  },
  "guest_list": {
    "id": 1,
    "name": "My Event Guest List"
  }
}
```

## Usage

### In Views

```php
@if(isset($list->health))
    <span class="badge badge-{{ $list->health['color'] }}" title="{{ $list->health['message'] }}">
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        {{ ucfirst($list->health['status']) }}
    </span>
@endif
```

### In JavaScript

```javascript
fetch(`/organizer/guest-lists/${listId}/health`)
  .then(response => response.json())
  .then(data => {
    console.log('Health Score:', data.health.score);
    console.log('Status:', data.health.status);
    console.log('Issues:', data.health.issues);
  });
```

## Benefits

1. **Data Quality**: Helps identify and fix data quality issues
2. **User Experience**: Visual indicators make it easy to spot problematic lists
3. **Performance**: Health is stored in database, not calculated on every request
4. **Automation**: Health is automatically recalculated when guests are added/updated/deleted
5. **Actionable**: Specific issues are listed for easy resolution
6. **Consistency**: Same health calculation logic used everywhere in the application

## Future Enhancements

- Health trend tracking over time
- Automated health improvement suggestions
- Bulk health fixes for common issues
- Health-based sorting and filtering
- Email notifications for poor health scores
