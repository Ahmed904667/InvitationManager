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

        <!-- Scanner Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-primary">Scanner Status</h3>
                        <p class="text-sm text-gray-500" id="scannerStatus">Initializing...</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-400 rounded-full mr-2" id="connectionIndicator"></div>
                        <span class="text-sm text-gray-600" id="connectionStatus">Online</span>
                    </div>
                    <button class="btn-secondary" onclick="toggleScanner()" id="toggleButton">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Start Scanner
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Scanner Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Camera View -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-primary">Camera View</h3>
                </div>
                <div class="relative">
                    <video id="camera" class="w-full h-64 lg:h-96 bg-gray-900" autoplay muted playsinline></video>
                    <div id="scannerOverlay" class="absolute inset-0 flex items-center justify-center hidden">
                        <div class="scanner-frame">
                            <div class="scanner-corner top-left"></div>
                            <div class="scanner-corner top-right"></div>
                            <div class="scanner-corner bottom-left"></div>
                            <div class="scanner-corner bottom-right"></div>
                        </div>
                    </div>
                    <div id="scanningIndicator" class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium hidden">
                        Scanning...
                    </div>
                </div>
                <div class="p-4 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button class="btn-secondary" onclick="switchCamera()">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Switch Camera
                            </button>
                            <button class="btn-secondary" onclick="toggleFlash()" id="flashButton">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Flash
                            </button>
                        </div>
                        <div class="text-sm text-gray-500">
                            <span id="cameraInfo">No camera selected</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scan Results -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-primary">Scan Results</h3>
                </div>
                <div class="p-4">
                    <div id="scanResults" class="space-y-4">
                        <div class="text-center text-gray-500 py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2">No scans yet</p>
                            <p class="text-sm">Scan a QR code to see guest information</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Entry -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-primary mb-4">Manual Entry</h3>
            <form id="manualEntryForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check In
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-primary">Recent Activity</h3>
            </div>
            <div class="p-4">
                <div id="recentActivity" class="space-y-3">
                    <!-- Will be populated via AJAX -->
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
    
    if (scanResults.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2">No scans yet</p>
                <p class="text-sm">Scan a QR code to see guest information</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = scanResults.slice(0, 5).map(result => `
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary">${result.data}</p>
                    <p class="text-xs text-gray-500">${new Date(result.timestamp).toLocaleTimeString()}</p>
                </div>
                <span class="badge badge-success">Scanned</span>
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
            
            if (data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent activity</p>';
                return;
            }
            
            container.innerHTML = data.map(activity => `
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary">${activity.guest.name}</p>
                        <p class="text-sm text-gray-500">${activity.guest_list.name}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        ${new Date(activity.created_at).toLocaleTimeString()}
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
    width: 200px;
    height: 200px;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.scanner-corner {
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #3b82f6;
}

.scanner-corner.top-left {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}

.scanner-corner.top-right {
    top: -3px;
    right: -3px;
    border-left: none;
    border-bottom: none;
}

.scanner-corner.bottom-left {
    bottom: -3px;
    left: -3px;
    border-right: none;
    border-top: none;
}

.scanner-corner.bottom-right {
    bottom: -3px;
    right: -3px;
    border-left: none;
    border-top: none;
}
</style>
@endpush 