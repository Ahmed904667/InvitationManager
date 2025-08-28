# Sent Event Update Feature

## Overview

The Sent Event Update feature allows organizers to manage and update events after invitations have been sent. This feature is specifically designed for events with "sent" status and provides comprehensive guest management capabilities.

## Features

### 1. Basic Information Updates
- Update event name, description, dates, and venue information
- Changes are logged for potential guest notifications
- Maintains event integrity while allowing necessary modifications

### 2. Guest Management
- **Add Guest Lists**: Attach new guest lists to the event
- **Add Individual Guests**: Add single guests to existing guest lists or as standalone guests
- **Remove Guests**: Remove individual guests from the event with notification system
- **Remove Guest Lists**: Remove entire guest lists from the event
- **Message Management**: Individual message creation for new guests only
- **Guest Lifecycle**: New guests appear in special section with message functionality, then move to current guests section
- **Guest Removal System**: 
  - Expires invitation for the specific event only
  - Keeps guest in their original guest list for other events
  - Sends notification messages via email/WhatsApp
  - Customizable removal messages
  - Invitation links show expired page
- Automatic invitation creation for new guests
- Support for standalone guests (not assigned to any guest list)

### 4. Guest Notifications
- Send update notifications to all guests
- Support for multiple platforms (Email, WhatsApp, or both)
- Three notification types:
  - **Event Update**: Notify about changes
  - **Event Reminder**: Send reminders
  - **Custom Message**: Send custom notifications
- Notification history tracking

## Access

### For Sent Events Only
This feature is only available for events with "sent" status. When users try to edit a sent event, they are automatically redirected to the new update interface.

### URL Structure
- Main update page: `/organizer/events/{event}/update-sent`
- Basic info update: `PUT /organizer/events/{event}/update-sent/basic`
- Guest management: Various POST endpoints for adding/removing guests
- Message generation: `POST /organizer/events/{event}/update-sent/generate-messages`
- Notifications: `POST /organizer/events/{event}/update-sent/notify-guests`

## User Interface

### Tabbed Interface
The update page uses a modern tabbed interface with three main sections:

1. **Basic Information**: Update event details
2. **Guest Management**: Add/remove guests and guest lists, generate messages for new guests
3. **Notifications**: Send notifications to guests

### Modern Design
- Responsive design that works on all devices
- Modern card-based layout
- Interactive modals for detailed views
- Real-time feedback and notifications

## Technical Implementation

### Controller Methods
- `updateSentEvent()`: Main view method
- `updateSentEventBasic()`: Update basic information
- `addGuestListToSentEvent()`: Add guest lists
- `addGuestToSentEvent()`: Add individual guests
- `removeGuestFromSentEvent()`: Remove guests
- `removeGuestListFromSentEvent()`: Remove guest lists
- `generateMessagesForNewGuests()`: Generate messages
- `notifyGuestsOfUpdates()`: Send notifications

### Security
- All methods require proper authorization
- Only event owners can update their events
- Validation ensures data integrity
- CSRF protection on all forms

### Database Operations
- Proper relationship management between events and guest lists
- Automatic invitation creation for new guests
- Clean deletion of removed guests and their invitations
- Message storage in JSON format for flexibility

## Usage Examples

### Adding a New Guest List
1. Navigate to the "Guest Management" tab
2. Select a guest list from the dropdown
3. Click "Add Guest List"
4. System automatically creates invitations for all guests

### Generating Messages for New Guests
1. Navigate to the "Messages" tab
2. Select message type (General/Group/Individual)
3. Click "Generate Messages"
4. System creates appropriate messages for guests without messages

### Sending Update Notifications
1. Navigate to the "Notifications" tab
2. Select platform (Email/WhatsApp/Both)
3. Choose notification type
4. Add optional custom message
5. Click "Send Notification"

## Benefits

1. **Flexibility**: Allows organizers to adapt to changing circumstances
2. **Guest Management**: Easy addition and removal of guests
3. **Communication**: Built-in notification system
4. **Message Management**: Automatic message generation for new guests
5. **Audit Trail**: All changes are logged for transparency
6. **User-Friendly**: Modern interface with clear navigation

## Future Enhancements

- Integration with email/WhatsApp services for actual message sending
- Advanced message templates
- Bulk guest operations
- Guest RSVP management
- Event analytics and reporting
- Automated reminder scheduling

## Notes

- This feature is specifically designed for sent events only
- All changes maintain data integrity and relationships
- The system automatically handles invitation creation and cleanup
- Notifications are logged but actual sending requires integration with external services
