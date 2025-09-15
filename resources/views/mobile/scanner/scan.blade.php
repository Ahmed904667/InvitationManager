@extends('layouts.mobile')

@section('title', 'QR Scanner - ' . $event->name)

@section('header-title', 'QR Scanner')
@section('header-subtitle', $scanner->name)

@section('header-actions')
<button onclick="toggleFlashlight()" id="flashButton" class="btn-secondary text-sm px-4 py-2 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
    </svg>
    Flash
</button>
@endsection

@section('content')
<div class="min-h-screen bg-primary-60">
    <!-- Desktop Navigation -->
    <div class="desktop-nav hidden md:flex">
        <a href="{{ route('mobile.scanner.scan', $scanner->token) }}" class="active">
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
            <span>Scan QR Codes</span>
        </a>
        <a href="{{ route('mobile.scanner.guests', $scanner->token) }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
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
    <!-- Enhanced Scanner Status -->
    <div class="card p-6 mb-6 bg-white/80 backdrop-blur-sm border-0 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-4 h-4 rounded-full mr-3 animate-pulse" id="statusDot"></div>
                    <div class="absolute inset-0 w-4 h-4 rounded-full bg-current opacity-20 animate-ping" id="statusPing"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-900" id="scannerStatus">Initializing...</span>
                    <p class="text-xs text-gray-500">Ready to scan QR codes</p>
                </div>
            </div>
            <button onclick="toggleScanner()" id="toggleButton" class="btn-primary px-6 py-2 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <span id="toggleText">Start</span>
            </button>
        </div>
    </div>

    <!-- Enhanced Camera Container -->
    <div class="card p-6 mb-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <div class="scanner-container relative rounded-lg overflow-hidden shadow-lg mx-auto" id="scanner-container" style="width: 500px; height: 500px; max-width: 95vw; max-height: 95vw;">
            <div id="qr-reader" style="width: 100%; height: 100%;"></div>
            <div class="scanner-overlay absolute inset-0 bg-black/50" id="scanner-overlay">
                <div class="scanner-target flex items-center justify-center h-full">
                    
                    <div class="text-center text-white p-8" id="scanner-instructions">
                        <div class="w-16 h-16 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
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
                        </div>
                        <h3 class="text-lg font-semibold mb-2">QR Code Scanner</h3>
                        <p class="text-sm opacity-90">Position QR code within the frame</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 md:grid-cols-3 md:gap-8">
        <div class="card p-4 text-center bg-gradient-to-br from-info-50 to-info-100 border-0 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="w-12 h-12 mx-auto mb-2 bg-info-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="text-xl font-bold text-info-700" id="totalGuests">-</div>
            <div class="text-xs text-info-600 font-medium">Total Guests</div>
        </div>
        <div class="card p-4 text-center bg-gradient-to-br from-success-50 to-success-100 border-0 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="w-12 h-12 mx-auto mb-2 bg-success-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="text-xl font-bold text-success-700" id="checkedInGuests">-</div>
            <div class="text-xs text-success-600 font-medium">Checked In</div>
        </div>
        <div class="card p-4 text-center bg-gradient-to-br from-purple-50 to-purple-100 border-0 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="w-12 h-12 mx-auto mb-2 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="text-xl font-bold text-purple-700" id="myScans">-</div>
            <div class="text-xs text-purple-600 font-medium">My Scans</div>
        </div>
    </div>

    <!-- Enhanced Recent Scans -->
    <div class="card p-6 bg-white/90 backdrop-blur-sm border-0 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Scans
            </h3>
            <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
        </div>
        <div id="recentScans">
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">No scans yet</p>
                <p class="text-gray-400 text-sm">Start scanning to see activity here</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Guest Info Modal -->
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
            <div class="p-6" id="guestInfo">
                <!-- Guest info will be populated here -->
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
            <p class="text-gray-600" id="processingMessage">Scanning QR code</p>
        </div>
    </div>
</div>

<!-- Enhanced Camera Permission Modal -->
<div id="cameraPermissionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <div class="text-center">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Camera Access Required</h3>
                <p class="text-gray-600 mb-6">To scan QR codes, please allow camera access in your browser.</p>
                
                <div class="space-y-4">
                    <div class="text-left text-sm text-gray-600 bg-gray-50 p-4 rounded-xl">
                        <p class="font-medium mb-3 text-gray-900">How to enable camera access:</p>
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Look for a camera icon in your browser's address bar</li>
                            <li>Click on it and select "Allow"</li>
                            <li>Refresh this page and try again</li>
                        </ol>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button onclick="closeCameraPermissionModal()" class="flex-1 btn-secondary py-3 rounded-xl">
                            Cancel
                        </button>
                        <button onclick="retryCamera()" class="flex-1 btn-primary py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@section('toolbar')
