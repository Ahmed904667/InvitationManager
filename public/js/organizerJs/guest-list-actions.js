// guest-list-actions.js

// Add Group via AJAX
window.addGroup = function(formData) {
    const url = '/organizer/guest-lists/' + window.guestListId + '/groups';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                if (text.includes('<form') && text.toLowerCase().includes('login')) {
                    throw new Error('Please log in to continue.');
                }
                try {
                    return JSON.parse(text);
                } catch {
                    throw new Error('Server error');
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.group) {
            if (typeof loadGroups === 'function') loadGroups();
            window.GuestManager?.showNotification('Group added successfully', 'success');
            return data.group;
        } else {
            window.GuestManager?.showNotification(data.message || 'Error adding group', 'error');
            throw new Error(data.message || 'Error adding group');
        }
    })
    .catch(error => {
        if (error.message && error.message.includes('log in')) {
            window.GuestManager?.showNotification(error.message, 'error');
        } else {
            window.GuestManager?.showNotification(error.message || 'Error adding group', 'error');
        }
        throw error;
    });
};

// Delete Group via AJAX
window.deleteGroup = function(groupId) {
    // Show confirmation modal instead of browser confirm
    return new Promise((resolve, reject) => {
        const modalId = 'confirmationModal';
        const modalTitle = document.querySelector(`#${modalId} .modal-title`);
        const modalMessage = document.querySelector(`#${modalId} .modal-message`);
        const confirmBtn = document.querySelector(`#${modalId} .modal-btn-primary`);
        
        if (modalTitle) modalTitle.textContent = 'Delete Group';
        if (modalMessage) modalMessage.textContent = 'Are you sure you want to delete this group?';
        if (confirmBtn) confirmBtn.textContent = 'Delete Group';
        
        const onConfirm = () => {
            const url = '/organizer/guest-lists/' + window.guestListId + '/groups/' + groupId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof loadGroups === 'function') loadGroups();
                    window.GuestManager?.showNotification('Group deleted successfully', 'success');
                    resolve(data);
                } else {
                    window.GuestManager?.showNotification(data.message || 'Error deleting group', 'error');
                    reject(new Error(data.message || 'Error deleting group'));
                }
            })
            .catch(error => {
                window.GuestManager?.showNotification(error.message || 'Error deleting group', 'error');
                reject(error);
            });
        };
        
        const onCancel = () => {
            reject('Cancelled');
        };
        
        // Show the confirmation modal
        if (typeof showConfirmationModal === 'function') {
            showConfirmationModal(modalId, onConfirm, onCancel);
        } else {
            // Fallback to browser confirm if modal function not available
            if (confirm('Are you sure you want to delete this group?')) {
                onConfirm();
            } else {
                reject('Cancelled');
            }
        }
    });
};

// Add Guest via AJAX
window.addGuest = function(formData) {
    const url = '/organizer/guest-lists/' + window.guestListId + '/guests';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    return fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                
                if (text.includes('<form') && text.toLowerCase().includes('login')) {
                    throw new Error('Please log in to continue.');
                }
                
                // Try to parse as JSON first (for validation errors)
                try {
                    const jsonData = JSON.parse(text);
                    
                    // Handle different possible error response formats
                    if (jsonData.errors) {
                        // Handle validation errors - show specific field errors
                        const errorMessages = Object.entries(jsonData.errors).map(([field, messages]) => {
                            const fieldName = field.charAt(0).toUpperCase() + field.slice(1);
                            return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                        }).join('; ');
                        return Promise.reject(new Error(errorMessages));
                    }
                    
                    if (jsonData.message) {
                        return Promise.reject(new Error(jsonData.message));
                    }
                    
                    // Check for other possible error formats
                    if (jsonData.error) {
                        return Promise.reject(new Error(jsonData.error));
                    }
                    
                    // If we have JSON but no errors or message, it might be a different type of error
                    return Promise.reject(new Error('Request failed. Please try again.'));
                } catch (parseError) {
                    
                    // If it's not JSON, it's probably an HTML error page
                    // Try to extract any meaningful error from HTML
                    if (text.includes('error') || text.includes('Error')) {
                        // Try to find error messages in HTML
                        const errorMatch = text.match(/<[^>]*error[^>]*>([^<]*)<\/[^>]*>/i);
                        if (errorMatch) {
                            return Promise.reject(new Error(errorMatch[1].trim()));
                        }
                    }
                    
                    return Promise.reject(new Error('Server error occurred. Please try again.'));
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.guest) {
            if (window.guestListGuests) window.guestListGuests.push(data.guest);
            if (typeof renderGuestTable === 'function' && window.guestListSettings && window.guestListGuests) {
                renderGuestTable(window.guestListSettings, window.guestListGuests);
            }
            window.GuestManager?.showNotification('Guest added successfully', 'success');
            
            // Refresh validation errors after adding
            refreshValidationErrors();
            return data.guest;
        } else {
            window.GuestManager?.showNotification(data.message || 'Error adding guest', 'error');
            throw new Error(data.message || 'Error adding guest');
        }
    })
    .catch(error => {
        if (error.message && error.message.includes('log in')) {
            window.GuestManager?.showNotification(error.message, 'error');
        } else {
            window.GuestManager?.showNotification(error.message || 'Error adding guest', 'error');
        }
        throw error;
    });
};



