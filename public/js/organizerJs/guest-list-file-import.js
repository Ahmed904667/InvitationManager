// ************* FILE IMPORT (EXCEL/CSV) ********************

// File Import Modal Logic

// Show file import modal
window.showImportModal = function() {
    // Update required columns display
    updateFileImportRequiredColumns();
    
    showModal('importModal');
}

// Update required columns display based on guest list settings
function updateFileImportRequiredColumns() {
    const formatDiv = document.querySelector('#importModal .text-blue-700 p:first-child');
    if (formatDiv) {
        let fields = ['Name'];
        if (window.guestListSettings?.fields?.email) fields.push('Email');
        if (window.guestListSettings?.fields?.phone) fields.push('Phone');
        if (window.guestListSettings?.fields?.language) fields.push('Language');
        if (window.guestListSettings?.fields?.group) {
            fields.push('Group (required - will create groups automatically)');
        }
        formatDiv.textContent = `Your file should include columns: ${fields.join(', ')}`;
    }
}

// Hide file import modal
window.hideImportModal = function() {
    hideModal('importModal');
    document.getElementById('importForm').reset();
}

// File import form submission handler
window.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Group assignment will be handled by the backend based on file column
            // No manual group selection needed since groups come from the file
            
            fetch(`/organizer/guest-lists/${window.guestListId}/import`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const jsonData = JSON.parse(text);
                            if (jsonData.errors) {
                                // Handle validation errors - show specific field errors
                                const errorMessages = Object.entries(jsonData.errors).map(([field, messages]) => {
                                    const fieldName = field.charAt(0).toUpperCase() + field.slice(1);
                                    return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                                }).join('; ');
                                throw new Error(errorMessages);
                            }
                            if (jsonData.message) {
                                throw new Error(jsonData.message);
                            }
                            throw new Error('Import failed. Please check your file format.');
                        } catch (parseError) {
                            // If it's not JSON, it might be HTML or plain text
                            if (text.includes('error') || text.includes('Error')) {
                                throw new Error('Import failed. Please check your file format.');
                            }
                            throw new Error('Import failed. Please check your file format.');
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    hideImportModal();
                    window.GuestManager?.showNotification(data.message || `Successfully imported ${data.imported} guests`, 'success');
                    
                    // Refresh the guest list and validation errors
                    if (window.refreshValidationErrors) {
                        window.refreshValidationErrors();
                    }
                    
                    // Reload the page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    window.GuestManager?.showNotification(data.message || 'Error importing guests', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.GuestManager?.showNotification(error.message || 'Error importing guests', 'error');
            });
        });
    }
});

// ************* END FILE IMPORT ******************** 