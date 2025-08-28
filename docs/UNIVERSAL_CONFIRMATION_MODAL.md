# Universal Confirmation Modal Component

This document explains how to use the enhanced universal confirmation modal component for any type of confirmation across the application.

## Overview

The `x-confirmation-modal` component is now a truly universal confirmation system that can handle:
- ✅ Delete confirmations (danger mode)
- ⚠️ Warning confirmations (warning mode)
- ℹ️ Information confirmations (info mode)
- 🎉 Success confirmations (success mode)
- 🔄 General confirmations (default mode)
- 📝 Form submissions
- 🧭 Navigation confirmations
- 🎨 Custom icons and styling

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | `'confirmationModal'` | Unique ID for the modal |
| `title` | string | `'Confirm Action'` | Modal title |
| `message` | string | `'Are you sure you want to proceed?'` | Main confirmation message |
| `itemName` | string | `''` | Name of the item (displayed in quotes) |
| `warningMessage` | string | `''` | Additional warning text |
| `confirmText` | string | `'Confirm'` | Text for the confirm button |
| `cancelText` | string | `'Cancel'` | Text for the cancel button |
| `confirmClass` | string | `'modal-btn-primary'` | CSS class for the confirm button |
| `danger` | boolean | `false` | Danger mode (red styling) |
| `warning` | boolean | `false` | Warning mode (yellow styling) |
| `info` | boolean | `false` | Info mode (blue styling) |
| `success` | boolean | `false` | Success mode (green styling) |
| `icon` | string | `null` | Custom SVG icon |
| `showWarningBox` | boolean | `false` | Show additional warning box |
| `warningBoxText` | string | `'This action will permanently delete...'` | Text for warning box |

## Usage Examples

### 1. Delete Confirmation (Danger Mode)

```blade
<x-confirmation-modal 
    id="deleteModal"
    title="Delete Item"
    message="You are about to delete"
    confirmText="Delete"
    confirmClass="modal-btn-danger"
    :danger="true"
    :showWarningBox="true"
    warningBoxText="This action will permanently delete the selected item and cannot be undone."
/>
```

### 2. Warning Confirmation

```blade
<x-confirmation-modal 
    id="warningModal"
    title="Proceed with Caution"
    message="This action may have unexpected consequences"
    confirmText="Proceed Anyway"
    confirmClass="modal-btn-warning"
    :warning="true"
    :showWarningBox="true"
    warningBoxText="Please review your settings before proceeding."
/>
```

### 3. Information Confirmation

```blade
<x-confirmation-modal 
    id="infoModal"
    title="Information"
    message="This will update your profile settings"
    confirmText="Update"
    confirmClass="modal-btn-primary"
    :info="true"
    :showWarningBox="true"
    warningBoxText="Your changes will be applied immediately."
/>
```

### 4. Success Confirmation

```blade
<x-confirmation-modal 
    id="successModal"
    title="Ready to Publish"
    message="Your content is ready to be published"
    confirmText="Publish Now"
    confirmClass="modal-btn-success"
    :success="true"
    :showWarningBox="true"
    warningBoxText="Once published, your content will be visible to all users."
/>
```

### 5. Custom Icon

```blade
<x-confirmation-modal 
    id="customModal"
    title="Custom Action"
    message="Perform a custom action"
    confirmText="Execute"
    :icon="'<svg class=\"h-6 w-6 text-purple-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M13 10V3L4 14h7v7l9-11h-7z\"></path></svg>'"
    :danger="true"
/>
```

## JavaScript Functions

### `confirmDelete(itemId, itemName, deleteFunction, modalId)`

For delete operations with item ID and name.

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

### `confirmAction(actionFunction, modalId, data)`

For any custom action with optional data.

```javascript
function confirmArchiveItem(itemId) {
    const archiveFunction = (data) => {
        fetch(`/api/items/${data}/archive`, {
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
    
    confirmAction(archiveFunction, 'archiveModal', itemId);
}
```

### `confirmFormSubmission(formId, modalId)`

For form submission confirmations.

```javascript
function confirmSubmitForm() {
    confirmFormSubmission('myForm', 'submitModal');
}
```

### `confirmNavigation(url, modalId)`

For navigation confirmations.

```javascript
function confirmLeavePage() {
    confirmNavigation('/dashboard', 'leaveModal');
}
```

### `showConfirmationModal(modalId, onConfirm, onCancel)`

Direct modal control.

```javascript
function customConfirmation() {
    const onConfirm = () => {
        // Custom logic here
        console.log('Confirmed!');
    };
    
    const onCancel = () => {
        console.log('Cancelled!');
    };
    
    showConfirmationModal('customModal', onConfirm, onCancel);
}
```

