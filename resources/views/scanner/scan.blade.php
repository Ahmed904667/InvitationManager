@extends('layouts.scanner')

@section('title', 'Scan Guest')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary">Guest Scanner</h1>
            <p class="text-gray-600 mt-2">Scan QR codes to check in guests</p>
        </div>

        <!-- Scanner Status & Toolbar -->
        <div class="scanner-toolbar bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Status Section -->
                <div class="flex items-center space-x-4">
                    <div class="scanner-status-icon">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-primary">Scanner Status</h3>
                        <p class="text-sm text-gray-500" id="scannerStatus">Initializing...</p>
                    </div>
                </div>

                <!-- Connection Status -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2 px-3 py-2 bg-gray-50 rounded-lg">
                        <div class="w-3 h-3 bg-green-400 rounded-full" id="connectionIndicator"></div>
                        <span class="text-sm font-medium text-gray-700" id="connectionStatus">Online</span>
                    </div>
                </div>

                <!-- Main Control Button -->
                <div class="flex items-center space-x-3">
                    <button class="scanner-main-button" onclick="toggleScanner()" id="toggleButton">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Start Scanner now
                    </button>
                    
                    <!-- Settings Button -->
                    <button class="scanner-settings-button" onclick="showSettingsModal()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Scanner Area -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Camera View - Takes 2 columns on desktop -->
            <div class="xl:col-span-2">
                <div class="scanner-camera-card bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="scanner-card-header">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-primary">Camera View</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-500">Live</span>
                        </div>
                    </div>
                    <div class="relative bg-gray-900">
                    <h1>this is the scan area</h1>
                        <video id="camera" class="w-full h-full bg-gray-900 object-cover" autoplay muted playsinline></video>
                        
                        <div id="scannerOverlay" class="absolute inset-0 flex items-center justify-center pointer-events-none" style="background-color: rgba(0, 255, 0, 0.1);">
                            <div class="scanner-frame" style="position: relative; width: 300px; height: 300px; border: 8px solid #ff0000; border-radius: 1rem; background-color: rgba(255, 0, 0, 0.1); box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);">
                                <div class="scanner-corner top-left"></div>
                                <div class="scanner-corner top-right"></div>
                                <div class="scanner-corner bottom-left"></div>
                                <div class="scanner-corner bottom-right"></div>
                            </div>
                        </div>
                        <div id="scanningIndicator" class="absolute top-4 left-4 bg-green-500 text-white px-4 py-2 rounded-full text-sm font-medium hidden shadow-lg">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                <span>Scanning...</span>
                            </div>
                        </div>
                    </div>
                <div class="scanner-controls bg-gradient-to-r from-gray-50 to-gray-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <!-- Camera Controls -->
                        <div class="flex items-center space-x-3">
                            <button class="scanner-control-button" onclick="switchCamera()">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Switch Camera
                            </button>
                            <button class="scanner-control-button" onclick="toggleFlash()" id="flashButton">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Flash
                            </button>
                        </div>
                        
                        <!-- Camera Info -->
                        <div class="flex items-center space-x-2 px-3 py-2 bg-white rounded-lg shadow-sm">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-600" id="cameraInfo">No camera selected</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scan Results - Takes 1 column on desktop -->
            <div class="xl:col-span-1">
                <div class="scanner-results-card bg-white rounded-xl shadow-lg border border-gray-200 h-full">
                    <div class="scanner-card-header">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-primary">Scan Results</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500" id="scanCount">0 scans</span>
                        </div>
                    </div>
                    <div class="scanner-results-content">
                        <div id="scanResults" class="space-y-3">
                            <div class="text-center text-gray-500 py-12">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="font-medium text-gray-600">No scans yet</p>
                                <p class="text-sm text-gray-400 mt-1">Scan a QR code to see guest information</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Manual Entry -->
            <div class="scanner-manual-entry bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-primary">Manual Entry</h3>
                </div>
                <form id="manualEntryForm" class="space-y-4">
                    <div>
                        <label class="form-label">Guest List</label>
                        <select name="guest_list_id" class="form-select" required>
                            <option value="">Select a list</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Guest Email</label>
                        <input type="email" name="email" class="form-input" placeholder="Enter guest email" required>
                    </div>
                    <button type="submit" class="scanner-main-button w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check In Guest
                    </button>
                </form>
            </div>

            <!-- Recent Activity -->
            <div class="scanner-activity bg-white rounded-xl shadow-lg border border-gray-200">
                <div class="scanner-card-header">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-primary">Recent Activity</h3>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500" id="activityCount">0 activities</span>
                    </div>
                </div>
                <div class="scanner-activity-content">
                    <div id="recentActivity" class="space-y-3">
                        <!-- Will be populated via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Guest Details Modal -->
<div id="guestModal" class="modal hidden">
    <div class="modal-content max-w-md">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-primary">Guest Information</h3>
            <button type="button" class="modal-close" onclick="hideGuestModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="guestModalContent">
            <!-- Will be populated via AJAX -->
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div id="settingsModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-lg font-medium text-primary">Scanner Settings</h3>
            <button type="button" class="modal-close" onclick="hideSettingsModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="space-y-6">
                <div>
                    <label class="form-label">Camera Resolution</label>
                    <select id="cameraResolution" class="form-select">
                        <option value="1280x720">HD (1280x720)</option>
                        <option value="1920x1080">Full HD (1920x1080)</option>
                        <option value="640x480">VGA (640x480)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Scan Interval (ms)</label>
                    <input type="number" id="scanInterval" class="form-input" value="100" min="50" max="1000">
                </div>
                <div>
                    <label class="form-label">Auto-save Scans</label>
                    <div class="flex items-center">
                        <input type="checkbox" id="autoSave" class="form-checkbox" checked>
                        <span class="ml-2 text-sm text-gray-700">Automatically save successful scans</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Sound Effects</label>
                    <div class="flex items-center">
                        <input type="checkbox" id="soundEffects" class="form-checkbox" checked>
                        <span class="ml-2 text-sm text-gray-700">Play sound on successful scan</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="hideSettingsModal()">Cancel</button>
                <button type="button" class="btn-primary" onclick="saveSettings()">Save Settings</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let scanner = null;
let currentStream = null;
let isScanning = false;
let scanResults = [];
let settings = {
    resolution: '1280x720',
    scanInterval: 100,
    autoSave: true,
    soundEffects: true
};

// Load scanner on page load
document.addEventListener('DOMContentLoaded', function() {
    loadGuestLists();
    loadRecentActivity();
    loadSettings();
    setupEventListeners();
    checkConnection();
});

function setupEventListeners() {
    // Manual entry form
    document.getElementById('manualEntryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        checkInGuest(formData);
    });

    // Settings
    document.getElementById('cameraResolution').addEventListener('change', function() {
        settings.resolution = this.value;
        if (isScanning) {
            restartScanner();
        }
    });

    document.getElementById('scanInterval').addEventListener('change', function() {
        settings.scanInterval = parseInt(this.value);
    });

    document.getElementById('autoSave').addEventListener('change', function() {
        settings.autoSave = this.checked;
    });

    document.getElementById('soundEffects').addEventListener('change', function() {
        settings.soundEffects = this.checked;
    });
}

async function toggleScanner() {
    if (isScanning) {
        stopScanner();
    } else {
        await startScanner();
    }
}

async function startScanner() {
    try {
        updateScannerStatus('Starting camera...', 'warning');
        
        const constraints = {
            video: {
                facingMode: 'environment',
                width: { ideal: parseInt(settings.resolution.split('x')[0]) },
                height: { ideal: parseInt(settings.resolution.split('x')[1]) }
            }
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        currentStream = stream;
        
        const video = document.getElementById('camera');
        video.srcObject = stream;
        
        // Wait for video to load
        await new Promise((resolve) => {
            video.onloadedmetadata = resolve;
        });

        // Start scanning
        startScanning();
        
        updateScannerStatus('Scanner active', 'success');
        document.getElementById('toggleButton').innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Stop Scanner
        `;
        
        document.getElementById('scannerOverlay').classList.remove('hidden');
        document.getElementById('scanningIndicator').classList.remove('hidden');
        
        isScanning = true;
        
    } catch (error) {
        console.error('Error starting scanner:', error);
        updateScannerStatus('Failed to start camera: ' + error.message, 'error');
    }
}

function stopScanner() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    
    const video = document.getElementById('camera');
    video.srcObject = null;
    
    if (scanner) {
        clearInterval(scanner);
        scanner = null;
    }
    
    updateScannerStatus('Scanner stopped', 'info');
    document.getElementById('toggleButton').innerHTML = `
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Start Scanner
    `;
    
    document.getElementById('scannerOverlay').classList.add('hidden');
    document.getElementById('scanningIndicator').classList.add('hidden');
    
    isScanning = false;
}

function startScanning() {
    const video = document.getElementById('camera');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    scanner = setInterval(() => {
        if (video.videoWidth === 0) return;
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            handleScanResult(code.data);
        }
    }, settings.scanInterval);
}

function handleScanResult(data) {
    // Prevent duplicate scans
    if (scanResults.some(result => result.data === data && Date.now() - result.timestamp < 2000)) {
        return;
    }
    
    const result = {
        data: data,
        timestamp: Date.now(),
        type: 'qr'
    };
    
    scanResults.unshift(result);
    
    // Play sound if enabled
    if (settings.soundEffects) {
        playScanSound();
    }
    
    // Process the scan
    processScanResult(data);
    
    // Update UI
    updateScanResults();
}

function processScanResult(data) {
    try {
        // Try to parse as JSON first
        const parsed = JSON.parse(data);
        if (parsed.guest_id) {
            checkInGuestById(parsed.guest_id);
            return;
        }
    } catch (e) {
        // Not JSON, try as email
        if (data.includes('@')) {
            checkInGuestByEmail(data);
            return;
        }
    }
    
    // Show error for invalid QR code
    window.GuestManager.showNotification('Invalid QR code format', 'error');
}

async function checkInGuestById(guestId) {
    try {
        const response = await fetch('/scanner/checkin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ guest_id: guestId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showGuestModal(data.guest);
            showNotification('Guest checked in successfully!', 'success');
            loadRecentActivity();
        } else {
            showNotification(data.message || 'Failed to check in guest', 'error');
        }
    } catch (error) {
        console.error('Error checking in guest:', error);
        showNotification('Error checking in guest', 'error');
    }
}

async function checkInGuestByEmail(email) {
    const formData = new FormData();
    formData.append('email', email);
    
    try {
        const response = await fetch('/scanner/checkin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showGuestModal(data.guest);
            showNotification('Guest checked in successfully!', 'success');
            loadRecentActivity();
        } else {
            showNotification(data.message || 'Failed to check in guest', 'error');
        }
    } catch (error) {
        console.error('Error checking in guest:', error);
        showNotification('Error checking in guest', 'error');
    }
}

function checkInGuest(formData) {
    fetch('/scanner/checkin', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showGuestModal(data.guest);
            showNotification('Guest checked in successfully!', 'success');
            document.getElementById('manualEntryForm').reset();
            loadRecentActivity();
        } else {
            showNotification(data.message || 'Failed to check in guest', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error checking in guest', 'error');
    });
}

function updateScanResults() {
    const container = document.getElementById('scanResults');
    const scanCountElement = document.getElementById('scanCount');
    
    // Update scan count
    scanCountElement.textContent = `${scanResults.length} scan${scanResults.length !== 1 ? 's' : ''}`;
    
    if (scanResults.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="font-medium text-gray-600">No scans yet</p>
                <p class="text-sm text-gray-400 mt-1">Scan a QR code to see guest information</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = scanResults.slice(0, 5).map(result => `
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-primary truncate">${result.data}</p>
                    <p class="text-xs text-gray-500 mt-1">${new Date(result.timestamp).toLocaleTimeString()}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1"></div>
                        Scanned
                    </span>
                </div>
            </div>
        </div>
    `).join('');
}

function showGuestModal(guest) {
    const modal = document.getElementById('guestModal');
    const content = document.getElementById('guestModalContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h4 class="text-lg font-medium text-primary mb-2">${guest.name}</h4>
            <p class="text-gray-600 mb-4">${guest.email}</p>
            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-gray-600">Event: <span class="font-medium">${guest.guest_list.name}</span></p>
                <p class="text-sm text-gray-600">Checked in: <span class="font-medium">${new Date().toLocaleTimeString()}</span></p>
            </div>
            <button class="btn-primary w-full" onclick="hideGuestModal()">Close</button>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function hideGuestModal() {
    document.getElementById('guestModal').classList.add('hidden');
}

function showSettingsModal() {
    document.getElementById('settingsModal').classList.remove('hidden');
}

function hideSettingsModal() {
    document.getElementById('settingsModal').classList.add('hidden');
}

function saveSettings() {
    localStorage.setItem('scannerSettings', JSON.stringify(settings));
    hideSettingsModal();
    showNotification('Settings saved', 'success');
}

function loadSettings() {
    const saved = localStorage.getItem('scannerSettings');
    if (saved) {
        settings = { ...settings, ...JSON.parse(saved) };
    }
    
    document.getElementById('cameraResolution').value = settings.resolution;
    document.getElementById('scanInterval').value = settings.scanInterval;
    document.getElementById('autoSave').checked = settings.autoSave;
    document.getElementById('soundEffects').checked = settings.soundEffects;
}

function loadGuestLists() {
    fetch('/scanner/guest-lists')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="guest_list_id"]');
            select.innerHTML = '<option value="">Select a list</option>';
            
            data.forEach(list => {
                const option = document.createElement('option');
                option.value = list.id;
                option.textContent = list.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading guest lists:', error));
}

function loadRecentActivity() {
    fetch('/scanner/recent-activity')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentActivity');
            const activityCountElement = document.getElementById('activityCount');
            
            // Update activity count
            activityCountElement.textContent = `${data.length} activit${data.length !== 1 ? 'ies' : 'y'}`;
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-600">No recent activity</p>
                        <p class="text-sm text-gray-400 mt-1">Guest check-ins will appear here</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = data.map(activity => `
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-primary truncate">${activity.guest.name}</p>
                            <p class="text-sm text-gray-500 truncate">${activity.guest_list.name}</p>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${new Date(activity.created_at).toLocaleTimeString()}
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => console.error('Error loading recent activity:', error));
}

function updateScannerStatus(message, type) {
    const statusElement = document.getElementById('scannerStatus');
    statusElement.textContent = message;
    statusElement.className = `text-sm ${getStatusColor(type)}`;
}

function getStatusColor(type) {
    switch (type) {
        case 'success': return 'text-green-600';
        case 'warning': return 'text-yellow-600';
        case 'error': return 'text-red-600';
        default: return 'text-gray-500';
    }
}

function checkConnection() {
    const indicator = document.getElementById('connectionIndicator');
    const status = document.getElementById('connectionStatus');
    
    if (navigator.onLine) {
        indicator.className = 'w-3 h-3 bg-green-400 rounded-full mr-2';
        status.textContent = 'Online';
    } else {
        indicator.className = 'w-3 h-3 bg-red-400 rounded-full mr-2';
        status.textContent = 'Offline';
    }
}

function playScanSound() {
    // Create a simple beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
}

// Event listeners
window.addEventListener('online', checkConnection);
window.addEventListener('offline', checkConnection);

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endpush

@push('styles')
<style>
.scanner-frame {
    position: relative;
    width: 300px;
    height: 300px;
    border: 8px solid #ff0000;
    border-radius: 1rem;
    background-color: rgba(255, 0, 0, 0.1);
    box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
}

.scanner-corner {
    position: absolute;
    width: 30px;
    height: 30px;
    border: 4px solid #3b82f6;
    border-radius: 0.5rem;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

.scanner-corner.top-left {
    top: -4px;
    left: -4px;
    border-right: none;
    border-bottom: none;
}

.scanner-corner.top-right {
    top: -4px;
    right: -4px;
    border-left: none;
    border-bottom: none;
}

.scanner-corner.bottom-left {
    bottom: -4px;
    left: -4px;
    border-right: none;
    border-top: none;
}

.scanner-corner.bottom-right {
    bottom: -4px;
    right: -4px;
    border-left: none;
    border-top: none;
}
</style>
@endpush 