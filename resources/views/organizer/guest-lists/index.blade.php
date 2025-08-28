@extends('layouts.organizer')


@section('title', 'My Guest Lists')

@section('content')


<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">My Guest Lists</h1>
            <p class="text-gray-600 mt-2">Manage your event guest lists</p>
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

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Lists</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalLists">0</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--green-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--green-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="totalGuests">0</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--yellow-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--yellow-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Upcoming Events</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="upcomingEvents">0</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--purple-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--purple-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Check-in Rate</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="checkinRate">0%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Search Lists</label>
                <input type="text" id="searchInput" placeholder="Search by name..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Event Date</label>
                <input type="date" id="dateFilter" class="form-input">
            </div>
        </div>
    </div>

    <div id="guestListsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(isset($guestLists))
            @forelse($guestLists as $list)
                <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-tertiary); border-color: var(--border-primary);">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-primary">{{ $list->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $list->description ?? 'No description' }}</p>
                            </div>
                        </div>
                        <span class="badge badge-{{ $list->status ?? 'secondary' }}">{{ $list->status ?? 'N/A' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Guests</p>
                            <p class="text-lg font-bold text-primary">{{ $list->guests_count ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Check-ins</p>
                            <p class="text-lg font-bold text-primary">{{ $list->checkins_count ?? 0 }}</p>
                        </div>
                    </div>
                    @if($list->event_date)
                    <div class="mb-4 p-3 bg-gray-50 rounded-md">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $list->event_date->format('D, M j, Y') }}
                        </div>
                        @if($list->event_location)
                        <div class="flex items-center text-sm text-gray-600 mt-1">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $list->event_location }}
                        </div>
                        @endif
                    </div>
                    @endif
                    <div class="flex space-x-2">
                        <a href="{{ route('organizer.guest-lists.display', $list) }}" class="flex-1 btn-primary inline-flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View
                        </a>
                        <button class="btn-danger" onclick="confirmDeleteList({{ $list->id }}, '{{ $list->name }}')" title="Delete List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-secondary py-8">No guest lists found.</div>
            @endforelse
        @endif
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-8" id="loadMoreContainer" style="display: none;">
        <button class="btn-secondary" onclick="loadMoreLists()">
            Load More Lists
        </button>
    </div>
</div>

<!-- Create Guest List Modal -->
<div id="createModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Create New Guest List</h3>
            <button type="button" class="modal-close" onclick="hideCreateModal()"></button>
        </div>
        <form id="createGuestListForm">
            <div class="modal-body">
                <div class="space-y-6">
                    <div>
                        <label class="form-label">List Name</label>
                        <input type="text" name="name" class="form-input" placeholder="Enter list name" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input"  placeholder="Describe your List"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideCreateModal()">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-primary">Create List</button>
            </div>
        </form>
    </div>
</div>



<!-- Reusable Confirmation Modal -->
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
@endsection

@push('scripts')
<script>
let currentPage = 1;
let hasMorePages = true;

// Load guest lists on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGuestLists();
    loadStats();
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

    document.getElementById('dateFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    // File upload preview
    document.getElementById('file-upload').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            const label = document.querySelector('label[for="file-upload"] span');
            label.textContent = fileName;
        }
    });
}

