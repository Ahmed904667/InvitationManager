// ************* GOOGLE CONTACTS IMPORT ********************

// Google Contacts Import Modal Logic

let allGoogleContacts = [];

// Show Google Contacts modal
window.showGoogleContactsModal = function() {
    // Set dynamic search bar placeholder
    let fields = ['name'];
    if (window.guestListSettings?.fields?.email) fields.push('email');
    if (window.guestListSettings?.fields?.phone) fields.push('phone');
    const placeholder = 'Search by ' + fields.join(', ') + '...';
    const searchInput = document.getElementById('googleContactsSearchInput');
    if (searchInput) searchInput.placeholder = placeholder;

    // Show loading spinner or message in modal
    const tbody = document.getElementById('googleContactsTable');
    tbody.innerHTML = '<tr><td colspan="4">Loading contacts...</td></tr>';
    
    // Show loading message for sheets
    const sheetsContainer = document.getElementById('googleSheetsListContainer');
    if (sheetsContainer) {
        sheetsContainer.innerHTML = '<div class="text-center py-4"><div class="text-sm text-gray-600">Loading Google Sheets...</div></div>';
    }
    
    // Show group dropdown if group is enabled in settings
    const groupEnabled = window.guestListSettings?.fields?.group;
    const groupDiv = document.getElementById('googleContactsGroupDiv');
    const groupInfo = document.getElementById('googleContactsGroupInfo');
    
    if (groupEnabled) {
        // Show group dropdown when groups are enabled - manual assignment
        groupDiv.classList.remove('hidden');
        // Hide group info text
        if (groupInfo) {
            groupInfo.style.display = 'none';
        }
        // Populate group dropdown
        const select = document.getElementById('googleContactsGroupSelect');
        if (select) {
            select.innerHTML = '<option value="">No group</option>';
            if (window.guestListGroups && window.guestListGroups.length > 0) {
                window.guestListGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    select.appendChild(option);
                });
            }
        }
    } else {
        // Hide group dropdown when groups are disabled - no group assignment available
        groupDiv.classList.add('hidden');
        // Hide group info text since no groups are available
        if (groupInfo) {
            groupInfo.style.display = 'none';
        }
    }

    // Show/hide default language checkbox
    const langDiv = document.getElementById('googleContactsDefaultLangDiv');
    if (window.guestListSettings?.fields?.language) {
        langDiv.classList.remove('hidden');
    } else {
        langDiv.classList.add('hidden');
    }

    // Set up search bar
    if (searchInput) {
        searchInput.value = '';
        searchInput.oninput = function() {
            filterGoogleContacts();
        };
    }
    showModal('googleContactsModal');
    // Load contacts and groups after modal is shown
    loadGoogleContacts();
    // Ensure groups are loaded for the dropdown
    if (typeof loadGroups === 'function') {
        loadGroups();
        // Also refresh the dropdown after a short delay to ensure groups are loaded
        setTimeout(() => {
            const select = document.getElementById('googleContactsGroupSelect');
            if (select && window.guestListGroups && window.guestListGroups.length > 0) {
                select.innerHTML = '<option value="">No group</option>';
                window.guestListGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    select.appendChild(option);
                });
            }
        }, 500);
    }
}

window.loadGoogleContacts = function() {
    const tbody = document.getElementById('googleContactsTable');
    fetch(`/organizer/guest-lists/${window.guestListId}/google-contacts`)
        .then(response => {
            if (response.status === 401) {
                // Not authenticated, redirect to Google OAuth
                window.location.href = `/google/redirect?guest_list_id=${window.guestListId}&modal=contacts`;
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Already redirected
            if (data.contacts && data.contacts.length > 0) {
                allGoogleContacts = data.contacts;
                renderGoogleContactsTable(allGoogleContacts);
            } else {
                allGoogleContacts = [];
                tbody.innerHTML = '<tr><td colspan="4">No contacts found.</td></tr>';
            }
        })
        .catch(() => {
            allGoogleContacts = [];
            tbody.innerHTML = '<tr><td colspan="4">Failed to load contacts.</td></tr>';
        });
}



function filterGoogleContacts() {
    const searchInput = document.getElementById('googleContactsSearchInput');
    if (!searchInput) return;
    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
        renderGoogleContactsTable(allGoogleContacts);
        return;
    }
    const showEmail = window.guestListSettings?.fields?.email;
    const showPhone = window.guestListSettings?.fields?.phone;
    const filtered = allGoogleContacts.filter(contact => {
        let match = contact.name && contact.name.toLowerCase().includes(query);
        if (showEmail && contact.email) {
            match = match || contact.email.toLowerCase().includes(query);
        }
        if (showPhone && contact.phone) {
            match = match || contact.phone.toLowerCase().includes(query);
        }
        return match;
    });
    renderGoogleContactsTable(filtered);
}