<!-- Mobile Navigation (Hidden on Desktop) -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 md:hidden">
    <div class="flex justify-around max-w-md mx-auto">
        <a href="{{ route('mobile.scanner.scan', $scanner->token) }}" class="flex flex-col items-center py-3 px-4 text-primary-600 border-b-2 border-primary-600">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
            </svg>
            <span class="text-sm font-medium">Scan</span>
        </a>
        <a href="{{ route('mobile.scanner.guests', $scanner->token) }}" class="flex flex-col items-center py-3 px-4 text-gray-600 hover:text-primary-600 transition-colors">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <span class="text-sm font-medium">Guests</span>
        </a>
        <a href="{{ route('mobile.scanner.profile', $scanner->token) }}" class="flex flex-col items-center py-3 px-4 text-gray-600 hover:text-primary-600 transition-colors">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-sm font-medium">Profile</span>
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Simple scanner container */
    .scanner-container {
        width: 100%;
        height: 100%;
        max-width: 95vw;
        max-height: 95vw;
        position: relative;
        background: #000;
        border-radius: 0.5rem;
        overflow: hidden;
        margin: 0 auto;
        aspect-ratio: 1;
        border: 2px solid #e5e7eb;
    }
    
    @media (min-width: 640px) {
        .scanner-container {
            width: 600px;
            height: 600px;
        }
    }
    
    @media (min-width: 768px) {
        .scanner-container {
            width: 700px;
            height: 700px;
        }
    }
    
    @media (min-width: 1024px) {
        .scanner-container {
            width: 800px;
            height: 800px;
        }
    }
    
    .scanner-overlay {
        background: rgba(0,0,0,0.5);
    }
    
    /* Make camera video fill the container */
    #qr-reader {
        width: 100% !important;
        height: 100% !important;
    }
    
    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 0.5rem;
    }
    
    #qr-reader canvas {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 0.5rem;
    }
    
    .scanner-target {
        position: relative;
    }
    
    /* Simple corner indicators */
    .qr-corner {
        position: absolute;
        width: 40px;
        height: 40px;
        border: 3px solid #fff;
    }
    
    .qr-corner.top-left {
        top: 50%;
        left: 50%;
        transform: translate(-150px, -150px);
        border-right: none;
        border-bottom: none;
    }
    
    .qr-corner.top-right {
        top: 50%;
        left: 50%;
        transform: translate(110px, -150px);
        border-left: none;
        border-bottom: none;
    }
    
    .qr-corner.bottom-left {
        top: 50%;
        left: 50%;
        transform: translate(-150px, 110px);
        border-right: none;
        border-top: none;
    }
    
    .qr-corner.bottom-right {
        top: 50%;
        left: 50%;
        transform: translate(110px, 110px);
        border-left: none;
        border-top: none;
    }
    
    /* Enhanced button hover effects */
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(139, 43, 250, 0.3);
    }
    
    .btn-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Card hover effects */
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    
    /* Status dot animations */
    #statusDot {
        animation: pulse 2s infinite;
    }
    
    #statusPing {
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
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
    
    /* Enhanced gradient backgrounds */
    .bg-gradient-scanner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }
    
    /* Toolbar and content spacing */
    .pb-24 {
        padding-bottom: 6rem; /* 96px - ensures content doesn't get hidden behind toolbar */
    }
    
    /* Fixed toolbar styles */
    .fixed.bottom-0 {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 40;
    }
    
    /* Center content layout */
    .container {
        margin-left: auto !important;
        margin-right: auto !important;
        width: 100%;
    }
    
    .max-w-4xl {
        max-width: 56rem !important; /* 896px */
    }
    
    /* Force centering on all screen sizes */
    @media (min-width: 640px) {
        .container.mx-auto {
            margin-left: auto !important;
            margin-right: auto !important;
        }
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
        .fixed.bottom-0 {
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
        
        .desktop-nav {
            max-width: 1200px !important;
            margin: 0 auto 3rem auto !important;
        }
        
        .desktop-nav a {
            padding: 1.25rem 2.5rem !important;
            font-size: 1.1rem !important;
        }
        
        /* Enhanced scanner container for large screens */
        .scanner-container {
            border-radius: 2rem !important;
        }
        
        /* Enhanced stats cards */
        .card {
            padding: 2rem !important;
        }
        
        /* Larger stat cards on desktop */
        .grid.grid-cols-1.sm\\:grid-cols-3 .card {
            padding: 2rem 1.5rem !important;
        }
        
        .grid.grid-cols-1.sm\\:grid-cols-3 .card .w-12 {
            width: 4rem !important;
            height: 4rem !important;
        }
        
        .grid.grid-cols-1.sm\\:grid-cols-3 .card .text-xl {
            font-size: 2rem !important;
        }
    }
    
    /* Extra large desktop improvements */
    @media (min-width: 1280px) {
        .mobile-content {
            max-width: 1600px !important;
        }
        
        .container {
            max-width: 1400px !important;
        }
        
        .desktop-nav {
            max-width: 1400px !important;
        }
    }
    

</style>
@endpush

@push('scripts')
<script>
const scannerToken = '{{ $scanner->token }}';
const scannerTimezone = '{{ $organizerTimezone }}';
let qrScanner = null;
let isScanning = false;
let flashlightOn = false;
let recentScans = [];

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
            // Timezone auto-detected and saved
        })
        .catch(error => {
            // Silently handle timezone detection errors
        });
        
        return timezone;
    } catch (error) {
        return 'UTC';
    }
}

