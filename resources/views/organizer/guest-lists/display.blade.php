@extends('layouts.organizer')

@section('title', $guestList->name)

@push('styles')
<style>
/* --- Main Content Responsive Styles --- */

/* Main content width rules */
.main-content-1149 {
  max-width: 773px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 2rem;
  padding-right: 2rem;
}
@media (min-width: 1024px) and (max-width: 1148.98px) {
  .main-content-1149 {
    max-width: 768px !important;
  }
}

/* --- Custom Styles --- */









@media (max-width: 768px) {
  .main-content-1149 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  /* Make tables responsive on mobile */
  #guestsTableContainer table {
    font-size: 14px;
  }
  
  #guestsTableContainer th,
  #guestsTableContainer td {
    padding: 8px 4px;
  }
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 main-content-1149">
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('organizer.guest-lists.index') }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 id="guestListName" class="text-3xl font-bold text-primary">{{ $guestList->name }}</h1>
                    <p id="guestListDescription" class="text-gray-600 mt-1">{{ $guestList->description }}</p>
                </div>
            </div>
            @if($guestList->event_date)
            <div class="mt-4 flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($guestList->event_date)->format('F j, Y') }}
                    @if($guestList->event_time)
                        at {{ \Carbon\Carbon::parse($guestList->event_time)->format('g:i A') }}
                    @endif
                </div>
                @if($guestList->event_location)
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $guestList->event_location }}
                </div>
                @endif
            </div>
            @endif
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('organizer.guest-lists.edit', $guestList) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit List
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--primary-100);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-600);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Total Guests</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $allGuests->count() }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-lg shadow-sm border p-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
            <div class="flex items-center">
                <div class="p-2 rounded-lg" style="background: var(--success-500);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #fff;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Groups Count</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $guestGroups->count() }}</p>
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
                    <p class="text-sm font-medium" style="color: var(--text-secondary);">Pending</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $allGuests->where('checked_in', false)->count() }}</p>
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
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">
                        @if($allGuests->count() > 0)
                            {{ round(($allGuests->where('checked_in', true)->count() / $allGuests->count()) * 100) }}%
                        @else
                            0%
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="rounded-lg shadow-sm border p-6 mb-6" style="background: var(--bg-primary); border-color: var(--border-primary);">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Search Guests</label>
                <input type="text" id="searchInput" placeholder="Search by name or email..." class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Status</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All Guests</option>
                    <option value="checked_in">Checked In</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            @if($guestList->settings['fields']['group'] ?? false)
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Group</label>
                <select id="groupFilter" class="form-select">
                    <option value="">All Groups</option>
                    @foreach($guestGroups as $group)
                        <option value="{{ $group->name }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
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

    <!-- Guests List -->
    <div id="guestsTableContainer">
        @if($allGuests->count() > 0)
            @if($guestList->settings['fields']['group'] ?? false)
                @php
                    // Group guests by group_id
                    $grouped = [];
                    $ungrouped = [];
                    
                    foreach($guestGroups as $group) {
                        $grouped[$group->id] = [];
                    }
                    
                    foreach($allGuests as $guest) {
                        if ($guest->group_id && isset($grouped[$guest->group_id])) {
                            $grouped[$guest->group_id][] = $guest;
                        } else {
                            $ungrouped[] = $guest;
                        }
                    }
                @endphp
                
                <!-- Render tables for each group -->
                @foreach($guestGroups as $group)
                    @php $groupGuests = $grouped[$group->id] ?? []; @endphp
                    <h2 class="text-xl font-bold mt-8 mb-2">{{ $group->name }}</h2>
                    <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                                @if(count($groupGuests) > 0)
                                    @foreach($groupGuests as $guest)
                                        <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-status="{{ $guest->checked_in ? 'checked_in' : 'pending' }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                            @if($guestList->settings['fields']['email'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                            @endif
                                            @if($guestList->settings['fields']['phone'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                            @endif
                                            @if($guestList->settings['fields']['language'] ?? false)
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="100%" class="px-6 py-4 text-center text-gray-400">No guests in this group.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endforeach
                
                <!-- Render table for ungrouped guests -->
                @if(count($ungrouped) > 0)
                    <h2 class="text-xl font-bold mt-8 mb-2">No Group</h2>
                    <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                                @foreach($ungrouped as $guest)
                                    <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-status="{{ $guest->checked_in ? 'checked_in' : 'pending' }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                        @if($guestList->settings['fields']['email'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                        @endif
                                        @if($guestList->settings['fields']['phone'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                        @endif
                                        @if($guestList->settings['fields']['language'] ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <!-- Groups not enabled - display all guests in a single flat table -->
                <div class="rounded-lg shadow-sm border overflow-hidden mb-8" style="background: var(--bg-primary); border-color: var(--border-primary);">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50" style="background: var(--bg-primary); border-color: var(--border-primary);">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                @if($guestList->settings['fields']['email'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                @endif
                                @if($guestList->settings['fields']['phone'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                                @endif
                                @if($guestList->settings['fields']['language'] ?? false)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preferred Language</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody style="background: var(--bg-primary); color: var(--text-primary);">
                            @foreach($allGuests as $guest)
                                <tr class="hover:bg-gray-100 transition" data-name="{{ strtolower($guest->name) }}" data-email="{{ strtolower($guest->email ?? '') }}" data-status="{{ $guest->checked_in ? 'checked_in' : 'pending' }}" data-group="{{ $guest->group ? $guest->group->name : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $guest->name }}</td>
                                    @if($guestList->settings['fields']['email'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->email ?? '' }}</td>
                                    @endif
                                    @if($guestList->settings['fields']['phone'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->phone ?? '' }}</td>
                                    @endif
                                    @if($guestList->settings['fields']['language'] ?? false)
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $guest->language ?? '' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="text-center py-16 px-6">
                <div class="mx-auto mb-6">
                    <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No guests found</h3>
                <p class="text-gray-500 mb-6">This guest list is empty. 
                    <a href="{{ route('organizer.guest-lists.edit', $guestList) }}" class="text-blue-600 hover:text-blue-800 font-medium underline">
                        Add some guests to get started
                    </a>.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/organizerJs/guest-list-table.js') }}"></script>
<script>
// Filter functionality
function filterGuests() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const group = document.getElementById('groupFilter').value;
    
    const guestRows = document.querySelectorAll('#guestsTableContainer tbody tr');
    
    guestRows.forEach(row => {
        const name = row.dataset.name;
        const email = row.dataset.email;
        const guestStatus = row.dataset.status;
        const guestGroup = row.dataset.group;
        
        const matchesSearch = name.includes(search) || email.includes(search);
        const matchesStatus = !status || guestStatus === status;
        const matchesGroup = !group || guestGroup === group;
        
        if (matchesSearch && matchesStatus && matchesGroup) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Hide empty tables
    const tables = document.querySelectorAll('#guestsTableContainer table');
    tables.forEach(table => {
        const visibleRows = table.querySelectorAll('tbody tr[style="display: table-row;"]');
        const tableWrapper = table.closest('.rounded-lg');
        const groupHeading = tableWrapper.previousElementSibling;
        
        if (visibleRows.length === 0) {
            tableWrapper.style.display = 'none';
            if (groupHeading && groupHeading.tagName === 'H2') {
                groupHeading.style.display = 'none';
            }
        } else {
            tableWrapper.style.display = 'block';
            if (groupHeading && groupHeading.tagName === 'H2') {
                groupHeading.style.display = 'block';
            }
        }
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('groupFilter').value = '';
    filterGuests();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const groupFilter = document.getElementById('groupFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterGuests, 300));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterGuests);
    }
    
    if (groupFilter) {
        groupFilter.addEventListener('change', filterGuests);
    }
});

// Debounce function
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