// Update renderGoogleContactsTable to accept contacts as argument
window.renderGoogleContactsTable = function(contacts) {
    const tbody = document.getElementById('googleContactsTable');
    tbody.innerHTML = '';
    // Determine which columns to show
    const showEmail = window.guestListSettings?.fields?.email;
    const showPhone = window.guestListSettings?.fields?.phone;
    // Update table header
    const thead = tbody.parentElement.querySelector('thead tr');
    let headerHtml = '<th class="px-4 py-2"><input type="checkbox" id="selectAllContacts" class="form-checkbox" /></th>';
    headerHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>';
    if (showEmail) {
        headerHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>';
    }
    if (showPhone) {
        headerHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>';
    }
    thead.innerHTML = headerHtml;
    // Render rows
    contacts.forEach((contact, idx) => {
        let rowHtml = `<td class="px-4 py-2"><input type="checkbox" class="contact-checkbox form-checkbox" value="${idx}"></td>`;
        rowHtml += `<td class="px-4 py-2 text-primary">${contact.name}</td>`;
        if (showEmail) {
            rowHtml += `<td class="px-4 py-2 text-gray-500">${contact.email}</td>`;
        }
        if (showPhone) {
            rowHtml += `<td class="px-4 py-2 text-gray-500">${contact.phone || '-'}</td>`;
        }
        const tr = document.createElement('tr');
        tr.innerHTML = rowHtml;
        tbody.appendChild(tr);
    });
    // Select all handler
    const selectAll = document.getElementById('selectAllContacts');
    selectAll.checked = false;
    selectAll.onclick = function() {
        document.querySelectorAll('.contact-checkbox').forEach(cb => {
            cb.checked = selectAll.checked;
        });
    };
};

