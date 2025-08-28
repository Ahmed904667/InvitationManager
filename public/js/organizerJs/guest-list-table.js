// Clean guest-list-table.js

window.renderGuestTable = function(settings, guests) {
    const tableContainer = document.getElementById('guestsTableContainer');
    if (!tableContainer) return;
    // Ensure action bar placeholder exists
    let actionBarPlaceholder = document.getElementById('guestActionBarPlaceholder');
    if (!actionBarPlaceholder) {
        actionBarPlaceholder = document.createElement('div');
        actionBarPlaceholder.id = 'guestActionBarPlaceholder';
        actionBarPlaceholder.style.height = '0px';
        tableContainer.parentNode.insertBefore(actionBarPlaceholder, tableContainer);
    }
    // Remove any existing action bar
    let oldBar = document.getElementById('guestActionBar');
    if (oldBar) oldBar.remove();
    tableContainer.innerHTML = '';
    
    // Check if groups are enabled
    if (settings.fields.group) {
        // Group guests by group_id
        const groups = window.guestListGroups || [];
        const grouped = {};
        groups.forEach(group => {
            grouped[group.id] = [];
        });
        const ungrouped = [];
        guests.forEach(guest => {
            if (guest.group_id && grouped[guest.group_id]) {
                grouped[guest.group_id].push(guest);
            } else {
                ungrouped.push(guest);
            }
        });
        
        // Render a table for each group (even if empty)
        groups.forEach(group => {
            const groupGuests = grouped[group.id];
            
            // Create group header container
            const groupHeader = document.createElement('div');
            groupHeader.className = 'flex items-center justify-between mt-8 mb-2';
            
            // Create group name heading
            const heading = document.createElement('h2');
            heading.className = 'text-xl font-bold';
            heading.textContent = group.name;
            groupHeader.appendChild(heading);
            
            // Create group actions container
            const groupActions = document.createElement('div');
            groupActions.className = 'flex items-center space-x-2';
            
            // Edit button
            const editBtn = document.createElement('button');
            editBtn.className = 'p-1 text-gray-500 hover:text-blue-600 transition-colors';
            editBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
            editBtn.title = 'Edit Group';
            editBtn.onclick = () => editGroup(group.id, group.name, group.description);
            groupActions.appendChild(editBtn);
            
            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'p-1 text-gray-500 hover:text-red-600 transition-colors';
            deleteBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
            deleteBtn.title = 'Delete Group';
            deleteBtn.onclick = () => deleteGroup(group.id, group.name);
            groupActions.appendChild(deleteBtn);
            
            groupHeader.appendChild(groupActions);
            tableContainer.appendChild(groupHeader);
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-lg shadow-sm border overflow-hidden mb-8';
            wrapper.style.background = 'var(--bg-primary)';
            wrapper.style.borderColor = 'var(--border-primary)';
            const table = document.createElement('table');
            table.className = 'min-w-full divide-y divide-gray-200';
            table.innerHTML = renderTableHtml(settings, groupGuests, false, true);
            wrapper.appendChild(table);
            tableContainer.appendChild(wrapper);
        });
        
        // Render a table for ungrouped guests
        if (ungrouped.length > 0) {
            const heading = document.createElement('h2');
            heading.className = 'text-xl font-bold mt-8 mb-2';
            heading.textContent = 'No Group';
            tableContainer.appendChild(heading);
            const wrapper = document.createElement('div');
            wrapper.className = 'rounded-lg shadow-sm border overflow-hidden mb-8';
            wrapper.style.background = 'var(--bg-primary)';
            wrapper.style.borderColor = 'var(--border-primary)';
            const table = document.createElement('table');
            table.className = 'min-w-full divide-y divide-gray-200';
            table.innerHTML = renderTableHtml(settings, ungrouped, false, true);
            wrapper.appendChild(table);
            tableContainer.appendChild(wrapper);
        }
    } else {
        // Groups not enabled - display all guests in a single flat table
        const wrapper = document.createElement('div');
        wrapper.className = 'rounded-lg shadow-sm border overflow-hidden mb-8';
        wrapper.style.background = 'var(--bg-primary)';
        wrapper.style.borderColor = 'var(--border-primary)';
        const table = document.createElement('table');
        table.className = 'min-w-full divide-y divide-gray-200';
        table.innerHTML = renderTableHtml(settings, guests, false, true);
        wrapper.appendChild(table);
        tableContainer.appendChild(wrapper);
    }
    // Attach event listeners to checkboxes to update action bar
    setTimeout(() => {
        document.querySelectorAll('.guest-checkbox').forEach(cb => {
            cb.removeEventListener('change', guestCheckboxChangeHandler);
            cb.addEventListener('change', guestCheckboxChangeHandler);
        });
    }, 0);
    // Render action bar if selection mode is active
    renderActionBar(settings);
};