function formatTimeToLocal(timestamp) {
    try {
        // Parse the timestamp (assuming it's in Y-m-d H:i:s format from server)
        const date = new Date(timestamp);
        
        // Format to scanner's timezone instead of browser's local timezone
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            timeZone: scannerTimezone
        });
    } catch (error) {
        return timestamp; // Fallback to original timestamp
    }
}

function calculateRelativeTime(timestamp) {
    try {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }
    } catch (error) {
        return 'Unknown';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Check for HTTPS requirement
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        updateStatus('error', 'HTTPS required for camera');
        showAlert('Camera access requires HTTPS. Please use a secure connection.', 'error');
        return;
    }
    
    // Auto-detect and save timezone
    detectAndSaveTimezone();
    
    loadStats();
    initializeScanner();
    
    // Refresh stats periodically
    setInterval(loadStats, 30000);
});

function initializeScanner() {
    updateStatus('initializing', 'Initializing scanner...');
    
    try {
        // Initialize QR scanner with better configuration
        qrScanner = new Html5QrcodeScanner("qr-reader", {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            showTorchButtonIfSupported: false, // Disable built-in torch button
            showZoomSliderIfSupported: false,
            defaultZoomValueIfSupported: 2,
            rememberLastUsedCamera: true,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
            videoConstraints: {
                facingMode: "environment"
            }
        }, /* verbose= */ false);
        
        updateStatus('ready', 'Ready to scan');
        
        // Don't auto-start scanner - let user manually start it
        // This prevents camera permission issues on page load
    } catch (error) {
        updateStatus('error', 'Scanner initialization failed');
        showAlert('Failed to initialize scanner. Please refresh the page.', 'error');
    }
}

function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

async function checkCameraPermission() {
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return false;
        }
        
        // Check if we can query permissions
        if (navigator.permissions) {
            const permission = await navigator.permissions.query({ name: 'camera' });
            return permission.state === 'granted';
        }
        
        // Fallback: try to get a media stream
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        stream.getTracks().forEach(track => track.stop());
        return true;
    } catch (error) {
        return false;
    }
}

async function toggleScanner() {
    if (isScanning) {
        stopScanner();
    } else {
        // Check camera permission before starting
        const hasPermission = await checkCameraPermission();
        if (!hasPermission) {
            updateStatus('error', 'Camera permission required');
            showCameraPermissionModal();
            return;
        }
        startScanner();
    }
}

async function startScanner() {
    try {
        updateStatus('starting', 'Starting camera...');
        
        // Check for camera support
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Camera not supported on this device');
        }
        
        // Start the QR scanner directly - it will handle camera permissions
        await qrScanner.render(onScanSuccess, onScanError);
        isScanning = true;
        updateStatus('scanning', 'Scanning for QR codes...');
        document.getElementById('toggleButton').innerHTML = '<span id="toggleText">Stop</span>';
        
        // Hide overlay when camera is active
        document.getElementById('scanner-overlay').style.display = 'none';
    } catch (error) {
        updateStatus('error', 'Camera error');
        isScanning = false;
        
        let errorMessage = 'Error accessing camera. ';
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Please allow camera access and try again.';
            showCameraPermissionModal();
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'No camera found on this device.';
        } else if (error.name === 'NotSupportedError') {
            errorMessage += 'Camera not supported on this browser.';
        } else if (error.name === 'NotReadableError') {
            errorMessage += 'Camera is being used by another application.';
        } else {
            errorMessage += 'Please check your browser settings.';
        }
        
        showAlert(errorMessage, 'error');
    }
}