// Ensure DOM is ready before attaching form handler
window.addEventListener('DOMContentLoaded', function() {
    const googleContactsImportForm = document.getElementById('googleContactsImportForm');
    if (googleContactsImportForm) {
        googleContactsImportForm.onsubmit = function(e) {
            e.preventDefault();
            const selectedIds = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => parseInt(cb.value));
            if (selectedIds.length === 0) {
                window.GuestManager?.showNotification('Please select at least one contact.', 'error');
                return;
            }
            // Get selected contacts data
            let selectedContacts = selectedIds.map(idx => allGoogleContacts[idx]);
            // Check if auto apply default language is checked
            const applyDefaultLang = document.getElementById('googleContactsApplyDefaultLanguage');
            if (applyDefaultLang && applyDefaultLang.checked && window.guestListSettings?.default_language) {
                selectedContacts = selectedContacts.map(contact => ({
                    ...contact,
                    language: window.guestListSettings.default_language
                }));
            }
            // Get selected group (if any) - manual assignment for contacts
            let groupId = '';
            const groupEnabled = window.guestListSettings?.fields?.group;
            if (groupEnabled) {
                // Use manual group assignment when groups are enabled in settings
                const groupSelect = document.getElementById('googleContactsGroupSelect');
                if (groupSelect) {
                    groupId = groupSelect.value;
                }
            }
            // When groups are disabled, no group assignment is available for contacts
            // Send to backend
            fetch(`/organizer/guest-lists/${window.guestListId}/import-google-contacts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    selected_contacts: selectedContacts.map(contact => JSON.stringify(contact)),
                    group_id: groupId || null
                })
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
                            throw new Error('Import failed. Please try again.');
                        } catch (parseError) {
                            throw new Error('Import failed. Please try again.');
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    hideModal('googleContactsModal');
                    window.GuestManager?.showNotification(data.message || `Successfully imported ${data.imported} contacts`, 'success');
                    
                    // Refresh the guest list and validation errors
                    if (window.refreshValidationErrors) {
                        window.refreshValidationErrors();
                    }
                    
                    // Reload the page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    // Show detailed error message including duplicates
                    let errorMessage = data.message || 'Error importing contacts.';
                    
                    if (data.duplicates && data.duplicates.length > 0) {
                        errorMessage += '\n\nDuplicates found:\n' + data.duplicates.join('\n');
                    }
                    
                    if (data.errors && data.errors.length > 0) {
                        errorMessage += '\n\nOther errors:\n' + data.errors.join('\n');
                    }
                    
                    window.GuestManager?.showNotification(errorMessage, 'error');
                }
            })
            .catch((error) => {
                window.GuestManager?.showNotification(error.message || 'Error importing contacts.', 'error');
            });
        };
    }
}); 

// ************* END GOOGLE CONTACTS IMPORT ********************

// ************* GOOGLE SHEETS IMPORT ********************

// Google Sheets pagination and cache management
let currentPage = 1;
let hasMoreSheets = false;
let allValidSheets = [];
let allUnvalidSheets = [];
let currentShowUnvalid = false;
let currentPageToken = null;
let nextPageToken = null;

// Google Sheets cache variables
let cachedSheets = null;
let cachedSheetsTimestamp = 0;
const SHEETS_CACHE_TTL = 5 * 60 * 1000; // 5 minutes
const AUTH_CACHE_TTL = 2 * 60 * 1000; // 2 minutes for auth checks

// Persistent cache helpers for Google Sheets
function saveSheetsCacheToStorage(data) {
    const cacheData = {
        ...data,
        timestamp: Date.now()
    };
    localStorage.setItem('googleSheetsCache_' + window.guestListId, JSON.stringify(cacheData));
}
function loadSheetsCacheFromStorage() {
    const data = localStorage.getItem('googleSheetsCache_' + window.guestListId);
    if (data) {
        const cacheData = JSON.parse(data);
        const now = Date.now();
        if (now - cacheData.timestamp < SHEETS_CACHE_TTL) {
            return cacheData;
        }
    }
    return null;
}
function clearSheetsCacheInStorage() {
    localStorage.removeItem('googleSheetsCache_' + window.guestListId);
    // Also clear the in-memory cache
    cachedSheets = null;
    cachedSheetsTimestamp = 0;
    allValidSheets = [];
    allUnvalidSheets = [];
    currentPageToken = null;
    nextPageToken = null;
    currentPage = 1;
}

// Function to clear all Google-related caches (can be called on logout)
window.clearGoogleCaches = function() {
    clearSheetsCacheInStorage();
    // Clear any other Google-related caches here
    console.log('Google caches cleared');
};

// Function to reset sheets state
function resetSheetsState() {
    currentPage = 1;
    allValidSheets = [];
    allUnvalidSheets = [];
    currentShowUnvalid = false;
    currentPageToken = null;
    nextPageToken = null;
}

window.showGoogleSheetsModal = function() {
    // Dynamically display required columns
    const reqList = document.getElementById('googleSheetsRequiredColumns');
    if (reqList) {
        let fields = ['Name'];
        if (window.guestListSettings?.fields?.email) fields.push('Email');
        if (window.guestListSettings?.fields?.phone) fields.push('Phone');
        if (window.guestListSettings?.fields?.language) fields.push('Language');
        if (window.guestListSettings?.fields?.group) {
            fields.push('Group (required - will create groups automatically)');
        }
        reqList.innerHTML = fields.map(f => `<li>${f}</li>`).join('');
    }

    // Show group dropdown if group is enabled in settings
    const groupEnabled = window.guestListSettings?.fields?.group;
    const groupDiv = document.getElementById('googleSheetsGroupDiv');
    const groupInfo = document.getElementById('googleSheetsGroupInfo');
    
    if (groupEnabled) {
        // Hide group dropdown when groups are enabled - groups will be assigned from sheet column
        groupDiv.classList.add('hidden');
        // Show group info text
        if (groupInfo) {
            groupInfo.style.display = 'block';
        }
    } else {
        // Show group dropdown only when groups are disabled - manual assignment
        groupDiv.classList.remove('hidden');
        // Hide group info text
        if (groupInfo) {
            groupInfo.style.display = 'none';
        }
        // Populate group dropdown
        const select = document.getElementById('googleSheetsGroupSelect');
        select.innerHTML = '<option value="">No group</option>';
        if (window.guestListGroups) {
            window.guestListGroups.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                select.appendChild(option);
            });
        }
    }

    // Check Google authentication first before loading from cache
    checkGoogleAuthAndLoadSheets();
}

// Helper to set refresh button handler
function setRefreshBtnHandler() {
    var refreshBtn = document.getElementById('refreshSheetsBtn');
    if (refreshBtn) {
        refreshBtn.onclick = function() {
            // Clear cache and reset state
            clearSheetsCacheInStorage();
            resetSheetsState();
            
            // Show refreshing indicator
            refreshBtn.disabled = true;
            const originalText = refreshBtn.textContent;
            refreshBtn.textContent = 'Refreshing...';
            
            // Load fresh data
            loadSheetsPage(1, true);
            
            // Restore button after a delay
            setTimeout(function() {
                refreshBtn.disabled = false;
                refreshBtn.textContent = originalText;
            }, 2000);
        };
    }
}

// Function to check Google authentication and load sheets
function checkGoogleAuthAndLoadSheets() {
    // First check if user is authenticated with Google
    fetch(`/organizer/guest-lists/${window.guestListId}/google-sheets?check_auth=1`)
        .then(res => {
            if (res.status === 401) {
                // Not authenticated, clear cache and redirect
                clearSheetsCacheInStorage();
                window.location.href = '/google/redirect?guest_list_id=' + window.guestListId + '&modal=sheets';
                throw new Error('Not authenticated with Google');
            }
            return res.json();
        })
        .then(data => {
            if (data.error) {
                // Authentication error, clear cache
                clearSheetsCacheInStorage();
                throw new Error(data.error);
            }
            
            // User is authenticated, now try to load from cache
            const cache = loadSheetsCacheFromStorage();
            if (cache) {
                // Load from cache
                allValidSheets = cache.valid_sheets || [];
                allUnvalidSheets = cache.unvalid_sheets || [];
                hasMoreSheets = cache.has_more;
                currentPage = cache.current_page || 1;
                currentPageToken = cache.current_page_token || null;
                nextPageToken = cache.next_page_token || null;
                currentShowUnvalid = false;
                
                const searchInput = document.getElementById('googleSheetsSearchInput');
                const searchValue = searchInput ? searchInput.value : '';
                renderSheetsList(
                    filterSheets(allValidSheets, searchValue),
                    filterSheets(allUnvalidSheets, searchValue),
                    currentShowUnvalid,
                    hasMoreSheets,
                    nextPageToken
                );
                showModal('googleSheetsModal');
                setRefreshBtnHandler();
                console.log(`Sheets loaded from cache: valid=${allValidSheets.length}, unvalid=${allUnvalidSheets.length}`);
            } else {
                // No cache or expired, fetch from Google
                resetSheetsState();
                showModal('googleSheetsModal');
                setRefreshBtnHandler();
                loadSheetsPage(1, true);
            }
        })
                .catch(err => {
            if (err.message !== 'Not authenticated with Google') {
                console.error('Error checking Google auth:', err);
                document.getElementById('googleSheetsListContainer').innerHTML = '<div class="text-xs text-red-500">Failed to check Google authentication.</div>';
            }
        });
}

// Setup search bar
function setupSearchBar() {
    const searchInput = document.getElementById('googleSheetsSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.oninput = function() {
            renderSheetsList(
                filterSheets(allValidSheets, searchInput.value),
                filterSheets(allUnvalidSheets, searchInput.value),
                currentShowUnvalid,
                hasMoreSheets,
                currentPage + 1
            );
        };
    }
}

function loadSheetsPage(page, isFirstLoad) {
    // Show loading indicator on Load More button if present
    const loadMoreBtn = document.getElementById('loadMoreSheetsBtn');
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'Loading...';
    }
    
    // Show loading message
    const sheetsContainer = document.getElementById('googleSheetsListContainer');
    if (sheetsContainer && isFirstLoad) {
        sheetsContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="text-sm text-gray-600 mb-2">Loading Google Sheets (max 5 sheets)...</div>
                <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            </div>`;
    } else if (sheetsContainer && !isFirstLoad) {
        // Show "Loading more..." message for pagination under the valid sheets list
        const validSheetsContainer = sheetsContainer.querySelector('.valid-sheets-container');
        if (validSheetsContainer) {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'text-center py-2 border-t border-gray-200 mt-2';
            loadingDiv.innerHTML = `
                <div class="text-xs text-gray-500">Loading more sheets...</div>
                <div class="inline-block animate-spin rounded-full h-3 w-3 border-b-2 border-blue-600 mt-1"></div>
            `;
            validSheetsContainer.appendChild(loadingDiv);
        }
    }
    
    // Add timeout for the request
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
    
    // Build URL with page token for pagination
    let url = `/organizer/guest-lists/${window.guestListId}/google-sheets?page=${page}`;
    if (currentPageToken) {
        url += `&pageToken=${encodeURIComponent(currentPageToken)}`;
    }
    
    fetch(url, {
        signal: controller.signal
    })
        .then(res => {
            if (res.status === 401) {
                // Clear cache when authentication fails
                clearSheetsCacheInStorage();
                window.location.href = '/google/redirect?guest_list_id=' + window.guestListId + '&modal=sheets';
                throw new Error('Redirecting to Google login');
            }
            return res.json();
        })
        .then(data => {
            clearTimeout(timeoutId);
            if (!data) return;
            
            if (isFirstLoad) {
                // First load: replace all sheets
                allValidSheets = data.valid_sheets || [];
                allUnvalidSheets = data.unvalid_sheets || [];
                currentPage = 1;
            } else {
                // Load more: append new sheets
                allValidSheets = allValidSheets.concat(data.valid_sheets || []);
                allUnvalidSheets = allUnvalidSheets.concat(data.unvalid_sheets || []);
                currentPage = page;
            }
            
            hasMoreSheets = data.has_more;
            nextPageToken = data.next_page_token;
            
            const searchInput = document.getElementById('googleSheetsSearchInput');
            const searchValue = searchInput ? searchInput.value : '';
            renderSheetsList(
                filterSheets(allValidSheets, searchValue),
                filterSheets(allUnvalidSheets, searchValue),
                currentShowUnvalid,
                hasMoreSheets,
                nextPageToken
            );
            
            // Update cache with all loaded sheets
            saveSheetsCacheToStorage({
                valid_sheets: allValidSheets,
                unvalid_sheets: allUnvalidSheets,
                has_more: hasMoreSheets,
                next_page_token: nextPageToken,
                current_page_token: currentPageToken,
                current_page: currentPage
            });
            
            // Log Google load (only on first load, not on load more)
            if (isFirstLoad) {
                console.log(`Sheets loaded from Google: valid=${allValidSheets.length}, unvalid=${allUnvalidSheets.length}`);
            } else {
                console.log(`More sheets loaded: valid=${data.valid_sheets?.length || 0}, unvalid=${data.unvalid_sheets?.length || 0}`);
            }
        })
        .catch(err => {
            clearTimeout(timeoutId);
            if (err.name === 'AbortError') {
                document.getElementById('googleSheetsListContainer').innerHTML = '<div class="text-xs text-red-500">Request timed out. Please try again.</div>';
            } else if (err.message !== 'Redirecting to Google login') {
                document.getElementById('googleSheetsListContainer').innerHTML = '<div class="text-xs text-red-500">Failed to load Google Sheets.</div>';
            }
        });
}

function filterSheets(sheets, query) {
    if (!query) return sheets;
    query = query.toLowerCase();
    return sheets.filter(sheet =>
        (sheet.file_name && sheet.file_name.toLowerCase().includes(query)) ||
        (sheet.sheet_title && sheet.sheet_title.toLowerCase().includes(query))
    );
}

function renderSheetsList(validSheets, unvalidSheets, showUnvalid, hasMore, nextPage) {
    const container = document.getElementById('googleSheetsListContainer');
    let html = '<div class="flex flex-col gap-2">';
    // Add counts summary
    html += `<div class="flex flex-wrap gap-4 items-center mb-2">
        <span class="text-xs text-green-700 font-semibold">Valid Sheets: ${validSheets.length}</span>
        <span class="text-xs text-red-600 font-semibold">Unvalid Sheets: ${unvalidSheets.length}</span>
    </div>`;
    
    // Valid sheets container
    html += '<div class="valid-sheets-container">';
    if (validSheets.length === 0 && (!showUnvalid || unvalidSheets.length === 0)) {
        html += '<div class="text-xs text-gray-500">No valid Google Sheets found for this guest list.</div>';
    }
    validSheets.forEach((sheet, idx) => {
        html += `<label class="flex flex-col sm:flex-row sm:items-center gap-2 p-2 rounded bg-green-50">
            <div class="flex items-center gap-2 flex-1">
                <input type="radio" name="selectedGoogleSheet" value="valid-${idx}" class="form-radio">
                <span class="font-medium">${sheet.file_name}</span>
                <span class="text-xs text-gray-500 ml-2">(${sheet.sheet_title})</span>
                <span class="text-xs text-green-600 ml-2">Valid</span>
            </div>
            <a href="https://docs.google.com/spreadsheets/d/${sheet.file_id}/edit" target="_blank" rel="noopener" class="modal-btn modal-btn-secondary px-2 py-1 text-xs">Open</a>
        </label>`;
    });
    html += '</div>';
    
    // Invalid sheets container
    if (showUnvalid && unvalidSheets.length > 0) {
        html += '<div class="invalid-sheets-container mt-2">';
        unvalidSheets.forEach((sheet, idx) => {
            html += `<label class="flex flex-col sm:flex-row sm:items-center gap-2 p-2 rounded bg-red-50 opacity-60 cursor-not-allowed">
                <div class="flex items-center gap-2 flex-1">
                    <input type="radio" disabled class="form-radio">
                    <span class="font-medium">${sheet.file_name}</span>
                    <span class="text-xs text-gray-500 ml-2">(${sheet.sheet_title})</span>
                    <span class="text-xs text-red-600 ml-2">Unvalid: missing ${sheet.missing?.join(', ') || 'required columns'}</span>
                </div>
                <a href="https://docs.google.com/spreadsheets/d/${sheet.file_id}/edit" target="_blank" rel="noopener" class="modal-btn modal-btn-secondary px-2 py-1 text-xs">Open</a>
            </label>`;
        });
        html += '</div>';
    }
    html += '</div>';
    if (hasMore) {
        html += `<button type="button" id="loadMoreSheetsBtn" class="modal-btn modal-btn-secondary mt-2">Load More</button>`;
    }
    container.innerHTML = html;

    // Add event listeners for valid sheets
    document.querySelectorAll('input[name="selectedGoogleSheet"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const idx = parseInt(this.value.replace('valid-', ''));
            const sheet = validSheets[idx];
            // Show preview (header only for now)
            const previewDiv = document.getElementById('googleSheetsPreviewTable');
            previewDiv.innerHTML = '<div class="text-xs">Columns: ' + sheet.header.join(', ') + '</div>';
            document.getElementById('googleSheetsPreviewContainer').classList.remove('hidden');
            document.getElementById('importGoogleSheetBtn').disabled = false;
            // Store selected sheet info for import
            window.selectedGoogleSheet = sheet;
        });
    });
    if (hasMore) {
        document.getElementById('loadMoreSheetsBtn').onclick = function() {
            // Set the current page token to the next page token for pagination
            currentPageToken = nextPageToken;
            loadSheetsPage(currentPage + 1, false);
        };
    }

    // Always re-attach the showUnvalidSheets event listener after render
    const showUnvalidCheckbox = document.getElementById('showUnvalidSheets');
    if (showUnvalidCheckbox) {
        showUnvalidCheckbox.checked = showUnvalid;
        showUnvalidCheckbox.onchange = function() {
            currentShowUnvalid = this.checked;
            const searchInput = document.getElementById('googleSheetsSearchInput');
            const searchValue = searchInput ? searchInput.value : '';
            renderSheetsList(
                filterSheets(allValidSheets, searchValue),
                filterSheets(allUnvalidSheets, searchValue),
                currentShowUnvalid,
                hasMoreSheets,
                nextPage
            );
        };
    }
    // Update global showUnvalid state
    currentShowUnvalid = showUnvalid;
}