function guestCheckboxChangeHandler() {
    renderActionBar(window.guestListSettings);
}

function renderActionBar(settings, fixed = null) {
    let bar = document.getElementById('guestActionBar');
    if (!window.showSelectionColumn) {
        if (bar) bar.remove();
        return;
    }
    // Get selected guest IDs
    const selected = Array.from(document.querySelectorAll('.guest-checkbox:checked')).map(cb => cb.value);
    // Determine stickiness if not explicitly passed
    if (fixed === null) {
        const placeholder = document.getElementById('guestActionBarPlaceholder');
        if (placeholder) {
            const rect = placeholder.getBoundingClientRect();
            fixed = rect.top <= 64;
        } else {
            fixed = false;
        }
    }
    // Create or update the action bar
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'guestActionBar';
        bar.className = 'z-40 flex items-center justify-between gap-6 px-6 py-3 bg-white shadow-lg rounded-lg';
        bar.style.background = 'var(--bg-primary)';
        // Insert after the search/filter card (before guestsTableContainer)
        const placeholder = document.getElementById('guestActionBarPlaceholder');
        if (placeholder) {
            placeholder.parentNode.insertBefore(bar, placeholder.nextSibling);
        }
    } else {
        bar.style.display = 'flex';
    }
    // Set fixed or static position and border
    if (fixed) {
        const container = document.querySelector('.main-content-1149');
        if (container) {
            const rect = container.getBoundingClientRect();
            bar.style.position = 'fixed';
            bar.style.top = '64px'; // navbar height
            bar.style.left = rect.left + 'px';
            bar.style.width = rect.width + 'px';
            bar.style.right = 'auto';
            bar.style.zIndex = 40;
            bar.style.border = '1px solid var(--border-primary)';
            document.getElementById('guestActionBarPlaceholder').style.height = bar.getBoundingClientRect().height + 'px';
        } else {
            bar.style.position = 'fixed';
            bar.style.top = '64px';
            bar.style.left = '0';
            bar.style.right = '0';
            bar.style.width = '100%';
            bar.style.border = '1px solid var(--border-primary)';
        }
    } else {
        bar.style.position = 'static';
        bar.style.left = '';
        bar.style.right = '';
        bar.style.width = '';
        bar.style.border = '1px solid var(--border-primary)';
        document.getElementById('guestActionBarPlaceholder').style.height = '0px';
    }
    // Build action bar content
    let html = `<div class="flex flex-1 items-center justify-start">
        <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-sm">
            Selected: <span id="selectedCount" class="ml-1">${selected.length}</span>
        </span>
    </div>`;
    html += `<div class="flex flex-1 items-center justify-center gap-3">`;
    html += `<button class="btn-danger inline-flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed min-w-[140px]" style="height:44px;width:140px;min-width:140px;" onclick="deleteSelectedGuests()" ${selected.length === 0 ? 'disabled' : ''}>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Delete Selected
    </button>`;
    if (settings.fields.group) {
        html += `<select id="changeGroupSelect" class="form-select mr-2 transition disabled:opacity-50" style="height:44px;min-width:140px;max-width:180px;" ${selected.length === 0 ? 'disabled' : ''}>
            <option value="">Change Group...</option>`;
        (window.guestListGroups || []).forEach(group => {
            html += `<option value="${group.id}">${group.name}</option>`;
        });
        html += `</select>`;
        html += `<button class="btn-primary inline-flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed min-w-[140px]" style="height:44px;width:140px;min-width:140px;" onclick="changeGroupForSelected()" ${selected.length === 0 ? 'disabled' : ''}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3m-7 4h10"/></svg>
            Change Group
        </button>`;
    }
    html += `</div>`;
    // Responsive arrangement: stack on mobile
    bar.innerHTML = `<div class="flex flex-row flex-wrap items-center justify-between w-full gap-4 sm:flex-nowrap">${html}</div>`;
}

