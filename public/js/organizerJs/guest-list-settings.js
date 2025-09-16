document.addEventListener('DOMContentLoaded', function() {
    const settingsForm = document.getElementById('listSettingsForm');
    if (!settingsForm) return;

    settingsForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(settingsForm);
        const url = settingsForm.getAttribute('action');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('guestListName').innerText = data.name;
                document.getElementById('guestListDescription').innerText = data.description;

                // Update global settings and re-render the table
                window.guestListSettings = data.settings;
                renderAddGuestFields(window.guestListSettings);
                updateAddGroupToolbar(window.guestListSettings);
                if (window.renderGuestTable && window.guestListGuests) {
                    window.renderGuestTable(window.guestListSettings, window.guestListGuests);
                }
                
                // Dispatch custom event to notify other components about settings update
                window.dispatchEvent(new CustomEvent('settingsUpdated', {
                    detail: { settings: data.settings }
                }));

                // Refresh validation errors since settings might affect required fields
                if (typeof refreshValidationErrors === 'function') {
                    refreshValidationErrors();
                }

                if (typeof hideListSettingsModal === 'function') {
                    hideListSettingsModal();
                }
            } else {
                window.GuestManager?.showNotification(data.message || 'Error updating list settings', 'error');
            }
        })
        .catch(() => {
            window.GuestManager?.showNotification('Error updating list settings', 'error');
        });
    });

    window.updateAddGroupToolbar = function(settings) {
        const container = document.getElementById('toolbarAddGroup');
        if (!container) return;
        if (settings.fields.group) {
            container.innerHTML = `
                <div class="toolbar-option group relative h-12">
                    <button class="toolbar-button absolute right-0 top-0 flex items-center rounded-2xl transition-all duration-300 w-12 group-hover:w-48 focus:w-48 overflow-hidden z-10 focus:outline-none focus:ring-2 focus:ring-green-500/50"
                        onclick="showModal('addGroupModal')" 
                        title="Add Group"
                        aria-label="Add new group">
                        <div class="button-bg w-full h-12 glass-effect rounded-2xl transition-all duration-300 group-hover:shadow-xl focus:shadow-xl" style="background: var(--bg-tertiary);"></div>
                        <div class="absolute inset-0 flex items-center px-3">
                            <svg class="w-6 h-6 flex-shrink-0 transition-transform group-hover:scale-110 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success-600);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="expand-text ml-3 text-sm font-semibold opacity-0 group-hover:opacity-100 focus:opacity-100 whitespace-nowrap" style="color: var(--text-primary);">Add Group</span>
                        </div>
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = '';
        }
    };

    window.updateGroupsCount = function(count) {
        const el = document.getElementById('groupsCount');
        if (el) el.textContent = count;
    };
    updateGroupsCount();



    // Ensure toolbar is updated on page load
    updateAddGroupToolbar(window.guestListSettings);


    function renderAddGuestFields(settings) {
       
        const container = document.getElementById('addGuestFields');
        if (!container) return;
        let html = '';
        // Name (always required)
        html += `
            <div>
                <label class="form-label">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Name" required>
            </div>
        `;
        // Email
        if (settings.fields.email) {
            html += `
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Email">
                </div>
            `;
        }
        // Phone
        if (settings.fields.phone) {
            // Use guest list's own country code setting, fallback to organizer defaults
            const defaultCountryCode = settings.default_country_code || (settings.defaults && settings.defaults.country_code ? settings.defaults.country_code : '');
            html += `
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input phone-input" placeholder="${defaultCountryCode} Phone number" value="${defaultCountryCode}">
                </div>
            `;
        }
        // Group
        if (settings.fields.group) {
            html += `
                <div>
                    <label class="form-label">Group</label>
                    <select name="group_id" class="form-select" id="addGuestGroupSelect">
                        <option value="">No group</option>
                    </select>
                </div>
            `;
        }
        // Preferred Language
        if (settings.fields.language) {

            // Use guest list's own language setting, fallback to organizer defaults
            const defaultLang = settings.default_language || (settings.defaults && settings.defaults.language ? settings.defaults.language : '');
            
            html += `
                <div>
                    <label class="form-label">Preferred Language</label>
                    <input type="text" name="language" class="form-input" placeholder="Preferred Language" value="${defaultLang}">
                </div>
            `;
        }
        
        // Notes
        html += `
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" placeholder="Notes (optional)" rows="3"></textarea>
            </div>
        `;
        
        container.innerHTML = html;
        
        // Add phone input restrictions
        const phoneInput = container.querySelector('input[name="phone"]');
        if (phoneInput) {
            addPhoneInputRestrictions(phoneInput);
        }
        
        // If group select, reload groups and disable submit until loaded
        if (settings.fields.group) {
            const submitBtn = document.querySelector('#addGuestForm button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            loadGroups(function(groups) {
                const select = document.getElementById('addGuestGroupSelect');
                if (select && groups && groups.length > 0) {
                    groups.forEach(group => {
                        const option = document.createElement('option');
                        option.value = group.id;
                        option.textContent = group.name;
                        select.appendChild(option);
                    });
                    // Optionally select the first group by default
                    // select.value = groups[0].id;
                }
                if (submitBtn) submitBtn.disabled = false;
            });
        }
    }

    // Update loadGroups to accept a callback for when groups are loaded
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

     // showModal function for add guest modal to load the fields

    window.showAddGuestModal = function() {
        if (typeof renderAddGuestFields === 'function') {
            renderAddGuestFields(window.guestListSettings);
        }
        window.showModal('addGuestModal');
    };

    // Function to add phone input restrictions
    window.addPhoneInputRestrictions = function(phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Allow only numbers, +, spaces, hyphens, and parentheses
            let value = e.target.value;
            let filteredValue = value.replace(/[^+0-9\s\-\(\)]/g, '');
            
            if (value !== filteredValue) {
                e.target.value = filteredValue;
                // Show a brief visual feedback
                e.target.style.borderColor = '#ef4444';
                setTimeout(() => {
                    e.target.style.borderColor = '';
                }, 1000);
            }
        });
        
        phoneInput.addEventListener('keypress', function(e) {
            // Allow backspace, delete, arrow keys, tab
            if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'ArrowLeft' || 
                e.key === 'ArrowRight' || e.key === 'ArrowUp' || e.key === 'ArrowDown' || 
                e.key === 'Tab') {
                return true;
            }
            
            // Allow only numbers, +, spaces, hyphens, and parentheses
            const allowedChars = /[+0-9\s\-\(\)]/;
            if (!allowedChars.test(e.key)) {
                e.preventDefault();
                // Show a brief visual feedback
                e.target.style.borderColor = '#ef4444';
                setTimeout(() => {
                    e.target.style.borderColor = '';
                }, 1000);
                return false;
            }
        });
        
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const filteredText = pastedText.replace(/[^+0-9\s\-\(\)]/g, '');
            
            // Insert the filtered text at cursor position
            const start = e.target.selectionStart;
            const end = e.target.selectionEnd;
            const currentValue = e.target.value;
            
            e.target.value = currentValue.substring(0, start) + filteredText + currentValue.substring(end);
            
            // Set cursor position after the pasted text
            const newPosition = start + filteredText.length;
            e.target.setSelectionRange(newPosition, newPosition);
            
            if (pastedText !== filteredText) {
                // Show a brief visual feedback
                e.target.style.borderColor = '#ef4444';
                setTimeout(() => {
                    e.target.style.borderColor = '';
                }, 1000);
            }
        });
    };

});