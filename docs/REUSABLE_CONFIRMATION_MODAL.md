# Reusable Confirmation Modal Component

This document explains how to use the reusable confirmation modal component for delete operations and other confirmations across the application.

## Overview

The `x-confirmation-modal` component provides a consistent way to handle user confirmations, especially for destructive actions like deletions. It includes built-in JavaScript functions for easy integration.

## Basic Usage

### 1. Include the Component

Add the confirmation modal component to your Blade template:

```blade
<x-confirmation-modal 
    id="deleteModal"
    title="Delete Item"
    message="You are about to delete"
    confirmText="Delete"
    confirmClass="modal-btn-danger"
    :danger="true"
/>
```

### 2. Create the Delete Function

Create a JavaScript function that handles the actual deletion:

```javascript
function confirmDeleteItem(itemId, itemName) {
    const deleteFunction = (id) => {
        fetch(`/api/items/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Handle success (reload data, show notification, etc.)
                window.GuestManager.showNotification('Item deleted successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error deleting item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error deleting item', 'error');
        });
    };
    
    confirmDelete(itemId, itemName, deleteFunction, 'deleteModal');
}
```

### 3. Add Delete Button

Add a delete button that calls your confirmation function:

```blade
<button class="btn-danger" onclick="confirmDeleteItem(123, 'Item Name')" title="Delete Item">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
    </svg>
</button>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | `'confirmationModal'` | Unique ID for the modal |
| `title` | string | `'Confirm Action'` | Modal title |
| `message` | string | `'Are you sure you want to proceed?'` | Main confirmation message |
| `itemName` | string | `''` | Name of the item being deleted (will be displayed in quotes) |
| `warningMessage` | string | `'This action cannot be undone.'` | Additional warning text |
| `confirmText` | string | `'Confirm'` | Text for the confirm button |
| `cancelText` | string | `'Cancel'` | Text for the cancel button |
| `confirmClass` | string | `'modal-btn-primary'` | CSS class for the confirm button |
| `danger` | boolean | `false` | Whether this is a dangerous action (shows red styling) |

## Available JavaScript Functions

### `confirmDelete(itemId, itemName, deleteFunction, modalId)`

Helper function specifically for delete confirmations.

**Parameters:**
- `itemId`: ID of the item to delete
- `itemName`: Name of the item (displayed in modal)
- `deleteFunction`: Function that performs the actual deletion
- `modalId`: ID of the modal to use (optional, defaults to 'confirmationModal')

### `showConfirmationModal(modalId, onConfirm, onCancel)`

Show a confirmation modal with custom callbacks.

**Parameters:**
- `modalId`: ID of the modal to show
- `onConfirm`: Function to call when user confirms
- `onCancel`: Function to call when user cancels (optional)

### `hideConfirmationModal(modalId)`

Hide a confirmation modal.

**Parameters:**
- `modalId`: ID of the modal to hide

## Examples

### Delete Guest List

```blade
<x-confirmation-modal 
    id="deleteListModal"
    title="Delete Guest List"
    message="You are about to delete the guest list"
    warningMessage="This action cannot be undone and will permanently remove all guests and data associated with this list."
    confirmText="Delete List"
    confirmClass="modal-btn-danger"
    :danger="true"
/>

<script>
function confirmDeleteList(listId, listName) {
    const deleteFunction = (id) => {
        fetch(`/organizer/guest-lists/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadGuestLists();
                loadStats();
                window.GuestManager.showNotification('Guest list deleted successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error deleting guest list', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error deleting guest list', 'error');
        });
    };
    
    confirmDelete(listId, listName, deleteFunction, 'deleteListModal');
}
</script>
```

### Delete Guest

```blade
<x-confirmation-modal 
    id="deleteGuestModal"
    title="Delete Guest"
    message="You are about to delete the guest"
    warningMessage="This action cannot be undone and will permanently remove this guest from the list."
    confirmText="Delete Guest"
    confirmClass="modal-btn-danger"
    :danger="true"
/>

<script>
function confirmDeleteGuest(guestId, guestName) {
    const deleteFunction = (id) => {
        fetch(`/organizer/guest-lists/${guestListId}/guests/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove guest from DOM or reload list
                window.GuestManager.showNotification('Guest deleted successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error deleting guest', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error deleting guest', 'error');
        });
    };
    
    confirmDelete(guestId, guestName, deleteFunction, 'deleteGuestModal');
}
</script>
```

### Delete Group

```blade
<x-confirmation-modal 
    id="deleteGroupModal"
    title="Delete Group"
    message="You are about to delete the group"
    warningMessage="This action cannot be undone. Guests in this group will be moved to 'No Group'."
    confirmText="Delete Group"
    confirmClass="modal-btn-danger"
    :danger="true"
/>

<script>
function confirmDeleteGroup(groupId, groupName) {
    const deleteFunction = (id) => {
        fetch(`/organizer/guest-lists/${guestListId}/groups/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload groups or remove from DOM
                window.GuestManager.showNotification('Group deleted successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error deleting group', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error deleting group', 'error');
        });
    };
    
    confirmDelete(groupId, groupName, deleteFunction, 'deleteGroupModal');
}
</script>
```

### Non-Dangerous Confirmation

```blade
<x-confirmation-modal 
    id="archiveModal"
    title="Archive Item"
    message="You are about to archive this item"
    warningMessage="Archived items can be restored later."
    confirmText="Archive"
    confirmClass="modal-btn-primary"
    :danger="false"
/>

<script>
function confirmArchiveItem(itemId, itemName) {
    const archiveFunction = (id) => {
        fetch(`/api/items/${id}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.GuestManager.showNotification('Item archived successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error archiving item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error archiving item', 'error');
        });
    };
    
    confirmDelete(itemId, itemName, archiveFunction, 'archiveModal');
}
</script>
```

## Best Practices

1. **Always use unique IDs** for each modal on the same page
2. **Provide clear, specific messages** about what will be deleted
3. **Use appropriate styling** (`:danger="true"` for destructive actions)
4. **Handle errors gracefully** in your delete functions
5. **Provide user feedback** through notifications
6. **Update the UI** after successful deletions (reload data, remove from DOM, etc.)

## CSS Classes

The component uses these CSS classes:
- `modal-btn-primary`: Blue primary button
- `modal-btn-secondary`: Gray secondary button  
- `modal-btn-danger`: Red danger button (for destructive actions)

Make sure these classes are defined in your CSS file. 