// Sticky action bar on scroll
window.addEventListener('scroll', function() {
    const bar = document.getElementById('guestActionBar');
    const placeholder = document.getElementById('guestActionBarPlaceholder');
    if (!bar || !placeholder) return;
    const rect = placeholder.getBoundingClientRect();
    if (rect.top <= 64) {
        renderActionBar(window.guestListSettings, true);
    } else {
        renderActionBar(window.guestListSettings, false);
    }
});
// Also update bar width on window resize
window.addEventListener('resize', function() {
    const bar = document.getElementById('guestActionBar');
    if (!bar) return;
    if (bar.style.position === 'fixed') {
        renderActionBar(window.guestListSettings, true);
    }
});



function renderTableHtml(settings, guests, showGroupCol, showEmptyRow) {
    let theadHtml = '<thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">';
    theadHtml += '<tr>';
    if (window.showSelectionColumn) {
        theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">';
        theadHtml += '<input type="checkbox" id="selectAll" class="form-checkbox">';
        theadHtml += '</th>';
    }
    theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>';
    if (settings.fields.email) theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>';
    if (settings.fields.phone) theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>';
    // No group column
    if (settings.fields.language) theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>';
    theadHtml += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>';
    theadHtml += '</tr></thead>';

    let tbodyHtml = '<tbody style="background: var(--bg-primary); color: var(--text-primary);">';
    if (guests.length === 0 && showEmptyRow) {
        tbodyHtml += '<tr><td colspan="100%" class="px-6 py-4 text-center text-gray-400">No guests in this group.</td></tr>';
    } else {
    guests.forEach(guest => {
            tbodyHtml += '<tr class="hover:bg-gray-100 transition">';
            if (window.showSelectionColumn) {
                tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap"><input type="checkbox" class="guest-checkbox form-checkbox" value="${guest.id}"></td>`;
            }
            tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap font-semibold">${guest.name}</td>`;
            if (settings.fields.email) tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap">${guest.email || ''}</td>`;
            if (settings.fields.phone) tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap">${guest.phone || ''}</td>`;
            // No group column
            if (settings.fields.language) tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap">${guest.language || ''}</td>`;
            tbodyHtml += `<td class="px-6 py-4 whitespace-nowrap">
                <button class='text-blue-600 hover:text-blue-900 font-medium mr-2' onclick='editGuest(${guest.id})'>Edit</button>
                <button class='text-red-600 hover:text-red-900 font-medium' onclick="deleteGuest(${guest.id})">Delete</button>
            </td>`;
        tbodyHtml += '</tr>';
    });
    }
    tbodyHtml += '</tbody>';

    return theadHtml + tbodyHtml;
}

function renderGuestRow(guest, settings) {
    let row = `<tr>`;
    row += `<td><input type="checkbox" class="guest-checkbox form-checkbox" value="${guest.id}"></td>`;
    row += `<td>${guest.name}</td>`;
    if (settings.fields.email) row += `<td>${guest.email || ''}</td>`;
    if (settings.fields.phone) row += `<td>${guest.phone || ''}</td>`;
    if (settings.fields.group) row += `<td>${guest.group_name || ''}</td>`;
    if (settings.fields.language) row += `<td>${guest.language || 'nothing there'}</td>`;
    row += `<td><button class='text-blue-600 hover:text-blue-900' onclick='editGuest(${guest.id})'>Edit</button> <button class='text-red-600 hover:text-red-900' onclick='deleteGuest(${guest.id})'>Delete</button></td>`;
    row += `</tr>`;
    return row;
}

// Add global variable to control selection column visibility
window.showSelectionColumn = false;

window.toggleSelectionMode = function() {
    window.showSelectionColumn = !window.showSelectionColumn;
    if (window.renderGuestTable && window.guestListSettings && window.guestListGuests) {
        window.renderGuestTable(window.guestListSettings, window.guestListGuests);
    }
};

// Enable selectAll functionality
function enableSelectAllCheckbox() {
    document.querySelectorAll('#selectAll').forEach(selectAll => {
        selectAll.addEventListener('change', function() {
            const table = selectAll.closest('table');
            if (!table) return;
            const checkboxes = table.querySelectorAll('.guest-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            renderActionBar(window.guestListSettings);
        });
    });
}

// Call enableSelectAllCheckbox after rendering tables
const originalRenderGuestTable = window.renderGuestTable;
window.renderGuestTable = function(settings, guests) {
    originalRenderGuestTable(settings, guests);
    enableSelectAllCheckbox();
};

document.addEventListener('DOMContentLoaded', function() {
    if (window.guestListSettings && window.guestListGuests) {
        window.renderGuestTable(window.guestListSettings, window.guestListGuests);
    }
}); 

