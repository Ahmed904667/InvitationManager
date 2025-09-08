@extends('layouts.mobile')

@section('title', 'Guest List - ' . $event->name)

@section('header-title', 'Guest List')
@section('header-subtitle', $scanner->name)

@section('header-actions')

@endsection

@section('content')
<div class="min-h-screen bg-primary-60">
    <!-- Desktop Navigation -->
    <div class="desktop-nav hidden md:flex">
        <a href="{{ route('mobile.scanner.scan', $scanner->token) }}">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="8" height="8" />
                                <path d="M6 6h.01" />
                                <rect x="14" y="2" width="8" height="8" />
                                <path d="M18 6h.01" />
                                <rect x="2" y="14" width="8" height="8" />
                                <path d="M6 18h.01" />
                                <path d="M14 14h.01" />
                                <path d="M18 18h.01" />
                                <path d="M18 22h4v-4" />
                                <path d="M14 18v4" />
                                <path d="M22 14h-4" />
                            </svg>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
            </svg>
            <span>Scan QR Codes</span>
        </a>
        <a href="{{ route('mobile.scanner.guests', $scanner->token) }}" class="active">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 715 0z"></path>
            </svg>
            <span>Guest List</span>
        </a>
        <a href="{{ route('mobile.scanner.profile', $scanner->token) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Scanner Profile</span>
        </a>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 pb-24 max-w-4xl">
        <!-- Search Bar -->
        <div class="card p-4 mb-6 bg-white/90 backdrop-blur-sm border-0 shadow-lg" id="searchContainer">
            <div class="relative">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Search guests by name, email, or phone..." 
                       class="w-full pl-12 pr-4 py-3 text-base rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300 bg-white"
                       value="{{ $search }}">
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Enhanced Stats Bar -->
        <div class="card p-6 mb-6 bg-white/90 backdrop-blur-sm border-0 shadow-lg">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-blue-600">{{ $groupedGuests->flatten()->count() }}</div>
                    <div class="text-sm text-gray-600 font-medium">Total Guests</div>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-green-600">{{ $groupedGuests->flatten()->where('checked_in', true)->count() }}</div>
                    <div class="text-sm text-gray-600 font-medium">Checked In</div>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-3 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-orange-600">{{ $groupedGuests->flatten()->where('checked_in', false)->count() }}</div>
                    <div class="text-sm text-gray-600 font-medium">Pending</div>
                </div>
            </div>
        </div>

        <!-- Guest List -->
        <div class="space-y-4" id="guestListContainer">
            @if($groupedGuests->isEmpty())
                <div class="card p-12 text-center bg-white/90 backdrop-blur-sm border-0 shadow-lg">
                    <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No guests found</h3>
                    <p class="text-gray-500">Try adjusting your search criteria</p>
                </div>
            @else
                @foreach($groupedGuests as $groupName => $guests)
                    @if($showGroups && count($groupedGuests) > 1)
                        <div class="card p-4 bg-gradient-to-r from-primary-50 to-primary-100 border-0 shadow-lg">
                            <h3 class="font-semibold text-primary-800 flex items-center justify-between">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    {{ $groupName }}
                                </span>
                                <span class="text-sm font-normal text-primary-600 bg-primary-200 px-2 py-1 rounded-full">{{ $guests->count() }} guests</span>
                            </h3>
                        </div>
                    @endif
                    
                    <div class="space-y-3">
                        @foreach($guests as $guest)
                            <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-[1.02]" 
                                 data-guest-id="{{ $guest->id }}"
                                 onclick="showGuestModal({{ $guest->id }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center mb-3">
                                            <div class="w-12 h-12 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mr-4">
                                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                                    {{ $guest->name }}
                                                    @if($guest->checked_in)
                                                        <svg class="w-5 h-5 ml-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    @endif
                                                </h4>
                                                @if($guest->guestGroup)
                                                    <p class="text-sm text-primary-600 font-medium">{{ $guest->guestGroup->name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 ml-16">
                                            @php
                                                $contacts = $guest->getContactInfo();
                                            @endphp
                                            @if(isset($contacts['email']))
                                                <div class="text-sm text-gray-600 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="truncate">{{ $contacts['email'] }}</span>
                                                </div>
                                            @endif
                                            
                                            @if(isset($contacts['phone']))
                                                <div class="text-sm text-gray-600 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                    <span>{{ $contacts['phone'] }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($guest->checked_in)
                                                <div class="text-sm text-green-600 flex items-center mt-2">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Checked in {{ $guest->checked_in_at->diffForHumans() }}
                                                    @if($guest->scanner_name)
                                                        by {{ $guest->scanner_name }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex-shrink-0">
                                        @if($guest->checked_in)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Checked In
                                            </span>
                                        @else
                                            <button onclick="event.stopPropagation(); quickCheckIn({{ $guest->id }})" 
                                                    class="btn-primary px-4 py-2 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Check In
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Enhanced Guest Detail Modal -->
<div id="guestModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Guest Information</h3>
                    <button onclick="closeGuestModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6" id="guestDetails">
                <!-- Guest details will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Processing Modal -->
<div id="processingModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="relative">
                <div class="animate-spin rounded-full h-16 w-16 border-4 border-primary-100 border-t-primary-500 mx-auto mb-6"></div>
                <div class="absolute inset-0 rounded-full h-16 w-16 border-4 border-transparent border-t-primary-300 animate-ping"></div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Processing...</h3>
            <p class="text-gray-600" id="processingMessage">Checking in guest</p>
        </div>
    </div>
</div>
@endsection

@section('toolbar')
<!-- Mobile Navigation (Hidden on Desktop) -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 md:hidden">
    <div class="flex justify-around max-w-md mx-auto">
    <a href="{{ route('mobile.scanner.scan', $scanner->token) }}" class="toolbar-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
        </svg>
        <span>Scan</span>
    </a>
    <a href="{{ route('mobile.scanner.guests', $scanner->token) }}" class="toolbar-btn active">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <span>Guests</span>
    </a>
    <a href="{{ route('mobile.scanner.profile', $scanner->token) }}" class="toolbar-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span>Profile</span>
    </a>
</div>
@endsection

@push('styles')
<style>
    /* Desktop Layout Optimization */
    @media (min-width: 768px) {
        /* Override mobile container for desktop centering */
        .mobile-container {
            max-width: none !important;
            margin: 0 auto !important;
            display: block !important;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .mobile-content {
            width: 100% !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
            padding: 2rem !important;
            background: transparent;
        }
        

        
        /* Hide mobile toolbar on desktop */
        .fixed.bottom-0 {
            display: none !important;
        }
        
        /* Desktop header styling - match scanner page */
        .mobile-header {
            background: #8b2bfa !important;
            padding: 2rem !important;
            border-radius: 0 0 2rem 2rem !important;
            margin-bottom: 2rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Desktop content container */
        .container {
            max-width: 1000px !important;
            margin: 0 auto !important;
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }
        
        /* Desktop navigation */
        .desktop-nav {
            display: flex !important;
            justify-content: center !important;
            margin-bottom: 2rem !important;
            background: white !important;
            border-radius: 1.5rem !important;
            padding: 1rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        .desktop-nav a {
            display: flex !important;
            align-items: center !important;
            padding: 1rem 2rem !important;
            margin: 0 0.5rem !important;
            border-radius: 1rem !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            font-weight: 500 !important;
        }
        
        .desktop-nav a.active {
            background: #8b2bfa !important;
            color: white !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        
        .desktop-nav a:not(.active):hover {
            background: #f1f5f9 !important;
            color: #8b2bfa !important;
        }
        
        .desktop-nav svg {
            margin-right: 0.5rem !important;
        }
    }
    
    /* Enhanced card styling */
    .card {
        border-radius: 1rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        transition: all 0.3s ease !important;
    }
    
    .card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Enhanced button styling */
    .btn-primary {
        background: linear-gradient(135deg, #8b2bfa 0%, #7c3aed 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 25px rgba(139, 43, 250, 0.3) !important;
    }
    
    .btn-secondary {
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }
    
    .btn-secondary:hover {
        background: #e2e8f0 !important;
        transform: translateY(-1px) !important;
    }
    
    /* Modal animations */
    .modal-enter {
        animation: modalEnter 0.3s ease-out;
    }
    
    .modal-exit {
        animation: modalExit 0.3s ease-in;
    }
    
    @keyframes modalEnter {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes modalExit {
        from {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        to {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
    }
    
    /* Hide scrollbar for guest list */
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    

    
    /* Large desktop improvements */
    @media (min-width: 1024px) {
        .mobile-content {
            max-width: 1400px !important;
            padding: 3rem !important;
        }
        
        .container {
            max-width: 1200px !important;
            padding-left: 3rem !important;
            padding-right: 3rem !important;
        }
        

    }
</style>
@endpush

@push('scripts')
<script>
const scannerToken = '{{ $scanner->token }}';
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    // Auto-detect and save timezone
    detectAndSaveTimezone();
    
    // Setup search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });
    
    // Setup keyboard shortcut for search
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    });
});

function detectAndSaveTimezone() {
    try {
        // Get timezone from browser
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Auto-save timezone to server
        fetch(`/scanner/${scannerToken}/detect-timezone`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                timezone: timezone
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Timezone auto-detected and saved:', timezone);
            } else {
                console.error('Error saving timezone:', data.error);
            }
        })
        .catch(error => {
            console.error('Error saving timezone:', error);
        });
        
        return timezone;
    } catch (error) {
        console.error('Error detecting timezone:', error);
        return 'UTC';
    }
}



function performSearch() {
    const searchTerm = document.getElementById('searchInput').value;
    const url = new URL(window.location);
    
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    
    window.location.href = url.toString();
}

function showGuestModal(guestId) {
    // Find guest data from the page
    const guestItem = document.querySelector(`[data-guest-id="${guestId}"]`);
    if (!guestItem) return;
    
    // For now, we'll build modal from DOM data
    // In a real implementation, you might fetch from API
    const modal = document.getElementById('guestModal');
    const details = document.getElementById('guestDetails');
    
    // Extract guest info from DOM
    const guestName = guestItem.querySelector('h4').textContent;
    const isCheckedIn = guestItem.querySelector('.text-green-600');
    
    details.innerHTML = `
        <div class="text-center mb-4">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">${guestName}</h3>
        </div>
        
        <div class="border-t border-b py-4 my-4">
            <div class="text-center">
                ${isCheckedIn ? `
                    <div class="text-green-600">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-semibold">Checked In</p>
                    </div>
                ` : `
                    <div class="text-blue-600">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <p class="font-semibold">Ready to Check In</p>
                    </div>
                `}
            </div>
        </div>
        
        <div class="flex space-x-3">
            ${!isCheckedIn ? `
                <button onclick="checkInGuestFromModal(${guestId})" class="btn-success flex-1">
                    Check In
                </button>
            ` : ''}
            <button onclick="closeGuestModal()" class="btn-secondary ${isCheckedIn ? 'flex-1' : ''}">
                Close
            </button>
        </div>
    `;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('modalContent').classList.remove('scale-95', 'opacity-0');
        document.getElementById('modalContent').classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeGuestModal() {
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        document.getElementById('guestModal').classList.add('hidden');
    }, 300);
}

function quickCheckIn(guestId) {
    showProcessingModal('Checking in guest...');
    checkInGuest(guestId);
}

function checkInGuestFromModal(guestId) {
    closeGuestModal();
    showProcessingModal('Checking in guest...');
    checkInGuest(guestId);
}

function checkInGuest(guestId) {
    fetch(`/scanner/${scannerToken}/checkin`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ 
            guest_id: guestId,
            notes: 'Manual check-in from guest list'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideProcessingModal();
        
        if (data.success) {
            showAlert('Guest checked in successfully!', 'success');
            updateGuestInList(guestId, true);
        } else {
            showAlert(data.message || 'Error checking in guest', 'error');
        }
    })
    .catch(error => {
        hideProcessingModal();
        console.error('Error checking in guest:', error);
        showAlert('Error checking in guest', 'error');
    });
}

function updateGuestInList(guestId, checkedIn) {
    const guestItem = document.querySelector(`[data-guest-id="${guestId}"]`);
    if (!guestItem) return;
    
    if (checkedIn) {
        // Add check mark to name
        const nameElement = guestItem.querySelector('h4');
        if (!nameElement.querySelector('svg')) {
            const checkIcon = document.createElement('svg');
            checkIcon.className = 'w-5 h-5 ml-2 text-green-600';
            checkIcon.setAttribute('fill', 'none');
            checkIcon.setAttribute('stroke', 'currentColor');
            checkIcon.setAttribute('viewBox', '0 0 24 24');
            checkIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            nameElement.appendChild(checkIcon);
        }
        
        // Update button area - replace the check-in button with checked-in status
        const buttonArea = guestItem.querySelector('.ml-4');
        if (buttonArea) {
            buttonArea.innerHTML = `
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Checked In
                </span>
            `;
        }
        
        // Add check-in time info
        const contactsArea = guestItem.querySelector('.space-y-2');
        if (contactsArea) {
            const timeInfo = document.createElement('div');
            timeInfo.className = 'text-sm text-green-600 flex items-center mt-2';
            timeInfo.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Checked in just now
            `;
            contactsArea.appendChild(timeInfo);
        }
        
        // Update the stats counters
        updateStatsCounters();
    }
}

function updateStatsCounters() {
    // Update the stats counters in the stats bar
    const totalGuests = document.querySelectorAll('[data-guest-id]').length;
    const checkedInGuests = document.querySelectorAll('[data-guest-id] .bg-green-100').length; // Count checked-in badges
    const pendingGuests = totalGuests - checkedInGuests;
    
    // Update the counters in the stats bar
    const totalElement = document.querySelector('.text-2xl.font-bold.text-blue-600');
    const checkedInElement = document.querySelector('.text-2xl.font-bold.text-green-600');
    const pendingElement = document.querySelector('.text-2xl.font-bold.text-orange-600');
    
    if (totalElement) totalElement.textContent = totalGuests;
    if (checkedInElement) checkedInElement.textContent = checkedInGuests;
    if (pendingElement) pendingElement.textContent = pendingGuests;
}

function showProcessingModal(message) {
    document.getElementById('processingMessage').textContent = message;
    document.getElementById('processingModal').classList.remove('hidden');
}

function hideProcessingModal() {
    document.getElementById('processingModal').classList.add('hidden');
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && document.activeElement.classList.contains('guest-item')) {
        document.activeElement.click();
    }
});
</script>
@endpush
