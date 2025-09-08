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
            <button class="btn-primary" onclick="showCreateModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create List
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                <div class="p-2 rounded-lg" style="background: var(--green-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--green-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Ready to Go Lists</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);" id="readyToGoLists">0</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Search Lists</label>
                <input type="text" id="searchInput" placeholder="Search by name or description..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Health Status</label>
                <select id="healthFilter" class="form-select">
                    <option value="">All Health</option>
                    <option value="excellent">Excellent</option>
                    <option value="not valid">Not Valid</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Guest Count</label>
                <select id="guestCountFilter" class="form-select">
                    <option value="">All Lists</option>
                    <option value="empty">Empty (0 guests)</option>
                    <option value="small">Small (1-10 guests)</option>
                    <option value="medium">Medium (11-50 guests)</option>
                    <option value="large">Large (50+ guests)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Sort By</label>
                <select id="sortBy" class="form-select">
                    <option value="created_at_desc">Newest First</option>
                    <option value="created_at_asc">Oldest First</option>
                    <option value="updated_at_desc">Recently Updated</option>
                    <option value="updated_at_asc">Least Recently Updated</option>
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                    <option value="guests_count_desc">Most Guests</option>
                    <option value="guests_count_asc">Least Guests</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="btn-secondary w-full" onclick="clearFilters()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>
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
                        @if($list->health)
                            <span class="badge badge-{{ $list->health['color'] }}" title="{{ $list->health['message'] }}">
                                @if($list->health['status'] === 'not valid')
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                                {{ ucfirst($list->health['status']) }}
                            </span>
                        @endif
                    </div>
                    <div class="mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Guests</p>
                            <p class="text-lg font-bold text-primary">{{ $list->guests_count ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Created: {{ $list->created_at->format('M j, Y') }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Updated: {{ $list->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

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
    
    // Check if we should auto-show the create modal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('create_modal') === 'true') {
        setTimeout(() => {
            showCreateModal();
        }, 500); // Small delay to ensure page is fully loaded
    }
});

function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', debounce(function() {
        currentPage = 1;
        loadGuestLists();
    }, 300));

    // Health filter
    document.getElementById('healthFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    // Guest count filter
    document.getElementById('guestCountFilter').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });

    // Sort by
    document.getElementById('sortBy').addEventListener('change', function() {
        currentPage = 1;
        loadGuestLists();
    });


}

function loadGuestLists(append = false) {
    const search = document.getElementById('searchInput').value;
    const health = document.getElementById('healthFilter').value;
    const guestCount = document.getElementById('guestCountFilter').value;
    const sortBy = document.getElementById('sortBy').value;

    const params = new URLSearchParams({
        page: currentPage,
        search: search,
        health: health,
        guest_count: guestCount,
        sort_by: sortBy
    });

    fetch(`/organizer/guest-lists/json?${params.toString()}`)
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

    // Check if no lists found
    if (lists.length === 0 && !append) {
        const search = document.getElementById('searchInput').value;
        const health = document.getElementById('healthFilter').value;
        const guestCount = document.getElementById('guestCountFilter').value;
        
        let message = 'No guest lists found.';
        let suggestion = '';
        
        if (search || health || guestCount) {
            message = 'No guest lists match your search criteria.';
            suggestion = 'Try adjusting your filters or search terms.';
        }
        
        container.innerHTML = `
            <div class="col-span-full text-center py-16 px-6">
                <div class="mx-auto mb-6">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">${message}</h3>
                ${suggestion ? `<p class="text-gray-500 mb-6">${suggestion}</p>` : ''}
                <button class="btn-primary" onclick="clearFilters()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>
            </div>
        `;
        return;
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
                    ${list.health ? `
                        <span class="badge badge-${list.health.color}" title="${list.health.message}">
                            ${list.health.status === 'not valid' ? `
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            ` : `
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            `}
                            ${list.health.status.charAt(0).toUpperCase() + list.health.status.slice(1)}
                        </span>
                    ` : ''}
                </div>
                
                <div class="mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Guests</p>
                        <p class="text-lg font-bold text-primary">${list.guests_count}</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Created: ${formatDate(list.created_at)}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Last update ${formatTimeAgo(list.updated_at)}
                        </div>
                    </div>
                </div>
                
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





function loadMoreLists() {
    currentPage++;
    loadGuestLists(true);
}

function updateLoadMoreButton() {
    const container = document.getElementById('loadMoreContainer');
    container.style.display = hasMorePages ? 'block' : 'none';
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('healthFilter').value = '';
    document.getElementById('guestCountFilter').value = '';
    document.getElementById('sortBy').value = 'created_at_desc';
    currentPage = 1;
    loadGuestLists();
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
    // Import functionality not implemented in this view
    console.log('Import functionality not available');
    window.GuestManager.showNotification('Import functionality not available', 'info');
}

function hideImportModal() {
    // Import functionality not implemented in this view
    console.log('Import functionality not available');
}

function loadGuestListsForImport() {
    // Import functionality not implemented in this view
    console.log('Import functionality not available');
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

// Import form event listener removed - form not present in this view

// Date formatting function
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
}

// Time ago formatting function
function formatTimeAgo(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 2592000) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 31536000) {
        const months = Math.floor(diffInSeconds / 2592000);
        return `${months} month${months > 1 ? 's' : ''} ago`;
    } else {
        const years = Math.floor(diffInSeconds / 31536000);
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
}

// Utility functions
function loadStats() {
    // Load basic stats from the stats endpoint
    fetch('/organizer/stats')
        .then(response => response.json())
        .then(data => {
            // Use the correct data structure from getAccountStatistics()
            document.getElementById('totalLists').textContent = data.overview?.total_guest_lists || 0;
            document.getElementById('totalGuests').textContent = data.overview?.total_guests || 0;
        })
        .catch(error => console.error('Error loading stats:', error));
    
    // Load guest lists to count excellent health status lists
    fetch('/organizer/guest-lists/json?health=excellent')
        .then(response => response.json())
        .then(data => {
            document.getElementById('readyToGoLists').textContent = data.total || 0;
        })
        .catch(error => console.error('Error loading ready to go lists count:', error));
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