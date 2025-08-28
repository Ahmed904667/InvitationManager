# Event-Guest Relationship System

## Overview

The Event-Guest Relationship System provides a sophisticated way to manage guest participation in events while maintaining the integrity of guest lists. This system allows guests to be part of multiple events through their guest lists while providing granular control over their participation in individual events.

## Architecture

### Core Components

1. **EventGuest Model** - Junction table model managing event-guest relationships
2. **EventGuestService** - Service layer for managing relationships
3. **Event-Guest Junction Table** - Database table storing relationship status

### Database Schema

```sql
event_guest table:
- id (primary key)
- event_id (foreign key to events)
- guest_id (foreign key to guests)
- status (enum: 'active', 'removed', 'expired')
- removed_at (timestamp)
- removal_reason (text)
- removed_by (foreign key to users)
- timestamps
```

## Key Features

### 1. **Guest List Integrity**
- Guests remain in their original guest lists
- Guest lists can be used across multiple events
- No impact on guest list structure when removing from events

### 2. **Event-Specific Management**
- Each event maintains its own guest participation status
- Guests can be active in one event and removed from another
- Granular control over guest participation per event

### 3. **Status Tracking**
- **Active**: Guest is participating in the event
- **Removed**: Guest was removed from the event by organizer
- **Expired**: Guest's invitation has expired

### 4. **Audit Trail**
- Track who removed guests and when
- Record removal reasons
- Maintain historical data

## Usage Examples

### Adding Guests to Events

```php
// Add individual guest
$eventGuestService = app(EventGuestService::class);
$eventGuest = $eventGuestService->addGuestToEvent($event, $guest);

// Add entire guest list
$eventGuestService->addGuestListToEvent($event, $guestList);
```

### Removing Guests from Events

```php
// Remove guest from specific event
$eventGuest = $eventGuestService->removeGuestFromEvent(
    $event, 
    $guest, 
    'Guest requested removal', 
    Auth::id()
);
```

### Querying Guest Status

```php
// Check if guest is active for event
$isActive = $eventGuestService->isGuestActiveForEvent($event, $guest);

// Get all active guests for event
$activeGuests = $eventGuestService->getActiveGuestsForEvent($event);

// Get all events guest is active in
$activeEvents = $eventGuestService->getActiveEventsForGuest($guest);
```

## Benefits

### 1. **Data Integrity**
- Guest lists remain unchanged when removing from events
- No orphaned guest records
- Consistent relationship management

### 2. **Scalability**
- Support for multiple events per guest list
- Efficient querying with proper indexing
- Transaction-safe operations

### 3. **Flexibility**
- Event-specific guest management
- Support for both standalone and guest list guests
- Easy to extend with additional statuses

### 4. **Audit & Compliance**
- Complete audit trail of guest changes
- Track removal reasons and responsible parties
- Historical data preservation

## Migration Strategy

### From Old System
1. Create event_guest records for existing event-guest relationships
2. Mark all existing relationships as 'active'
3. Update queries to use new relationship system
4. Gradually migrate functionality

### Data Consistency
- Ensure all existing event-guest relationships are properly migrated
- Validate data integrity after migration
- Maintain backward compatibility during transition

## Best Practices

### 1. **Always Use Service Layer**
- Use EventGuestService for all operations
- Don't manipulate event_guest table directly
- Ensure consistent business logic

### 2. **Proper Error Handling**
- Check guest existence before operations
- Validate event-guest relationships
- Handle edge cases gracefully

### 3. **Performance Considerations**
- Use proper indexing on frequently queried columns
- Implement caching for active guest lists
- Optimize queries for large guest lists

### 4. **Data Validation**
- Validate guest list membership before adding to events
- Ensure unique event-guest combinations
- Maintain referential integrity

## Integration Points

### 1. **Invitation System**
- Link invitations to event_guest relationships
- Expire invitations when guests are removed
- Maintain invitation status consistency

### 2. **Notification System**
- Send notifications based on event_guest status
- Track notification delivery per event
- Handle guest removal notifications

### 3. **Reporting System**
- Generate reports based on event_guest status
- Track guest participation across events
- Analyze guest list effectiveness

## Future Enhancements

### 1. **Additional Statuses**
- 'pending' - Guest invited but not confirmed
- 'confirmed' - Guest confirmed attendance
- 'declined' - Guest declined invitation

### 2. **Advanced Features**
- Bulk guest operations
- Guest list templates
- Automated guest management rules

### 3. **Analytics**
- Guest participation analytics
- Event success metrics
- Guest list performance tracking