// ************* END GOOGLE SHEETS IMPORT ********************

// Google Sheets Import Form Handler
window.addEventListener('DOMContentLoaded', function() {
    const googleSheetsImportForm = document.getElementById('googleSheetsImportForm');
    if (googleSheetsImportForm) {
        googleSheetsImportForm.onsubmit = function(e) {
            e.preventDefault();
            
            if (!window.selectedGoogleSheet) {
                window.GuestManager?.showNotification('Please select a Google Sheet to import.', 'error');
                return;
            }

            // Get selected group (if any) - only when groups are disabled in settings
            let groupId = '';
            const groupEnabled = window.guestListSettings?.fields?.group;
            if (!groupEnabled) {
                // Only use manual group assignment when groups are disabled in settings
                const groupSelect = document.getElementById('googleSheetsGroupSelect');
                if (groupSelect) {
                    groupId = groupSelect.value;
                }
            }
            // When groups are enabled, group assignment will be handled by the backend based on sheet column

            // Check if auto apply default language is checked
            let applyDefaultLang = false;
            const applyDefaultLangCheckbox = document.getElementById('googleSheetsApplyDefaultLanguage');
            if (applyDefaultLangCheckbox) {
                applyDefaultLang = applyDefaultLangCheckbox.checked;
            }

            // Prepare payload
            const payload = {
                sheet: {
                    file_id: window.selectedGoogleSheet.file_id,
                    file_name: window.selectedGoogleSheet.file_name,
                    sheet_title: window.selectedGoogleSheet.sheet_title
                },
                group_id: groupId || null,
                apply_default_language: applyDefaultLang
            };

            // Show loading state
            const importBtn = document.getElementById('importGoogleSheetBtn');
            const originalText = importBtn.textContent;
            importBtn.disabled = true;
            importBtn.textContent = 'Importing...';

            // Send to backend
            fetch(`/organizer/guest-lists/${window.guestListId}/import-google-sheet`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
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
                            throw new Error('Import failed. Please check your sheet format.');
                        } catch (parseError) {
                            throw new Error('Import failed. Please check your sheet format.');
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    hideModal('googleSheetsModal');
                    window.GuestManager?.showNotification(data.message || `Successfully imported ${data.imported} guests from Google Sheet`, 'success');
                    
                    // Refresh the guest list and validation errors
                    if (window.refreshValidationErrors) {
                        window.refreshValidationErrors();
                    }
                    
                    // Reload the page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    // Show detailed error message including duplicates
                    let errorMessage = data.message || 'Error importing from Google Sheet.';
                    
                    if (data.duplicates && data.duplicates.length > 0) {
                        errorMessage += '\n\nDuplicates found:\n' + data.duplicates.join('\n');
                    }
                    
                    if (data.errors && data.errors.length > 0) {
                        errorMessage += '\n\nOther errors:\n' + data.errors.join('\n');
                    }
                    
                    window.GuestManager?.showNotification(errorMessage, 'error');
                }
            })
            .catch((error) => {
                window.GuestManager?.showNotification(error.message || 'Error importing from Google Sheet.', 'error');
            })
            .finally(() => {
                // Restore button state
                importBtn.disabled = false;
                importBtn.textContent = originalText;
            });
        };
    }
});