function loadGuestLists(append = false) {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;

    fetch(`/organizer/guest-lists/json?page=${currentPage}&search=${search}&status=${status}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (append) {
                renderGuestLists(data.data, true);
            } else {
                renderGuestLists(data.data, false);
            }
            hasMorePages = data.next_page_url !== null;
            updateLoadMoreButton();
        })
        .catch(error => {
            console.error('Error loading guest lists:', error);
            window.GuestManager.showNotification('Guest updated!', 'success');('Error loading guest lists', 'error');
        });
}

function renderGuestLists(lists, append = false) {
    const container = document.getElementById('guestListsGrid');
    
    if (!append) {
        container.innerHTML = '';
    }

    lists.forEach(list => {
        const card = document.createElement('div');
        card.className = 'rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-shadow duration-200';
        card.style.borderColor = 'var(--border-primary)';
        card.innerHTML = `
            <div class="p-6" style="background: var(--bg-primary);">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-primary">${list.name}</h3>
                            <p class="text-sm text-gray-500">${list.description || 'No description'}</p>
                        </div>
                    </div>
                    <span class="badge badge-${getStatusColor(list.status)}">${list.status}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Guests</p>
                        <p class="text-lg font-bold text-primary">${list.guests_count}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Check-ins</p>
                        <p class="text-lg font-bold text-primary">${list.checkins_count || 0}</p>
                    </div>
                </div>
                
                ${list.event_date ? `
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        ${formatDate(list.event_date)}
                    </div>
                    ${list.event_location ? `
                    <div class="flex items-center text-sm text-gray-600 mt-1">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        ${list.event_location}
                    </div>
                    ` : ''}
                </div>
                ` : ''}
                
                <div class="flex space-x-2">
                    <button class="flex-1 btn-primary" onclick="viewList(${list.id})">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </button>
                    <button class="btn-secondary" onclick="editList(${list.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button class="btn-secondary" onclick="showImportModal(${list.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </button>
                    <button class="btn-danger" onclick="confirmDeleteList(${list.id}, '${list.name}')" title="Delete List">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
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
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function loadMoreLists() {
    currentPage++;
    loadGuestLists(true);
}

function updateLoadMoreButton() {
    const container = document.getElementById('loadMoreContainer');
    container.style.display = hasMorePages ? 'block' : 'none';
}

function showCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
}

function hideCreateModal() {
    const modal = document.getElementById('createModal');
    modal.classList.remove('show');
    modal.classList.add('hidden');
    document.getElementById('createGuestListForm').reset();
}

function showImportModal(listId = null) {
    const modal = document.getElementById('importModal');
    const select = modal.querySelector('select[name="guest_list_id"]');
    
    if (listId) {
        select.value = listId;
        select.disabled = true;
    } else {
        select.disabled = false;
        loadGuestListsForImport();
    }
    
    modal.classList.remove('hidden');
}

function hideImportModal() {
    document.getElementById('importModal').classList.add('hidden');
    document.getElementById('importForm').reset();
    const select = document.querySelector('#importModal select[name="guest_list_id"]');
    select.disabled = false;
}

function loadGuestListsForImport() {
    fetch('/organizer/guest-lists?per_page=100')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('#importModal select[name="guest_list_id"]');
            select.innerHTML = '<option value="">Choose a list</option>';
            
            data.data.forEach(list => {
                const option = document.createElement('option');
                option.value = list.id;
                option.textContent = list.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading lists for import:', error));
}

function viewList(listId) {
    window.location.href = '/organizer/guest-lists/' + listId;
}

function editList(listId) {
    window.location.href = '/organizer/guest-lists/' + listId + '/edit';
}

// Form submissions
document.getElementById('createGuestListForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/organizer/guest-lists', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest' // <-- This is critical!
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideCreateModal();
            window.location.href = '/organizer/guest-lists/' + data.guest_list.id;
        } else {
            window.GuestManager.showNotification(data.message || 'Error creating guest list', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.GuestManager.showNotification('Error creating guest list', 'error');
    });
});

document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/organizer/guest-lists/import', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideImportModal();
            loadGuestLists();
            window.GuestManager.showNotification('Guest updated!', 'success');(`Successfully imported ${data.imported_count} guests`, 'success');
        } else {
            window.GuestManager.showNotification('Guest updated!', 'success');(data.message || 'Error importing guests', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.GuestManager.showNotification('Guest updated!', 'success');('Error importing guests', 'error');
    });
});

// Utility functions
function loadStats() {
    fetch('/organizer/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalLists').textContent = data.total_lists || 0;
            document.getElementById('totalGuests').textContent = data.total_guests || 0;
            document.getElementById('upcomingEvents').textContent = data.upcoming_events || 0;
            document.getElementById('checkinRate').textContent = (data.checkin_rate || 0) + '%';
        })
        .catch(error => console.error('Error loading stats:', error));
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

// Delete functionality
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
                loadGuestLists(); // Reload the lists
                loadStats(); // Reload stats
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

@endpush 