function stopScanner() {
    try {
        qrScanner.clear();
        isScanning = false;
        updateStatus('ready', 'Ready to scan');
        document.getElementById('toggleButton').innerHTML = '<span id="toggleText">Start</span>';
        
        // Show overlay when camera is stopped
        document.getElementById('scanner-overlay').style.display = 'flex';
        const instructions = document.getElementById('scanner-instructions');
        instructions.innerHTML = `
            <div class="w-20 h-20 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm md:w-24 md:h-24">
                <svg class="w-10 h-10 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            </div>
            <p class="text-sm md:text-base">Tap "Start" to begin scanning</p>
        `;
    } catch (error) {
        // Silently handle stop scanner errors
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanner temporarily
    stopScanner();
    
    // Show processing modal
    showProcessingModal('Looking up guest...');
    
    // Process the scanned QR code
    processQRCode(decodedText);
}

function onScanError(error) {
    // Ignore frequent scan errors - they're normal
}

function processQRCode(qrData) {
    fetch(`/scanner/${scannerToken}/qr`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ qr_data: qrData })
    })
    .then(response => response.json())
    .then(data => {
        hideProcessingModal();
        
        if (data.success) {
            showGuestModal(data.guest);
        } else {
            showAlert('Invalid QR code', 'error');
            // Restart scanner after 3 seconds to give user time to read error
            setTimeout(startScanner, 3000);
        }
    })
    .catch(error => {
        hideProcessingModal();
        showAlert('Error processing QR code', 'error');
        setTimeout(startScanner, 2000);
    });
}



function showGuestModal(guest) {
    const modal = document.getElementById('guestModal');
    const modalContent = document.getElementById('modalContent');
    const guestInfo = document.getElementById('guestInfo');
    
    const contacts = guest.contacts || {};
    const contactsHtml = Object.keys(contacts).length > 0 ? 
        Object.entries(contacts).map(([type, value]) => 
            `<div class="text-sm text-gray-600 flex items-center justify-center mb-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'email' ? 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' : 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'}"></path>
                </svg>
                ${value}
            </div>`
        ).join('') : 
        '<div class="text-sm text-gray-500">No contact info</div>';
    
    guestInfo.innerHTML = `
        <div class="space-y-6">
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">${guest.name}</h3>
                ${guest.group ? `<p class="text-sm text-primary-600 font-medium mb-2">${guest.group}</p>` : ''}
                <div class="mt-3">${contactsHtml}</div>
            </div>
            
            <div class="space-y-4">
                ${guest.checked_in ? `
                    <div class="p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border border-green-200">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="font-semibold text-green-800">Already Checked In</p>
                            <p class="text-sm text-green-700">at ${formatTimeToLocal(guest.checked_in_at)}</p>
                            ${guest.scanner_name ? `<p class="text-xs text-green-600">by ${guest.scanner_name}</p>` : ''}
                        </div>
                    </div>
                ` : `
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <p class="font-semibold text-blue-800">Ready to Check In</p>
                        </div>
                    </div>
                `}
            </div>
            
            <div class="flex space-x-3">
                ${!guest.checked_in ? `
                    <button onclick="checkInGuest(${guest.id})" class="btn-success flex-1 py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check In
                    </button>
                ` : ''}
                <button onclick="closeGuestModal(); setTimeout(startScanner, 500);" class="btn-secondary ${guest.checked_in ? 'flex-1' : ''} py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    ${guest.checked_in ? 'Continue Scanning' : 'Cancel'}
                </button>
            </div>
        </div>
    `;
    
    // Show modal with animation
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function checkInGuest(guestId) {
    showProcessingModal('Checking in guest...');
    
    fetch(`/scanner/${scannerToken}/checkin`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ 
            guest_id: guestId,
            notes: 'Scanned check-in'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideProcessingModal();
        closeGuestModal();
        
        if (data.success) {
            showAlert('Guest checked in successfully!', 'success');
            addToRecentScans(data.guest);
            loadStats();
            // Restart scanner after showing success
            setTimeout(startScanner, 2000);
        } else {
            showAlert(data.message || 'Error checking in guest', 'error');
            setTimeout(startScanner, 2000);
        }
    })
    .catch(error => {
        hideProcessingModal();
        closeGuestModal();
        showAlert('Error checking in guest', 'error');
        setTimeout(startScanner, 2000);
    });
}

