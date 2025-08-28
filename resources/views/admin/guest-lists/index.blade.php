@extends('layouts.admin')

@section('title', 'Guest Lists Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">Guest Lists Management</h1>
            <p class="text-gray-600 mt-2">Manage all guest lists across the platform</p>
        </div>
        <div class="flex space-x-3">
            <button class="btn-secondary" onclick="exportAllLists()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export All
            </button>
            <button class="btn-primary" onclick="showCreateModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create List
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Search lists..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Organizer</label>
                <select id="organizerFilter" class="form-select">
                    <option value="">All Organizers</option>
                    <!-- Will be populated via AJAX -->
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <input type="date" id="dateFilter" class="form-input">
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Lists</p>
                    <p class="text-2xl font-bold text-primary" id="totalLists">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Guests</p>
                    <p class="text-2xl font-bold text-primary" id="totalGuests">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Events</p>
                    <p class="text-2xl font-bold text-primary" id="activeEvents">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg. Check-ins</p>
                    <p class="text-2xl font-bold text-primary" id="avgCheckins">0%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Guest Lists Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="form-checkbox">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            List Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Organizer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Guests
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="guestListsTable">
                    <!-- Will be populated via AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button class="btn-secondary">Previous</button>
                    <button class="btn-secondary">Next</button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium" id="showingStart">1</span> to <span class="font-medium" id="showingEnd">10</span> of <span class="font-medium" id="totalResults">0</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="pagination">
                            <!-- Pagination links will be generated here -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Guest List Modal -->
<div id="createModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-primary">Create New Guest List</h3>
            <button type="button" class="modal-close" onclick="hideCreateModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="createGuestListForm" class="modal-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label">List Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Organizer</label>
                    <select name="organizer_id" class="form-select" required>
                        <option value="">Select Organizer</option>
                        <!-- Will be populated via AJAX -->
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3"></textarea>
                </div>
                <div>
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-input">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="hideCreateModal()">Cancel</button>
                <button type="submit" class="btn-primary">Create List</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-primary">Bulk Actions</h3>
            <button type="button" class="modal-close" onclick="hideBulkActionsModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p class="text-gray-600 mb-4">Select an action to perform on the selected guest lists:</p>
            <div class="space-y-3">
                <button class="w-full btn-secondary" onclick="bulkExport()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Selected
                </button>
                <button class="w-full btn-warning" onclick="bulkArchive()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Archive Selected
                </button>
                <button class="w-full btn-danger" onclick="bulkDelete()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let selectedLists = [];

// Load guest lists on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGuestLists();
    loadStats();
    loadOrganizers();
    setupEventListeners();
});

function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', debounce(function() {
        currentPage = 1;
        loadGuestLists();
    }, 300));

    // Filters
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    document.getElementById('organizerFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    document.getElementById('dateFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.list-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedLists.push(checkbox.value);
            } else {
                selectedLists = [];
            }
        });
        updateBulkActions();
    });
}

function loadGuestLists() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const organizer = document.getElementById('organizerFilter').value;
    const date = document.getElementById('dateFilter').value;

    fetch(`/admin/guest-lists?page=${currentPage}&search=${search}&status=${status}&organizer=${organizer}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            renderGuestLists(data.data);
            renderPagination(data);
            updateStats(data.stats);
        })
        .catch(error => {
            console.error('Error loading guest lists:', error);
            window.GuestManager.window.GuestManager.window.GuestManager.showNotification('Guest updated!', 'success');('Guest updated!', 'success');('Guest updated!', 'success');('Error loading guest lists', 'error');
        });
}

function renderGuestLists(lists) {
    const tbody = document.getElementById('guestListsTable');
    tbody.innerHTML = '';

    lists.forEach(list => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="list-checkbox form-checkbox" value="${list.id}" onchange="toggleListSelection(${list.id})">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-primary">${list.name}</div>
                        <div class="text-sm text-gray-500">${list.description || 'No description'}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-primary">${list.organizer.name}</div>
                <div class="text-sm text-gray-500">${list.organizer.email}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-primary">
                ${list.guests_count} guests
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="badge badge-${getStatusColor(list.status)}">${list.status}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formatDate(list.created_at)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900" onclick="viewList(${list.id})">View</button>
                    <button class="text-indigo-600 hover:text-indigo-900" onclick="editList(${list.id})">Edit</button>
                    <button class="text-red-600 hover:text-red-900" onclick="deleteList(${list.id})">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getStatusColor(status) {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'warning';
        case 'archived': return 'secondary';
        default: return 'secondary';
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function toggleListSelection(listId) {
    const index = selectedLists.indexOf(listId);
    if (index > -1) {
        selectedLists.splice(index, 1);
    } else {
        selectedLists.push(listId);
    }
    updateBulkActions();
}

function updateBulkActions() {
    const bulkButton = document.querySelector('[onclick="showBulkActionsModal()"]');
    if (bulkButton) {
        bulkButton.disabled = selectedLists.length === 0;
    }
}

function showCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function hideCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createGuestListForm').reset();
}

function showBulkActionsModal() {
    if (selectedLists.length === 0) return;
    document.getElementById('bulkActionsModal').classList.remove('hidden');
}

function hideBulkActionsModal() {
    document.getElementById('bulkActionsModal').classList.add('hidden');
}

// Form submission
document.getElementById('createGuestListForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/admin/guest-lists', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCreateModal();
            loadGuestLists();
            window.GuestManager.showNotification('Guest updated!', 'success');('Guest list created successfully', 'success');
        } else {
            window.GuestManager.showNotification('Guest updated!', 'success');(data.message || 'Error creating guest list', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.GuestManager.showNotification('Guest updated!', 'success');('Error creating guest list', 'error');
    });
});

// Utility functions
function loadStats() {
    fetch('/admin/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalLists').textContent = data.total_lists || 0;
            document.getElementById('totalGuests').textContent = data.total_guests || 0;
            document.getElementById('activeEvents').textContent = data.active_events || 0;
            document.getElementById('avgCheckins').textContent = (data.avg_checkins || 0) + '%';
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadOrganizers() {
    fetch('/admin/organizers')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('organizerFilter');
            const createSelect = document.querySelector('select[name="organizer_id"]');
            
            data.forEach(organizer => {
                const option = new Option(organizer.name, organizer.id);
                select.add(option.cloneNode(true));
                createSelect.add(option);
            });
        })
        .catch(error => console.error('Error loading organizers:', error));
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush 