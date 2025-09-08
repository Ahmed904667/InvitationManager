@extends('layouts.mobile')

@section('title', 'Scanner Profile - ' . $scanner->name)

@section('header-title', 'Scanner Profile')
@section('header-subtitle', $scanner->name)

@section('header-actions')
<button onclick="showProfilesModal()" class="btn-secondary text-sm px-3 py-1">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
    </svg>
    Switch
</button>
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
        <a href="{{ route('mobile.scanner.guests', $scanner->token) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <span>Guest List</span>
        </a>
        <a href="{{ route('mobile.scanner.profile', $scanner->token) }}" class="active">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Scanner Profile</span>
        </a>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 pb-24 max-w-4xl space-y-6">
    <!-- Enhanced Scanner Info Card -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $scanner->name }}</h2>
            <p class="text-primary-600 font-medium mb-4">{{ $stats['event_name'] }}</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-center">
                <div class="p-4 bg-gradient-to-br from-success-50 to-success-100 rounded-xl border border-success-200">
                    <div class="text-2xl font-bold text-success-700">{{ $stats['total_checkins'] }}</div>
                    <div class="text-sm text-success-600 font-medium">Check-ins</div>
                </div>
                <div class="p-4 bg-gradient-to-br from-info-50 to-info-100 rounded-xl border border-info-200">
                    <div class="text-sm font-medium text-info-700">Last Used</div>
                    <div class="text-sm text-info-600" id="lastUsedDisplay">{{ $stats['last_used'] ?: 'Never' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Recent Check-ins -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Check-ins
            </h3>
            <span class="text-sm text-primary-600 font-medium">{{ $recentCheckIns->count() }} of {{ $stats['total_checkins'] }}</span>
        </div>
        
        <div class="space-y-3" id="recentCheckIns">
            @forelse($recentCheckIns as $checkIn)
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-success-100 to-success-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $checkIn->name }}</h4>
                            <p class="text-sm text-gray-600" data-timestamp="{{ $checkIn->checked_in_at->toISOString() }}" data-format="local">
                                {{ $checkIn->checked_in_at->format('M j, g:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-sm text-primary-600 font-medium" data-timestamp="{{ $checkIn->checked_in_at->toISOString() }}" data-relative="true">
                        {{ $checkIn->checked_in_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">No check-ins yet</p>
                    <p class="text-gray-400 text-sm">Start scanning to see activity here</p>
                </div>
            @endforelse
        </div>
        
        @if($recentCheckIns->count() >= 10)
            <div class="text-center mt-4">
                <button onclick="loadMoreCheckIns()" class="btn-secondary text-sm px-6 py-2 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    Load More
                </button>
            </div>
        @endif
    </div>

    <!-- Enhanced Performance Analytics -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Performance Analytics
        </h3>
        
        <!-- Performance Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 bg-gradient-to-br from-info-50 to-info-100 rounded-xl border border-info-200">
                <div class="w-12 h-12 mx-auto mb-2 bg-info-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="text-lg font-bold text-info-700" id="totalCheckins">-</div>
                <div class="text-xs text-info-600 font-medium">Total</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-success-50 to-success-100 rounded-xl border border-success-200">
                <div class="w-12 h-12 mx-auto mb-2 bg-success-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-lg font-bold text-success-700" id="todayCheckins">-</div>
                <div class="text-xs text-success-600 font-medium">Last 12h</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200">
                <div class="w-12 h-12 mx-auto mb-2 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="text-lg font-bold text-purple-700" id="peakHour">-</div>
                <div class="text-xs text-purple-600 font-medium">Peak Hour</div>
            </div>
        </div>

        <!-- Performance Chart -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-700">Check-ins Performance</h4>
                <div class="flex space-x-2">
                    <button onclick="switchChartView('hourly')" id="hourlyBtn" class="text-xs px-3 py-2 rounded-lg bg-primary-100 text-primary-700 font-medium transition-all duration-300 hover:bg-primary-200">Last 12h</button>
                    <button onclick="switchChartView('daily')" id="dailyBtn" class="text-xs px-3 py-2 rounded-lg bg-gray-100 text-gray-600 font-medium transition-all duration-300 hover:bg-gray-200">Week</button>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="performanceChart" width="400" height="256"></canvas>
            </div>
        </div>
    </div>

    <!-- Enhanced Scanner Settings -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Scanner Settings
        </h3>
        
        <div class="space-y-6">
            <!-- Scanner Name -->
            <div>
                <label for="scannerName" class="block text-sm font-medium text-gray-700 mb-2">Scanner Name</label>
                <input type="text" id="scannerName" value="{{ $scanner->name }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
            </div>
            
            <!-- Timezone Display -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                    <div>
                        <span class="text-sm font-medium text-gray-900" id="timezoneDisplay">{{ $scanner->timezone ?: 'Detecting...' }}</span>
                        <p class="text-xs text-gray-500 mt-1">Automatically detected from your device</p>
                    </div>
                    <button onclick="detectTimezone()" class="text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors duration-200">
                        Refresh
                    </button>
                </div>
            </div>
            
            <!-- Toggle Settings -->
            <div class="space-y-4 lg:space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 lg:p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                    <div class="flex items-center space-x-3 mb-3 sm:mb-0">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-primary-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-base lg:text-lg">Sound Notifications</h4>
                            <p class="text-sm lg:text-base text-gray-600">Play sound on successful scan</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer self-start sm:self-center">
                        <input type="checkbox" class="sr-only toggle-input" id="soundToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 lg:p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                    <div class="flex items-center space-x-3 mb-3 sm:mb-0">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-success-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-base lg:text-lg">Vibration</h4>
                            <p class="text-sm lg:text-base text-gray-600">Vibrate on successful scan</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer self-start sm:self-center">
                        <input type="checkbox" class="sr-only toggle-input" id="vibrationToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 lg:p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                    <div class="flex items-center space-x-3 mb-3 sm:mb-0">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-info-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-base lg:text-lg">Auto Continue</h4>
                            <p class="text-sm lg:text-base text-gray-600">Continue scanning after check-in</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer self-start sm:self-center">
                        <input type="checkbox" class="sr-only toggle-input" id="autoContinueToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Save Settings Button -->
            <button onclick="saveSettings()" class="w-full btn-primary py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Settings
            </button>
        </div>
    </div>

    <!-- Enhanced Actions -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
            </svg>
            Actions
        </h3>
        
        <div class="space-y-4">
            <button onclick="shareScanner()" class="w-full btn-primary py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                </svg>
                Share Scanner URL
            </button>
            
            <button onclick="exportData()" class="w-full btn-secondary py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Check-in Data
            </button>
        </div>
    </div>
    </div>
</div>

<!-- Scanner Profiles Modal -->
<div id="profilesModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full max-h-96 overflow-y-auto">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Switch Scanner Profile</h3>
                    <button onclick="hideProfilesModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <div id="profilesList">
                    <div class="flex justify-center py-4">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Toggle Button Styles - Matching organizer page */
    .toggle-slider {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        background-color: #e5e7eb;
        border-radius: 12px;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }
    
    .toggle-slider::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background-color: white;
        border-radius: 50%;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .toggle-input:checked + .toggle-slider {
        background-color: var(--primary-500, #8b2bfa);
    }
    
    .toggle-input:checked + .toggle-slider::after {
        transform: translateX(20px);
    }
    
    /* Enhanced card hover effects */
    .card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

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
        .mobile-toolbar {
            display: none !important;
        }
        
        /* Adjust content padding for no bottom toolbar */
        .pb-24 {
            padding-bottom: 2rem !important;
        }
        
        /* Desktop content container */
        .container {
            max-width: 1000px !important;
            margin: 0 auto !important;
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }
        
        /* Enhanced card styling for desktop */
        .card {
            border-radius: 1.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            transition: all 0.3s ease !important;
        }
        
        .card:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Desktop header styling */
        .mobile-header {
            background: #8b2bfa !important;
            padding: 2rem !important;
            border-radius: 0 0 2rem 2rem !important;
            margin-bottom: 2rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
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
    
    /* Enhanced button styles */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(139, 43, 250, 0.3);
    }
    
    .btn-secondary {
        background: white;
        border: 1px solid var(--border-primary);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn-secondary:hover {
        background: var(--bg-secondary);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Enhanced input styles */
    input[type="text"]:focus {
        box-shadow: 0 0 0 3px rgba(139, 43, 250, 0.1);
        border-color: var(--primary-500);
    }
    
    /* Gradient backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-50), var(--primary-100));
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, var(--success-50), var(--success-100));
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, var(--info-50), var(--info-100));
    }
    
    .bg-gradient-purple {
        background: linear-gradient(135deg, var(--purple-50), var(--purple-100));
    }
</style>
@endpush

@section('toolbar')
<div class="mobile-toolbar">
    <a href="{{ route('mobile.scanner.scan', $scanner->token) }}" class="toolbar-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
        </svg>
        <span>Scan</span>
    </a>
    <a href="{{ route('mobile.scanner.guests', $scanner->token) }}" class="toolbar-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <span>Guests</span>
    </a>
    <a href="{{ route('mobile.scanner.profile', $scanner->token) }}" class="toolbar-btn active">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span>Profile</span>
    </a>
</div>
@endsection

@push('scripts')
<script>
const scannerToken = '{{ $scanner->token }}';
let currentOffset = {{ $recentCheckIns->count() }};
let performanceChart = null;
let currentChartView = 'hourly';

document.addEventListener('DOMContentLoaded', function() {
    // Setup setting toggles
    setupToggleEvents();
    
    // Auto-detect timezone on page load
    detectTimezone();
    
    // Convert timestamps to local timezone
    convertTimestampsToLocal();
    
    loadAnalytics();
    initializePerformanceChart();
});

function setupToggleEvents() {
    document.getElementById('soundToggle').addEventListener('change', function() {
        saveSetting('sound_notifications', this.checked);
    });
    
    document.getElementById('vibrationToggle').addEventListener('change', function() {
        saveSetting('vibration', this.checked);
    });
    
    document.getElementById('autoContinueToggle').addEventListener('change', function() {
        saveSetting('auto_continue', this.checked);
    });
}

function saveSetting(key, value) {
    fetch(`/scanner/${scannerToken}/settings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ [key]: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Settings saved', 'success');
        } else {
            showAlert('Error saving settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        showAlert('Error saving settings', 'error');
    });
}

function detectTimezone() {
    try {
        // Get timezone from browser
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        // Update display
        document.getElementById('timezoneDisplay').textContent = timezone;
        
        // Auto-save timezone to server
        saveTimezone(timezone);
        
        return timezone;
    } catch (error) {
        console.error('Error detecting timezone:', error);
        document.getElementById('timezoneDisplay').textContent = 'UTC (fallback)';
        return 'UTC';
    }
}

function convertTimestampsToLocal() {
    // Convert timestamps to local timezone and relative time
    const timestampElements = document.querySelectorAll('[data-timestamp]');
    
    timestampElements.forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        if (timestamp) {
            try {
                const date = new Date(timestamp);
                
                // Check if this is a relative time element (has data-relative attribute)
                if (element.hasAttribute('data-relative')) {
                    // Calculate relative time
                    const now = new Date();
                    const diffInSeconds = Math.floor((now - date) / 1000);
                    
                    let relativeTime;
                    if (diffInSeconds < 60) {
                        relativeTime = 'Just now';
                    } else if (diffInSeconds < 3600) {
                        const minutes = Math.floor(diffInSeconds / 60);
                        relativeTime = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
                    } else if (diffInSeconds < 86400) {
                        const hours = Math.floor(diffInSeconds / 3600);
                        relativeTime = `${hours} hour${hours > 1 ? 's' : ''} ago`;
                    } else {
                        const days = Math.floor(diffInSeconds / 86400);
                        relativeTime = `${days} day${days > 1 ? 's' : ''} ago`;
                    }
                    
                    element.textContent = relativeTime;
                } else if (element.hasAttribute('data-format') && element.getAttribute('data-format') === 'local') {
                    // Format as local time with proper timezone
                    const localTime = date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                        timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
                    });
                    
                    element.textContent = localTime;
                } else {
                    // Default local time formatting
                    const localTime = date.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    element.textContent = localTime;
                }
                
                // Remove the data attributes to avoid double conversion
                element.removeAttribute('data-timestamp');
                element.removeAttribute('data-relative');
                element.removeAttribute('data-format');
            } catch (error) {
                console.error('Error converting timestamp:', error);
            }
        }
    });
}

function saveTimezone(timezone) {
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
            console.log('Timezone updated to:', timezone);
            // Reload analytics to reflect timezone changes
            loadAnalytics();
            // Re-convert all timestamps with new timezone
            convertTimestampsToLocal();
        } else {
            console.error('Error saving timezone:', data.error);
        }
    })
    .catch(error => {
        console.error('Error saving timezone:', error);
    });
}

function saveSettings() {
    const name = document.getElementById('scannerName').value;
    const soundNotifications = document.getElementById('soundToggle').checked;
    const vibration = document.getElementById('vibrationToggle').checked;
    const autoContinue = document.getElementById('autoContinueToggle').checked;
    
    fetch(`/scanner/${scannerToken}/settings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({
            name: name,
            sound_notifications: soundNotifications,
            vibration: vibration,
            auto_continue: autoContinue
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Settings saved successfully!', 'success');
        } else {
            showAlert('Error saving settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        showAlert('Error saving settings', 'error');
    });
}

function loadMoreCheckIns() {
    fetch(`/scanner/${scannerToken}/checkins?offset=${currentOffset}`, {
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.checkins && data.checkins.length > 0) {
            const container = document.getElementById('recentCheckIns');
            
            data.checkins.forEach(checkIn => {
                const checkInElement = document.createElement('div');
                checkInElement.className = 'flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]';
                
                // Convert timestamp to local time
                const checkInDate = new Date(checkIn.checked_in_at);
                const localTime = checkInDate.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
                // Calculate relative time
                const now = new Date();
                const diffInSeconds = Math.floor((now - checkInDate) / 1000);
                let relativeTime;
                if (diffInSeconds < 60) {
                    relativeTime = 'Just now';
                } else if (diffInSeconds < 3600) {
                    const minutes = Math.floor(diffInSeconds / 60);
                    relativeTime = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
                } else if (diffInSeconds < 86400) {
                    const hours = Math.floor(diffInSeconds / 3600);
                    relativeTime = `${hours} hour${hours > 1 ? 's' : ''} ago`;
                } else {
                    const days = Math.floor(diffInSeconds / 86400);
                    relativeTime = `${days} day${days > 1 ? 's' : ''} ago`;
                }
                
                checkInElement.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-success-100 to-success-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">${checkIn.name}</h4>
                            <p class="text-sm text-gray-600">${localTime}</p>
                        </div>
                    </div>
                    <div class="text-sm text-primary-600 font-medium">
                        ${relativeTime}
                    </div>
                `;
                container.appendChild(checkInElement);
            });
            
            currentOffset += data.checkins.length;
            
            if (data.checkins.length < 10) {
                // Hide load more button if no more data
                const loadMoreBtn = container.nextElementSibling.querySelector('button');
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = 'none';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error loading more check-ins:', error);
        showAlert('Error loading more data', 'error');
    });
}

function shareScanner() {
    const url = window.location.href.replace('/profile', '');
    
    if (navigator.share) {
        navigator.share({
            title: `Scanner: {{ $scanner->name }}`,
            text: `Use this link to access the event scanner for {{ $stats['event_name'] }}`,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showAlert('Scanner URL copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Error copying URL:', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showAlert('Scanner URL copied to clipboard!', 'success');
        });
    }
}

function exportData() {
    // Create a CSV export of check-in data
    fetch(`/scanner/${scannerToken}/export`, {
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `scanner_checkins_{{ $scanner->name }}_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        showAlert('Check-in data exported successfully!', 'success');
    })
    .catch(error => {
        console.error('Error exporting data:', error);
        showAlert('Error exporting data', 'error');
    });
}

function showProfilesModal() {
    document.getElementById('profilesModal').classList.remove('hidden');
    loadProfiles();
}

function hideProfilesModal() {
    document.getElementById('profilesModal').classList.add('hidden');
}

function loadProfiles() {
    fetch(`/scanner/${scannerToken}/profiles`, {
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('profilesList');
        
        if (data.profiles && data.profiles.length > 0) {
            container.innerHTML = data.profiles.map(profile => `
                <div class="flex items-center justify-between p-3 border rounded-lg mb-2 ${profile.is_current ? 'bg-blue-50 border-blue-200' : ''}">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${profile.name}</div>
                        <div class="text-sm text-gray-500">${profile.check_ins} check-ins</div>
                    </div>
                    ${!profile.is_current ? `
                        <button onclick="switchProfile('${profile.token}')" class="btn-secondary text-sm px-3 py-1">
                            Switch
                        </button>
                    ` : `
                        <span class="text-sm text-blue-600 font-medium">Current</span>
                    `}
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <p class="text-sm">No other profiles available</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading profiles:', error);
    });
}

function switchProfile(token) {
    window.location.href = `/scanner/${token}`;
}

// Trigger vibration for demonstrations (if supported)
function triggerVibration() {
    if (navigator.vibrate && document.getElementById('vibrationToggle').checked) {
        navigator.vibrate(200);
    }
}

// Play notification sound (if enabled)
function playNotificationSound() {
    if (document.getElementById('soundToggle').checked) {
        // Create and play a simple beep sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    }
}

// Analytics Functions
function loadAnalytics() {
    // Load analytics data
    fetch(`/scanner/${scannerToken}/analytics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAnalyticsDisplay(data.analytics);
            }
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
        });
}

function updateAnalyticsDisplay(analytics) {
    // Update overview metrics
    document.getElementById('totalCheckins').textContent = analytics.total_checkins;
    document.getElementById('todayCheckins').textContent = analytics.today_checkins;
    document.getElementById('peakHour').textContent = analytics.peak_hour;
    
    // Update last used time with proper timezone conversion
    if (analytics.last_used) {
        try {
            const lastUsedDate = new Date(analytics.last_used);
            const localTime = lastUsedDate.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('lastUsedDisplay').textContent = localTime;
        } catch (error) {
            console.error('Error converting last used time:', error);
            document.getElementById('lastUsedDisplay').textContent = analytics.last_used;
        }
    } else {
        document.getElementById('lastUsedDisplay').textContent = 'Never';
    }
    
    // Update performance chart
    if (performanceChart) {
        updatePerformanceChart(analytics);
    }
}

function initializePerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Check-ins',
                data: [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    cornerRadius: 6,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        stepSize: 1,
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        maxTicksLimit: 6
                    }
                }
            }
        }
    });
}

function updatePerformanceChart(analytics) {
            if (currentChartView === 'hourly') {
            performanceChart.data.labels = analytics.hourly_labels;
            performanceChart.data.datasets[0].data = analytics.hourly_data;
            performanceChart.data.datasets[0].label = 'Check-ins (Last 12h)';
        } else {
            performanceChart.data.labels = analytics.daily_labels;
            performanceChart.data.datasets[0].data = analytics.daily_data;
            performanceChart.data.datasets[0].label = 'Check-ins This Week';
        }
    
    performanceChart.update('none');
}

function switchChartView(view) {
    currentChartView = view;
    
    // Update button styles
    document.getElementById('hourlyBtn').className = view === 'hourly' 
        ? 'text-xs px-2 py-1 rounded bg-blue-100 text-blue-700' 
        : 'text-xs px-2 py-1 rounded bg-gray-100 text-gray-600';
    
    document.getElementById('dailyBtn').className = view === 'daily' 
        ? 'text-xs px-2 py-1 rounded bg-blue-100 text-blue-700' 
        : 'text-xs px-2 py-1 rounded bg-gray-100 text-gray-600';
    
    // Reload analytics to get the correct view
    loadAnalytics();
}
</script>
@endpush