// Delete Guest via AJAX
window.deleteGuest = function(guestId) {
    // Validate inputs
    if (!guestId || !window.guestListId) {
        window.GuestManager?.showNotification('Invalid guest or guest list ID', 'error');
        return Promise.reject('Invalid guest or guest list ID');
    }
    
    // Get guest name for confirmation
    const guest = window.guestListGuests?.find(g => g.id == guestId);
    const guestName = guest ? guest.name : 'this guest';
    
    // Show confirmation modal instead of browser confirm
    return new Promise((resolve, reject) => {
        const modalId = 'confirmationModal';
        const modalTitle = document.querySelector(`#${modalId} .modal-title`);
        const modalMessage = document.querySelector(`#${modalId} .modal-message`);
        const confirmBtn = document.querySelector(`#${modalId} .modal-btn-primary`);
        
        if (modalTitle) modalTitle.textContent = 'Delete Guest';
        if (modalMessage) modalMessage.textContent = `Are you sure you want to delete "${guestName}"? This action cannot be undone.`;
        if (confirmBtn) confirmBtn.textContent = 'Delete Guest';
        
        const onConfirm = () => {
            // Continue with deletion logic
            const url = '/organizer/guest-lists/' + window.guestListId + '/guests/' + guestId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            if (!csrfToken) {
                window.GuestManager?.showNotification('CSRF token not found', 'error');
                reject('CSRF token not found');
                return;
            }
            
            // Show loading state
            const deleteBtn = document.querySelector(`button[onclick="deleteGuest(${guestId})"]`);
            let originalText = '';
            if (deleteBtn) {
                originalText = deleteBtn.textContent;
                deleteBtn.textContent = 'Deleting...';
                deleteBtn.disabled = true;
            }
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const jsonData = JSON.parse(text);
                            throw new Error(jsonData.message || 'Server error');
                        } catch (e) {
                            throw new Error('Failed to delete guest');
                        }
                    });
                }
                
                // Check if response is empty
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Handle empty response or non-JSON response
                    return { success: true };
                }
            })
            .then(data => {
                if (data.success) {
                    // Remove guest from local list if present
                    if (window.guestListGuests) {
                        window.guestListGuests = window.guestListGuests.filter(g => g.id != guestId);
                    }
                    
                    // Re-render the table
                    if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                        try {
                            window.renderGuestTable(window.guestListSettings, window.guestListGuests);
                        } catch (error) {
                            // Fallback: reload the page if table rendering fails
                            window.location.reload();
                        }
                    } else {
                        // Fallback: reload the page if we can't re-render
                        window.location.reload();
                    }
                    
                    window.GuestManager?.showNotification('Guest deleted successfully', 'success');
                    
                    // Refresh validation errors after deletion
                    refreshValidationErrors();
                    resolve(data);
                } else {
                    window.GuestManager?.showNotification(data.message || 'Error deleting guest', 'error');
                    reject(new Error(data.message || 'Error deleting guest'));
                }
            })
            .catch(error => {
                window.GuestManager?.showNotification(error.message || 'Error deleting guest', 'error');
                reject(error);
            })
            .finally(() => {
                // Restore button state
                if (deleteBtn && originalText) {
                    deleteBtn.textContent = originalText;
                    deleteBtn.disabled = false;
                }
            });
        };
        
        const onCancel = () => {
            resolve(); // Resolve without doing anything
        };
        
        // Show the confirmation modal
        if (typeof showConfirmationModal === 'function') {
            showConfirmationModal(modalId, onConfirm, onCancel);
        } else {
            // Fallback to browser confirm if modal function not available
            if (confirm(`Are you sure you want to delete "${guestName}"? This action cannot be undone.`)) {
                onConfirm();
            } else {
                resolve();
            }
        }
    });
};

