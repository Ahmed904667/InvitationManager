{{-- Duplicate Guests Modal Component --}}
<div id="duplicateGuestsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold text-primary flex items-center">
                <i class="fas fa-exclamation-triangle text-warning-500 mr-3"></i>
                Duplicate Guests Found
            </h3>
            <button onclick="closeDuplicateGuestsModal()" class="text-gray-500 hover:text-gray-700 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <div class="bg-warning-50 border-2 border-warning-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-warning-500 text-xl mr-3"></i>
                    <div>
                        <h4 class="text-warning-700 font-semibold mb-1">What are duplicates?</h4>
                        <p class="text-warning-600 text-sm">
                            Duplicate guests are found across your selected guest lists when they have the same email, phone number, or name. 
                            You can remove specific guests from this event (they won't be deleted from your guest lists).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="duplicatesContent">
            {{-- Content will be populated by JavaScript --}}
        </div>

        <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
            <div class="text-sm text-secondary">
                <span id="selectedCount">0</span> guest(s) selected for removal
            </div>
            <div class="flex gap-3">
                <button onclick="closeDuplicateGuestsModal()" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button onclick="removeSelectedDuplicates()" id="removeButton" class="btn btn-primary" disabled>
                    <i class="fas fa-trash mr-2"></i> Remove Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables for duplicate guests management
let duplicateGuestsData = null;
let selectedGuestIds = new Set();

// Show the duplicate guests modal
window.showDuplicateGuestsModal = function(duplicates) {
    duplicateGuestsData = duplicates;
    selectedGuestIds.clear();
    
    const modal = document.getElementById('duplicateGuestsModal');
    const content = document.getElementById('duplicatesContent');
    
    if (!modal || !content) return;
    
    // Generate content based on duplicates
    let html = '';
    
    if (duplicates.email && duplicates.email.length > 0) {
        html += generateDuplicateSection('email', 'Email Addresses', duplicates.email);
    }
    
    if (duplicates.phone && duplicates.phone.length > 0) {
        html += generateDuplicateSection('phone', 'Phone Numbers', duplicates.phone);
    }
    
    // Note: Name duplicates are not checked - only email and phone duplicates are validated
    
    content.innerHTML = html;
    
    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Update selection count
    updateSelectionCount();
}

// Generate HTML for a duplicate section
function generateDuplicateSection(field, title, duplicates) {
    let html = `
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-primary mb-4 flex items-center">
                <i class="fas fa-${getFieldIcon(field)} text-primary-500 mr-2"></i>
                ${title} (${duplicates.length} duplicate${duplicates.length > 1 ? 's' : ''})
            </h4>
            <div class="space-y-4">
    `;
    
    duplicates.forEach(duplicate => {
        html += `
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold mr-3">
                            ${duplicate.value}
                        </span>
                        <span class="text-sm text-secondary">
                            Found in ${duplicate.count} guest${duplicate.count > 1 ? 's' : ''}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" class="select-all-checkbox mr-2" data-field="${field}" data-value="${duplicate.value}">
                            Select All
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        `;
        
        duplicate.guests.forEach(guest => {
            html += `
                <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center">
                    <input type="checkbox" 
                           class="guest-checkbox mr-3" 
                           data-guest-id="${guest.id}"
                           data-field="${field}"
                           data-value="${duplicate.value}"
                           onchange="updateGuestSelection()">
                    <div class="flex-1">
                        <div class="font-semibold text-primary">${guest.name}</div>
                        <div class="text-sm text-secondary">
                            ${guest.email || guest.phone || 'No contact info'}
                        </div>
                        <div class="text-xs text-gray-400">
                            List: ${guest.guest_list_name}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    return html;
}

// Get icon for field type
function getFieldIcon(field) {
    switch(field) {
        case 'email': return 'envelope';
        case 'phone': return 'phone';
        case 'name': return 'user';
        default: return 'info-circle';
    }
}

// Close the duplicate guests modal
window.closeDuplicateGuestsModal = function() {
    const modal = document.getElementById('duplicateGuestsModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Clear selections
    selectedGuestIds.clear();
    duplicateGuestsData = null;
}

// Update guest selection
window.updateGuestSelection = function() {
    const checkboxes = document.querySelectorAll('.guest-checkbox');
    selectedGuestIds.clear();
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedGuestIds.add(parseInt(checkbox.dataset.guestId));
        }
    });
    
    updateSelectionCount();
}

// Update select all checkboxes
function updateSelectAllCheckboxes() {
    const selectAllCheckboxes = document.querySelectorAll('.select-all-checkbox');
    
    selectAllCheckboxes.forEach(selectAllCheckbox => {
        const field = selectAllCheckbox.dataset.field;
        const value = selectAllCheckbox.dataset.value;
        const guestCheckboxes = document.querySelectorAll(`.guest-checkbox[data-field="${field}"][data-value="${value}"]`);
        
        const checkedCount = Array.from(guestCheckboxes).filter(cb => cb.checked).length;
        selectAllCheckbox.checked = checkedCount === guestCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < guestCheckboxes.length;
    });
}

// Handle select all checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('select-all-checkbox')) {
        const field = e.target.dataset.field;
        const value = e.target.dataset.value;
        const guestCheckboxes = document.querySelectorAll(`.guest-checkbox[data-field="${field}"][data-value="${value}"]`);
        
        guestCheckboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        
        updateGuestSelection();
    }
});

// Update selection count display
function updateSelectionCount() {
    const countElement = document.getElementById('selectedCount');
    const removeButton = document.getElementById('removeButton');
    
    if (countElement) {
        countElement.textContent = selectedGuestIds.size;
    }
    
    if (removeButton) {
        removeButton.disabled = selectedGuestIds.size === 0;
    }
    
    updateSelectAllCheckboxes();
}

// Remove selected duplicates
window.removeSelectedDuplicates = function() {
    if (selectedGuestIds.size === 0) {
        return;
    }
    
    const removeButton = document.getElementById('removeButton');
    const originalText = removeButton.innerHTML;
    
    // Show loading state
    removeButton.disabled = true;
    removeButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Removing...';
    
    // Send request to remove duplicates
    fetch('{{ route("organizer.events.create.remove-duplicates") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            guests_to_remove: Array.from(selectedGuestIds)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            closeDuplicateGuestsModal();
            
            // Show success message
            if (window.GuestManager?.showNotification) {
                window.GuestManager.showNotification(data.message, 'success');
            }
            
            // Re-enable the form submit button and restore original text
            const submitButton = document.getElementById('finalSubmit');
            if (submitButton) {
                submitButton.disabled = false;
                const isUpdate = {{ request()->has('mode') && request()->get('mode') === 'update' ? 'true' : 'false' }};
                submitButton.innerHTML = isUpdate 
                    ? '<i class="fas fa-paper-plane mr-2"></i>Update Event & Send Invitations'
                    : '<i class="fas fa-paper-plane mr-2"></i>Create Event & Send Invitations';
            }
            
            // Optionally refresh the page or update the guest count
            // You might want to update the guest count display here
            
        } else {
            throw new Error(data.message || 'Failed to remove duplicates');
        }
    })
    .catch(error => {
        console.error('Error removing duplicates:', error);
        
        if (window.GuestManager?.showNotification) {
            window.GuestManager.showNotification('Failed to remove duplicates. Please try again.', 'error');
        }
    })
    .finally(() => {
        // Reset button state
        removeButton.disabled = false;
        removeButton.innerHTML = originalText;
    });
}

// Handle form submission with duplicate check and page load duplicate detection
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('event-form-4');
    
    // Check for duplicates in session on page load
    const hasDuplicatesInSession = {{ session()->has('event_duplicate_guests') ? 'true' : 'false' }};
    if (hasDuplicatesInSession) {
        const duplicates = @json(session('event_duplicate_guests', []));
        if (duplicates && Object.keys(duplicates).length > 0) {
            // Show warning message
            if (window.GuestManager?.showNotification) {
                window.GuestManager.showNotification('Duplicate guests were found in your guest lists. Please resolve them before proceeding.', 'warning');
            }
            
            // Disable the submit button until duplicates are resolved
            const submitButton = document.getElementById('finalSubmit');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Resolve Duplicates First';
            }
        }
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // If there are duplicates in session, prevent submission
            if (hasDuplicatesInSession) {
                e.preventDefault();
                
                // Show the duplicate modal
                const duplicates = @json(session('event_duplicate_guests', []));
                if (duplicates && Object.keys(duplicates).length > 0) {
                    showDuplicateGuestsModal(duplicates);
                }
                
                return false;
            }
        });
    }
});
</script>