function closeGuestModal() {
    const modal = document.getElementById('guestModal');
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function showProcessingModal(message) {
    document.getElementById('processingMessage').textContent = message;
    document.getElementById('processingModal').classList.remove('hidden');
}

function hideProcessingModal() {
    document.getElementById('processingModal').classList.add('hidden');
}

function showCameraPermissionModal() {
    document.getElementById('cameraPermissionModal').classList.remove('hidden');
}

function closeCameraPermissionModal() {
    document.getElementById('cameraPermissionModal').classList.add('hidden');
}

async function retryCamera() {
    closeCameraPermissionModal();
    // Wait a moment then try to start the scanner again
    setTimeout(async () => {
        if (!isScanning) {
            const hasPermission = await checkCameraPermission();
            if (hasPermission) {
                startScanner();
            } else {
                updateStatus('error', 'Camera permission still required');
                showCameraPermissionModal();
            }
        }
    }, 500);
}

function toggleFlashlight() {
    // This would need to be implemented with the camera API
    flashlightOn = !flashlightOn;
    const button = document.getElementById('flashButton');
    
    if (flashlightOn) {
        button.classList.add('bg-yellow-500', 'text-white');
        button.classList.remove('btn-secondary');
    } else {
        button.classList.remove('bg-yellow-500', 'text-white');
        button.classList.add('btn-secondary');
    }
}

function updateStatus(status, message) {
    const dot = document.getElementById('statusDot');
    const statusText = document.getElementById('scannerStatus');
    
    dot.className = 'w-3 h-3 rounded-full mr-3';
    
    switch(status) {
        case 'ready':
            dot.classList.add('bg-blue-500');
            break;
        case 'initializing':
            dot.classList.add('bg-yellow-500');
            break;
        case 'starting':
            dot.classList.add('bg-blue-400');
            break;
        case 'scanning':
            dot.classList.add('bg-green-500');
            break;
        case 'error':
            dot.classList.add('bg-red-500');
            break;
        default:
            dot.classList.add('bg-gray-500');
    }
    
    statusText.textContent = message;
}

function loadStats() {
    // Fetch real stats from API
    fetch(`/scanner/${scannerToken}/scan-stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalGuests').textContent = data.stats.total_guests;
                document.getElementById('checkedInGuests').textContent = data.stats.checked_in_guests;
                document.getElementById('myScans').textContent = data.stats.my_scans;
                
                // Update recent scans with real data
                updateRecentScansFromAPI(data.stats.recent_checkins);
            }
        })
        .catch(error => {
            // Silently handle stats loading errors
        });
    

}

function addToRecentScans(guest) {
    recentScans.unshift({
        name: guest.name,
        time: new Date().toLocaleTimeString(),
        id: guest.id
    });
    
    // Keep only last 5 scans
    recentScans = recentScans.slice(0, 5);
    
    updateRecentScansDisplay();
}

function updateRecentScansFromAPI(apiRecentScans) {
    // Update the recentScans array with data from API
    recentScans = apiRecentScans.map(scan => ({
        name: scan.name,
        time: formatTimeToLocal(scan.time),
        relativeTime: calculateRelativeTime(scan.time),
        id: scan.id
    }));
    
    updateRecentScansDisplay();
}



function updateRecentScansDisplay() {
    const container = document.getElementById('recentScans');
    
    if (recentScans.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0zm-9 8a3 3 0 00-3-3h6a3 3 0 00-3 3z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">No scans yet</p>
                <p class="text-gray-400 text-sm">Start scanning to see activity here</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = recentScans.map(scan => `
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl mb-3 last:mb-0 shadow-sm hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900">${scan.name}</div>
                    <div class="text-sm text-gray-600">${scan.time}</div>
                </div>
            </div>
            <div class="text-sm text-primary-600 font-medium">${scan.relativeTime}</div>
        </div>
    `).join('');
}

// Handle page visibility changes to manage camera
document.addEventListener('visibilitychange', function() {
    if (document.hidden && isScanning) {
        stopScanner();
    }
});

// Clean up when leaving page
window.addEventListener('beforeunload', function() {
    if (isScanning) {
        stopScanner();
    }
});
</script>
@endpush