window.loadGroups = function(callback) {
    fetch('/organizer/guest-lists/' + window.guestListId + '/groups')
        .then(response => response.json())
        .then(data => {
            window.guestListGroups = data;
            updateGroupsCount(data.length);
            // Update selects
            const select = document.querySelector('select[name="group_id"]');
            const filterSelect = document.getElementById('groupFilter');
            if (select) {
                select.innerHTML = '<option value="">No group</option>';
                data.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    select.appendChild(option.cloneNode(true));
                });
            }
            if (filterSelect) {
                filterSelect.innerHTML = '<option value="">All Groups</option>';
                data.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    filterSelect.appendChild(option);
                });
            }
            // Re-render guest table with latest groups
            if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                window.renderGuestTable(window.guestListSettings, window.guestListGuests);
            }
            if (typeof callback === 'function') callback(data);
        })
        .catch(error => {
            console.error('Error loading groups:', error);
        });
};

// ************* BULK ACTIONS ********************


window.deleteSelectedGuests = async function() {
    const selected = Array.from(document.querySelectorAll('.guest-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        window.GuestManager?.showNotification('No guests selected for deletion.', 'warning');
        return;
    }
    
    if (!window.guestListId || isNaN(Number(window.guestListId)) || Number(window.guestListId) <= 0) {
        window.GuestManager?.showNotification('Guest list ID is invalid or not found.', 'error');
        return;
    }

    // Get guest names for confirmation
    const selectedGuests = window.guestListGuests?.filter(g => selected.includes(g.id.toString())) || [];
    const guestNames = selectedGuests.map(g => g.name).join(', ');
    
    // Show confirmation modal instead of browser confirm
    const modalId = 'confirmationModal';
    const modalTitle = document.querySelector(`#${modalId} .modal-title`);
    const modalMessage = document.querySelector(`#${modalId} .modal-message`);
    const confirmBtn = document.querySelector(`#${modalId} .modal-btn-primary`);
    
    if (modalTitle) modalTitle.textContent = 'Delete Multiple Guests';
    if (modalMessage) modalMessage.textContent = `Are you sure you want to delete ${selected.length} guest(s): "${guestNames}"? This action cannot be undone.`;
    if (confirmBtn) confirmBtn.textContent = 'Delete Guests';
    
    return new Promise((resolve, reject) => {
        const onConfirm = async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = `/organizer/guest-lists/${window.guestListId}/guests`;

            // Show loading state
            const deleteBtn = document.querySelector('button[onclick="deleteSelectedGuests()"]');
            if (deleteBtn) {
                const originalText = deleteBtn.textContent;
                deleteBtn.textContent = 'Deleting...';
                deleteBtn.disabled = true;
            }

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ guest_ids: selected })
                });

                if (!response.ok) {
                    const text = await response.text();
                    try {
                        const jsonData = JSON.parse(text);
                        throw new Error(jsonData.message || 'Server error');
                    } catch (e) {
                        throw new Error('Failed to delete guests');
                    }
                }

                const data = await response.json();
                if (data.success) {
                    // Remove deleted guests from the local array
                    window.guestListGuests = window.guestListGuests.filter(g => !selected.includes(g.id.toString()));
                    
                    // Re-render the table
                    if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                        window.renderGuestTable(window.guestListSettings, window.guestListGuests);
                    }
                    
                    // Uncheck all checkboxes
                    document.querySelectorAll('.guest-checkbox:checked').forEach(cb => cb.checked = false);
                    
                    window.GuestManager?.showNotification(data.message, 'success');
                    
                    // Refresh validation errors after deletion
                    refreshValidationErrors();
                    resolve(data);
                } else {
                    window.GuestManager?.showNotification(data.message || 'Error deleting guests', 'error');
                    reject(new Error(data.message || 'Error deleting guests'));
                }
            } catch (error) {
                window.GuestManager?.showNotification(error.message || 'Error deleting guests', 'error');
                reject(error);
            } finally {
                // Restore button state
                if (deleteBtn) {
                    deleteBtn.textContent = 'Delete Selected';
                    deleteBtn.disabled = false;
                }
            }
        };
        
        const onCancel = () => {
            resolve(); // Resolve without doing anything
        };
        
        // Show the confirmation modal
        if (typeof showConfirmationModal === 'function') {
            showConfirmationModal(modalId, onConfirm, onCancel);
        } else {
            // Fallback to browser confirm if modal function not available
            if (confirm(`Are you sure you want to delete ${selected.length} guest(s): "${guestNames}"? This action cannot be undone.`)) {
                onConfirm();
            } else {
                resolve();
            }
        }
    });
};