## Complete Examples

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
    :showWarningBox="true"
    warningBoxText="This will delete all guests, check-ins, and any imported data associated with this list."
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

### Archive Guest List

```blade
<x-confirmation-modal 
    id="archiveListModal"
    title="Archive Guest List"
    message="You are about to archive the guest list"
    warningMessage="Archived lists can be restored later from the archive section."
    confirmText="Archive List"
    confirmClass="modal-btn-warning"
    :warning="true"
    :showWarningBox="true"
    warningBoxText="The list will be hidden from the main view but can be restored anytime."
/>

<script>
function confirmArchiveList(listId, listName) {
    const archiveFunction = (id) => {
        fetch(`/organizer/guest-lists/${id}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadGuestLists();
                window.GuestManager.showNotification('Guest list archived successfully', 'success');
            } else {
                window.GuestManager.showNotification(data.message || 'Error archiving guest list', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error archiving guest list', 'error');
        });
    };
    
    confirmAction(archiveFunction, 'archiveListModal', listId);
}
</script>
```

### Publish Event

```blade
<x-confirmation-modal 
    id="publishEventModal"
    title="Publish Event"
    message="You are about to publish your event"
    warningMessage="Once published, your event will be visible to all invited guests."
    confirmText="Publish Event"
    confirmClass="modal-btn-success"
    :success="true"
    :showWarningBox="true"
    warningBoxText="Published events cannot be unpublished. All guests will receive notifications."
/>

<script>
function confirmPublishEvent(eventId) {
    const publishFunction = (id) => {
        fetch(`/organizer/events/${id}/publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.GuestManager.showNotification('Event published successfully!', 'success');
                window.location.href = `/organizer/events/${id}`;
            } else {
                window.GuestManager.showNotification(data.message || 'Error publishing event', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.GuestManager.showNotification('Error publishing event', 'error');
        });
    };
    
    confirmAction(publishFunction, 'publishEventModal', eventId);
}
</script>
```

### Form Submission

```blade
<x-confirmation-modal 
    id="submitFormModal"
    title="Submit Application"
    message="You are about to submit your application"
    warningMessage="After submission, you cannot make changes to your application."
    confirmText="Submit Application"
    confirmClass="modal-btn-primary"
    :info="true"
    :showWarningBox="true"
    warningBoxText="Please review all information before submitting. You will receive a confirmation email."
/>

<form id="applicationForm" method="POST" action="/applications">
    @csrf
    <!-- Form fields here -->
    <button type="button" onclick="confirmSubmitApplication()" class="btn-primary">
        Submit Application
    </button>
</form>

<script>
function confirmSubmitApplication() {
    confirmFormSubmission('applicationForm', 'submitFormModal');
}
</script>
```

### Leave Page Confirmation

```blade
<x-confirmation-modal 
    id="leavePageModal"
    title="Leave Page"
    message="You have unsaved changes"
    warningMessage="If you leave now, your changes will be lost."
    confirmText="Leave Anyway"
    confirmClass="modal-btn-danger"
    :warning="true"
    :showWarningBox="true"
    warningBoxText="All unsaved changes will be discarded permanently."
/>

<script>
function confirmLeavePage(url) {
    confirmNavigation(url, 'leavePageModal');
}

// Usage in beforeunload event
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
```

## CSS Classes

The component supports these button classes:
- `modal-btn-primary`: Blue primary button
- `modal-btn-secondary`: Gray secondary button  
- `modal-btn-danger`: Red danger button
- `modal-btn-warning`: Yellow warning button
- `modal-btn-success`: Green success button
- `modal-btn-info`: Blue info button

## Best Practices

1. **Use appropriate modes** for different types of actions:
   - `danger` for destructive actions (delete, remove)
   - `warning` for potentially risky actions (archive, deactivate)
   - `info` for informational actions (update, change)
   - `success` for positive actions (publish, activate)

2. **Provide clear, specific messages** about what will happen

3. **Use unique IDs** for each modal on the same page

4. **Handle errors gracefully** in your action functions

5. **Provide user feedback** through notifications

6. **Update the UI** after successful actions

7. **Use warning boxes** for important additional information

8. **Customize icons** for specific actions when needed

## Migration from Old Version

If you're updating from the previous version:

1. **Add missing props** if you want warning boxes:
   ```blade
   :showWarningBox="true"
   warningBoxText="Your custom warning message"
   ```

2. **Use new helper functions** for different types of confirmations:
   - `confirmAction()` for custom actions
   - `confirmFormSubmission()` for forms
   - `confirmNavigation()` for navigation

3. **Add appropriate mode flags**:
   - `:danger="true"` for delete actions
   - `:warning="true"` for warnings
   - `:info="true"` for information
   - `:success="true"` for success actions

The component is now truly universal and can handle any type of confirmation you need! 