window.changeGroupForSelected = async function() {
    const selected = Array.from(document.querySelectorAll('.guest-checkbox:checked')).map(cb => cb.value);
    const groupId = document.getElementById('changeGroupSelect').value;
    if (selected.length === 0 || !groupId) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const url = `/organizer/guest-lists/${window.guestListId}/change-group`;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ guest_ids: selected, group_id: groupId })
        });

        const data = await response.json();
        if (data.success) {
            // Update local guest list
            window.guestListGuests.forEach(g => {
                if (selected.includes(g.id.toString())) {
                    g.group_id = groupId;
                }
            });
            window.renderGuestTable(window.guestListSettings, window.guestListGuests);
            window.GuestManager?.showNotification(data.message || 'Group changed successfully!', 'success');
        } else {
            window.GuestManager?.showNotification(data.message || 'Failed to change group.', 'error');
        }
    } catch (e) {
        console.error('Error changing group:', e);
        window.GuestManager?.showNotification('Error changing group.', 'error');
    }
};




// ************* END BULK ACTIONS ********************

// ************* EDIT GUEST FUNCTIONALITY ********************

// Edit Guest via AJAX
window.editGuest = function(guestId) {
    // Find the guest data
    const guest = window.guestListGuests.find(g => g.id == guestId);
    if (!guest) {
        window.GuestManager?.showNotification('Guest not found', 'error');
        return;
    }

    // Debug: Log guest data


    // Show the edit modal
    showModal('editGuestModal');
    
    // Render the edit fields with guest data
    renderEditGuestFields(window.guestListSettings, guest);
    
    // Set the guest ID for the form
    document.getElementById('editGuestForm').setAttribute('data-guest-id', guestId);
    
    
};

// Render edit guest fields with existing data
window.renderEditGuestFields = function(settings, guest) {

    
    const container = document.getElementById('editGuestFields');
    if (!container) {
        console.error('editGuestFields container not found!');
        return;
    }

    
    let html = '';
    // Name (always required)
    html += `
        <div>
            <label class="form-label">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="form-input" placeholder="Name" value="${guest.name || ''}" required>
        </div>
    `;
    
    // Email
    if (settings.fields.email) {
        html += `
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="Email" value="${guest.email || ''}">
            </div>
        `;
    }
    
    // Phone
    if (settings.fields.phone) {
        html += `
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" placeholder="Phone" value="${guest.phone || ''}">
            </div>
        `;
    }
    
    // Group
    if (settings.fields.group) {
        html += `
            <div>
                <label class="form-label">Group</label>
                <select name="group_id" class="form-select" id="editGuestGroupSelect">
                    <option value="">No group</option>
                </select>
            </div>
        `;
    }
    
    // Preferred Language
    if (settings.fields.language) {
        html += `
            <div>
                <label class="form-label">Preferred Language</label>
                <input type="text" name="language" class="form-input" placeholder="Preferred Language" value="${guest.language || ''}">
            </div>
        `;
    }
    
    // Notes
    html += `
        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input" placeholder="Notes (optional)" rows="3">${guest.notes || ''}</textarea>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Debug: Check what was rendered

    
    // Check if name field exists after rendering
    const nameFieldAfter = container.querySelector('input[name="name"]');

    
    // If group select, load groups and set current selection
    if (settings.fields.group) {
        loadGroups(function(groups) {
            const select = document.getElementById('editGuestGroupSelect');
            if (select && groups && groups.length > 0) {
                // Clear existing options first to prevent duplicates
                select.innerHTML = '<option value="">No group</option>';
                groups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    if (guest.group_id == group.id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        });
    }
};

// Update Guest via AJAX
window.updateGuest = function(formDataObj) {
    const guestId = document.getElementById('editGuestForm').getAttribute('data-guest-id');
    if (!guestId) {
        window.GuestManager?.showNotification('Guest ID not found', 'error');
        return Promise.reject('Guest ID not found');
    }

    const url = '/organizer/guest-lists/' + window.guestListId + '/guests/' + guestId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    return fetch(url, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(formDataObj)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                
                if (text.includes('<form') && text.toLowerCase().includes('login')) {
                    throw new Error('Please log in to continue.');
                }
                
                try {
                    const jsonData = JSON.parse(text);
                    
                    // Handle different possible error response formats
                    if (jsonData.errors) {
                        // Handle validation errors - show specific field errors
                        const errorMessages = Object.entries(jsonData.errors).map(([field, messages]) => {
                            const fieldName = field.charAt(0).toUpperCase() + field.slice(1);
                            return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                        }).join('; ');
                        return Promise.reject(new Error(errorMessages));
                    }
                    
                    if (jsonData.message) {
                        return Promise.reject(new Error(jsonData.message));
                    }
                    
                    // Check for other possible error formats
                    if (jsonData.error) {
                        return Promise.reject(new Error(jsonData.error));
                    }
                    
                    // If we have JSON but no errors or message, it might be a different type of error
                    return Promise.reject(new Error('Update failed. Please try again.'));
                } catch (parseError) {
                    
                    // If it's not JSON, it's probably an HTML error page
                    // Try to extract any meaningful error from HTML
                    if (text.includes('error') || text.includes('Error')) {
                        // Try to find error messages in HTML
                        const errorMatch = text.match(/<[^>]*error[^>]*>([^<]*)<\/[^>]*>/i);
                        if (errorMatch) {
                            return Promise.reject(new Error(errorMatch[1].trim()));
                        }
                    }
                    
                    return Promise.reject(new Error('Server error occurred. Please try again.'));
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.guest) {
            // Update the guest in the local array
            const guestIndex = window.guestListGuests.findIndex(g => g.id == guestId);
            if (guestIndex !== -1) {
                window.guestListGuests[guestIndex] = { ...window.guestListGuests[guestIndex], ...data.guest };
            }
            
            // Re-render the table
            if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                window.renderGuestTable(window.guestListSettings, window.guestListGuests);
            }
            
            window.GuestManager?.showNotification('Guest updated successfully', 'success');
            
            // Refresh validation errors after updating
            refreshValidationErrors();
            hideModal('editGuestModal');
            return data.guest;
        } else {
            window.GuestManager?.showNotification(data.message || 'Error updating guest', 'error');
            throw new Error(data.message || 'Error updating guest');
        }
    })
    .catch(error => {
        if (error.message && error.message.includes('log in')) {
            window.GuestManager?.showNotification(error.message, 'error');
        } else {
            window.GuestManager?.showNotification(error.message || 'Error updating guest', 'error');
        }
        throw error;
    });
};

// ************* END EDIT GUEST FUNCTIONALITY ********************

// ************* GROUP MANAGEMENT ********************

// Edit group function
window.editGroup = function(groupId, currentName, currentDescription = '') {
    // Create a custom edit modal using the confirmation modal structure
    const editModalId = 'editGroupModal';
    
    // Create the edit modal if it doesn't exist
    if (!document.getElementById(editModalId)) {
        const editModal = document.createElement('div');
        editModal.id = editModalId;
        editModal.className = 'modal hidden';
        editModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" style="color: var(--text-primary)">Edit Group</h3>
                    <button type="button" class="modal-close" onclick="hideEditGroupModal()"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">Group Name <span class="text-red-500">*</span></label>
                        <input type="text" id="editGroupNameInput" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               style="border-color: var(--border-primary); background: var(--bg-primary); color: var(--text-primary);" 
                               placeholder="Enter group name" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">Description</label>
                        <textarea id="editGroupDescriptionInput" rows="3" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" 
                                  style="border-color: var(--border-primary); background: var(--bg-primary); color: var(--text-primary);" 
                                  placeholder="Enter group description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" onclick="hideEditGroupModal()">
                        Cancel
                    </button>
                    <button type="button" class="modal-btn modal-btn-primary" onclick="updateGroup()">
                        Update Group
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(editModal);
    }
    
    // Set the current group data for editing
    window.currentEditGroup = { id: groupId, name: currentName, description: currentDescription };
    
    // Set the input values
    const nameInput = document.getElementById('editGroupNameInput');
    const descriptionInput = document.getElementById('editGroupDescriptionInput');
    
    if (nameInput) {
        nameInput.value = currentName;
    }
    if (descriptionInput) {
        descriptionInput.value = currentDescription || '';
    }
    
    // Show the modal
    const modal = document.getElementById(editModalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
        nameInput.focus();
    }
};

// Hide edit group modal
window.hideEditGroupModal = function() {
    const modal = document.getElementById('editGroupModal');
    if (modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }
    window.currentEditGroup = null;
};

// Update group function
window.updateGroup = function() {
    if (!window.currentEditGroup) return;
    
    const newName = document.getElementById('editGroupNameInput').value.trim();
    const newDescription = document.getElementById('editGroupDescriptionInput').value.trim();
    
    if (!newName) {
        window.GuestManager?.showNotification('Group name cannot be empty', 'error');
        return;
    }
    
    const { id: groupId } = window.currentEditGroup;
    
    // Make API call to update group
    const url = `/organizer/guest-lists/${window.guestListId}/groups/${groupId}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const requestBody = {
        name: newName,
        description: newDescription
    };
    
    
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(requestBody)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                try {
                    const jsonData = JSON.parse(text);
                    if (jsonData.errors) {
                        const errorMessages = Object.entries(jsonData.errors).map(([field, messages]) => {
                            const fieldName = field.charAt(0).toUpperCase() + field.slice(1);
                            return `${fieldName}: ${Array.isArray(messages) ? messages.join(', ') : messages}`;
                        }).join('; ');
                        throw new Error(errorMessages);
                    }
                    if (jsonData.message) {
                        throw new Error(jsonData.message);
                    }
                    throw new Error('Failed to update group');
                } catch (parseError) {
                    throw new Error('Failed to update group');
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.GuestManager?.showNotification('Group updated successfully', 'success');
            
            // Update the group in the local array
            if (window.guestListGroups) {
                const groupIndex = window.guestListGroups.findIndex(g => g.id == groupId);
                if (groupIndex !== -1) {
                    window.guestListGroups[groupIndex].name = newName;
                    window.guestListGroups[groupIndex].description = newDescription;
                }
            }
            
            // Re-render the table to show updated group name
            if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                window.renderGuestTable(window.guestListSettings, window.guestListGuests);
            }
            
            // Refresh validation errors
            if (window.refreshValidationErrors) {
                window.refreshValidationErrors();
            }
            
            // Hide the modal
            hideEditGroupModal();
        } else {
            window.GuestManager?.showNotification(data.message || 'Error updating group', 'error');
        }
    })
    .catch(error => {
        window.GuestManager?.showNotification(error.message || 'Error updating group', 'error');
    });
};

// Delete group function
window.deleteGroup = function(groupId, groupName) {
    // Use the confirmation modal for deletion
    const onConfirm = () => {
        // Make API call to delete group
        const url = `/organizer/guest-lists/${window.guestListId}/groups/${groupId}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const jsonData = JSON.parse(text);
                        if (jsonData.message) {
                            throw new Error(jsonData.message);
                        }
                        throw new Error('Failed to delete group');
                    } catch (parseError) {
                        throw new Error('Failed to delete group');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.GuestManager?.showNotification('Group deleted successfully', 'success');
                
                // Remove the group from the local array
                if (window.guestListGroups) {
                    window.guestListGroups = window.guestListGroups.filter(g => g.id != groupId);
                }
                
                // Remove all guests in this group from the local array
                if (window.guestListGuests) {
                    window.guestListGuests = window.guestListGuests.filter(guest => guest.group_id != groupId);
                }
                
                // Re-render the table to show updated structure
                if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
                    window.renderGuestTable(window.guestListSettings, window.guestListGuests);
                }
                
                // Refresh validation errors
                if (window.refreshValidationErrors) {
                    window.refreshValidationErrors();
                }
            } else {
                window.GuestManager?.showNotification(data.message || 'Error deleting group', 'error');
            }
        })
        .catch(error => {
            window.GuestManager?.showNotification(error.message || 'Error deleting group', 'error');
        });
    };
    
    // Show confirmation modal with custom content
    showConfirmationModal('confirmationModal', onConfirm);
    
    // Update modal content for delete confirmation
    const modalTitle = document.querySelector('#confirmationModal .modal-title');
    const modalMessage = document.querySelector('#confirmationModal .modal-body p');
    const confirmBtn = document.querySelector('#confirmationModal .modal-btn-primary');
    
    if (modalTitle) modalTitle.textContent = 'Delete Group';
    if (modalMessage) modalMessage.textContent = `Are you sure you want to delete the group "${groupName}"? This will permanently delete the group and ALL guests in this group. This action cannot be undone.`;
    if (confirmBtn) confirmBtn.textContent = 'Delete Group';
};

// ************* END GROUP MANAGEMENT ********************

// Function to refresh validation errors
window.refreshValidationErrors = function() {
    if (!window.guestListId) return;
    
    // Add a small delay to ensure guest data is updated first
    setTimeout(() => {
        fetch(`/organizer/guest-lists/${window.guestListId}/validation-errors`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.errors && data.summary) {
                    window.validationErrors = data.errors;
                    window.errorSummary = data.summary;
                    
                    // Update the error display in the UI
                    updateErrorDisplay();
                }
            })
            .catch(error => {
                console.error('Error refreshing validation errors:', error);
                // If there's an error, still try to update the display with current data
                updateErrorDisplay();
            });
    }, 100);
};

// Function to update error display in the UI
window.updateErrorDisplay = function() {
    const errorContainer = document.querySelector('[data-error-display]');
    const iconContainer = document.getElementById('healthIconContainer');
    
    if (!errorContainer) return;
    
    let html = '';
    let iconHtml = '';
    
    if (window.errorSummary && (window.errorSummary.total_errors > 0 || window.errorSummary.total_warnings > 0)) {
        if (window.errorSummary.total_errors > 0) {
            html += `<span class="text-xl font-bold text-red-600">${window.errorSummary.total_errors}</span>`;
            html += `<span class="text-sm font-medium text-red-600">errors</span>`;
            
            iconHtml = `
                <div class="p-2 rounded-lg bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            `;
        } else if (window.errorSummary.total_warnings > 0) {
            html += `<span class="text-xl font-bold text-orange-600">${window.errorSummary.total_warnings}</span>`;
            html += `<span class="text-sm font-medium text-orange-600">warnings</span>`;
            
            iconHtml = `
                <div class="p-2 rounded-lg bg-orange-100">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            `;
        }
        
        if (window.errorSummary.total_errors > 0 && window.errorSummary.total_warnings > 0) {
            html = html.replace('</span>', '</span><span class="text-gray-400 mx-1">•</span>');
        }
    } else {
        html = `<span class="text-xl font-bold text-green-600">0</span><span class="text-sm font-medium text-green-600">issues</span>`;
        
        iconHtml = `
            <div class="p-2 rounded-lg bg-green-100">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        `;
    }
    
    errorContainer.innerHTML = html;
    if (iconContainer) {
        iconContainer.innerHTML = iconHtml;
    }
}

// Form submission handlers are now in the HTML file to avoid conflicts
