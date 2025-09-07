@extends('layouts.organizer')

@section('title', 'Edit Profile')

@section('content')
<div class="min-h-screen bg-primary py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-primary-600">Edit Profile</h1>
                <p class="mt-2 text-secondary">Update your account information and preferences</p>
            </div>
            <a href="{{ route('organizer.profile.show') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Back to Profile
            </a>
        </div>

        <!-- Profile Photo Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Profile Photo</h3>
            
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center overflow-hidden shadow-lg">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-white font-bold text-3xl">{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex-1">
                    <div class="space-y-3">
                        <!-- Photo Upload Button -->
                        <button type="button" onclick="openPhotoModal()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Change Photo
                        </button>

                        @if($user->profile_photo_url)
                            <form action="{{ route('organizer.profile.photo.delete') }}" method="POST" class="inline-block ml-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 text-sm transition-colors duration-200">
                                    Remove Photo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information Form -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Profile Information</h3>
            
            <form action="{{ route('organizer.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-primary mb-2">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-primary mb-2">Bio</label>
                        <textarea id="bio" name="bio" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200 @error('bio') border-red-500 @enderror"
                                  placeholder="Tell us a bit about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Email Update Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Email Address</h3>
            
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-secondary mb-1">Current Email</p>
                    <p class="text-primary font-medium">{{ $user->email }}</p>
                </div>
                <button type="button" onclick="openEmailModal()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                    Update Email
                </button>
            </div>
        </div>

        <!-- Phone Update Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Phone Number</h3>
            
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-secondary mb-1">Current Phone</p>
                    <p class="text-primary font-medium">{{ $user->phone ?: 'Not provided' }}</p>
                </div>
                <button type="button" onclick="openPhoneModal()" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                    {{ $user->phone ? 'Update Phone' : 'Add Phone' }}
                </button>
            </div>
        </div>

        <!-- Password Reset Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Password</h3>
            
            <div class="space-y-4">
                <div class="bg-blue-500/10 dark:bg-blue-900/20 border border-blue-500/20 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-400 dark:text-blue-300">
                                Secure Password Reset
                            </h3>
                            <div class="mt-2 text-sm text-blue-300 dark:text-blue-400">
                                <p>Click the button below to receive a secure password reset link via email. This link will be valid for 24 hours and can only be used once.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button 
                    type="button" 
                    onclick="sendPasswordResetLink()"
                    id="passwordResetBtn"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200 flex items-center justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Send Password Reset Link
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div id="photoModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-secondary rounded-xl shadow-2xl border border-white/20 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/20">
                <h3 class="text-lg font-semibold text-primary">Upload Profile Photo</h3>
                <button type="button" onclick="closePhotoModal()" class="text-secondary hover:text-primary transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <!-- File Input -->
                <div class="mb-6">
                    <label for="photoInput" class="block text-sm font-medium text-primary mb-2">Choose Image</label>
                    <input type="file" id="photoInput" accept="image/*" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200">
                </div>

                <!-- Image Preview and Cropper -->
                <div id="imagePreview" class="hidden mb-6">
                    <div class="text-center">
                        <div class="inline-block relative">
                            <img id="previewImage" src="" alt="Preview" class="max-w-full max-h-96 rounded-lg">
                        </div>
                    </div>
                    <p class="text-sm text-secondary mt-2 text-center">Drag to move, scroll to zoom</p>
                </div>

                <!-- Cropper Controls -->
                <div id="cropperControls" class="hidden mb-6">
                    <div class="flex justify-center space-x-4">
                        <button type="button" id="rotateLeft" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            ↺ Rotate Left
                        </button>
                        <button type="button" id="rotateRight" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200">
                            ↻ Rotate Right
                        </button>
                        <button type="button" id="resetCrop" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closePhotoModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="button" id="cropAndUpload" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 hidden">
                        Crop & Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Update Modal -->
<div id="emailModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-secondary rounded-xl shadow-2xl border border-white/20 max-w-md w-full">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/20">
                <h3 class="text-lg font-semibold text-primary">Update Email Address</h3>
                <button type="button" onclick="closeEmailModal()" class="text-secondary hover:text-primary transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div id="emailForm">
                    <div class="mb-4">
                        <label for="newEmail" class="block text-sm font-medium text-primary mb-2">New Email Address</label>
                        <input type="email" id="newEmail" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200" placeholder="Enter new email">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEmailModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="button" onclick="sendEmailOTP()" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Send OTP
                        </button>
                    </div>
                </div>

                <div id="emailOTPForm" class="hidden">
                    <div class="mb-4">
                        <label for="emailOTP" class="block text-sm font-medium text-primary mb-2">Enter OTP Code</label>
                        <input type="text" id="emailOTP" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200 text-center text-lg tracking-widest" placeholder="000000" maxlength="6">
                        <p class="text-sm text-secondary mt-2">Enter the 6-digit code sent to your email</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEmailModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="button" onclick="verifyEmailOTP()" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Verify & Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phone Update Modal -->
<div id="phoneModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-secondary rounded-xl shadow-2xl border border-white/20 max-w-md w-full">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/20">
                <h3 class="text-lg font-semibold text-primary">Update Phone Number</h3>
                <button type="button" onclick="closePhoneModal()" class="text-secondary hover:text-primary transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div id="phoneForm">
                    <div class="mb-4">
                        <label for="newPhone" class="block text-sm font-medium text-primary mb-2">New Phone Number</label>
                        <input type="tel" id="newPhone" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200" placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePhoneModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="button" onclick="sendPhoneOTP()" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Send OTP
                        </button>
                    </div>
                </div>

                <div id="phoneOTPForm" class="hidden">
                    <div class="mb-4">
                        <label for="phoneOTP" class="block text-sm font-medium text-primary mb-2">Enter OTP Code</label>
                        <input type="text" id="phoneOTP" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-primary dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors duration-200 text-center text-lg tracking-widest" placeholder="000000" maxlength="6">
                        <p class="text-sm text-secondary mt-2">Enter the 6-digit code sent to your phone</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePhoneModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="button" onclick="verifyPhoneOTP()" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            Verify & Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<script>
let cropper = null;
let selectedFile = null;
let currentEmail = '';
let currentPhone = '';

// Photo Modal Functions
function openPhotoModal() {
    document.getElementById('photoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePhotoModal() {
    document.getElementById('photoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    
    document.getElementById('photoInput').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('cropperControls').classList.add('hidden');
    document.getElementById('cropAndUpload').classList.add('hidden');
    
    selectedFile = null;
}

// Email Modal Functions
function openEmailModal() {
    document.getElementById('emailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('emailForm').classList.remove('hidden');
    document.getElementById('emailOTPForm').classList.add('hidden');
    document.getElementById('newEmail').value = '';
    document.getElementById('emailOTP').value = '';
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('emailForm').classList.remove('hidden');
    document.getElementById('emailOTPForm').classList.add('hidden');
}

// Phone Modal Functions
function openPhoneModal() {
    document.getElementById('phoneModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('phoneForm').classList.remove('hidden');
    document.getElementById('phoneOTPForm').classList.add('hidden');
    document.getElementById('newPhone').value = '';
    document.getElementById('phoneOTP').value = '';
}

function closePhoneModal() {
    document.getElementById('phoneModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('phoneForm').classList.remove('hidden');
    document.getElementById('phoneOTPForm').classList.add('hidden');
}

// Send Email OTP
function sendEmailOTP() {
    const email = document.getElementById('newEmail').value.trim();
    
    if (!email) {
        safeShowNotification('Please enter a valid email address', 'error');
        return;
    }
    
    fetch('{{ route("organizer.profile.email") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentEmail = email;
            document.getElementById('emailForm').classList.add('hidden');
            document.getElementById('emailOTPForm').classList.remove('hidden');
            safeShowNotification('OTP sent! Check your email for the verification code.', 'success');
        } else {
            safeShowNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        safeShowNotification('Error sending OTP. Please try again. If the problem persists, try refreshing the page.', 'error');
    });
}

// Verify Email OTP
function verifyEmailOTP() {
    const otp = document.getElementById('emailOTP').value.trim();
    
    if (!otp || otp.length !== 6) {
        safeShowNotification('Please enter a valid 6-digit OTP', 'error');
        return;
    }
    
    fetch('{{ route("organizer.profile.email.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            email: currentEmail,
            otp_code: otp 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            safeShowNotification('Email updated successfully!', 'success');
            closeEmailModal();
            window.location.reload();
        } else {
            safeShowNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        safeShowNotification('Error verifying OTP. Please try again.', 'error');
    });
}

// Send Phone OTP
function sendPhoneOTP() {
    const phone = document.getElementById('newPhone').value.trim();
    
    if (!phone) {
        safeShowNotification('Please enter a valid phone number', 'error');
        return;
    }
    
    fetch('{{ route("organizer.profile.phone") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPhone = phone;
            document.getElementById('phoneForm').classList.add('hidden');
            document.getElementById('phoneOTPForm').classList.remove('hidden');
            safeShowNotification('OTP sent! Check your WhatsApp for the verification code.', 'success');
        } else {
            safeShowNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        safeShowNotification('Error sending OTP. Please try again. If the problem persists, try refreshing the page.', 'error');
    });
}

// Verify Phone OTP
function verifyPhoneOTP() {
    const otp = document.getElementById('phoneOTP').value.trim();
    
    if (!otp || otp.length !== 6) {
        safeShowNotification('Please enter a valid 6-digit OTP', 'error');
        return;
    }
    
    fetch('{{ route("organizer.profile.phone.verify") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            phone: currentPhone,
            otp_code: otp 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            safeShowNotification('Phone updated successfully!', 'success');
            closePhoneModal();
            window.location.reload();
        } else {
            safeShowNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        safeShowNotification('Error verifying OTP. Please try again.', 'error');
    });
}

// Handle file selection
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        selectedFile = file;
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            previewImage.src = e.target.result;
            
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('cropperControls').classList.remove('hidden');
            document.getElementById('cropAndUpload').classList.remove('hidden');
            
            if (cropper) {
                cropper.destroy();
            }
            
            cropper = new Cropper(previewImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready: function() {
                    console.log('Cropper ready');
                }
            });
        };
        
        reader.readAsDataURL(file);
    }
});

// Cropper controls
document.getElementById('rotateLeft').addEventListener('click', function() {
    if (cropper) {
        cropper.rotate(-90);
    }
});

document.getElementById('rotateRight').addEventListener('click', function() {
    if (cropper) {
        cropper.rotate(90);
    }
});

document.getElementById('resetCrop').addEventListener('click', function() {
    if (cropper) {
        cropper.reset();
    }
});

// Crop and upload
document.getElementById('cropAndUpload').addEventListener('click', function() {
    if (cropper && selectedFile) {
        const button = this;
        const originalText = button.textContent;
        button.textContent = 'Uploading...';
        button.disabled = true;
        
        try {
            const canvas = cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('profile_photo', blob, selectedFile.name);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                
                fetch('{{ route("organizer.profile.photo") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        safeShowNotification('Profile photo updated successfully!', 'success');
                        closePhotoModal();
                        window.location.reload();
                    } else {
                        safeShowNotification('Error uploading photo: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    safeShowNotification('Error uploading photo. Please try again.', 'error');
                })
                .finally(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                });
            }, 'image/jpeg', 0.9);
        } catch (error) {
            console.error('Cropper error:', error);
            safeShowNotification('Error processing image. Please try again.', 'error');
            button.textContent = originalText;
            button.disabled = false;
        }
    }
});

// Close modals when clicking outside
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});

document.getElementById('emailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmailModal();
    }
});

document.getElementById('phoneModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhoneModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePhotoModal();
        closeEmailModal();
        closePhoneModal();
    }
});

// Auto-format OTP inputs
document.getElementById('emailOTP').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
});

document.getElementById('phoneOTP').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
});

// Fallback notification function if showNotification is not available
function fallbackNotification(message, type = 'info') {
    // Try to use the global showNotification first
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }
    
    // Fallback to alert if showNotification is not available
    alert(`${type.toUpperCase()}: ${message}`);
}

// Ensure showNotification is available
function safeShowNotification(message, type = 'info') {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        fallbackNotification(message, type);
    }
}

// Send Password Reset Link
function sendPasswordResetLink() {
    const button = document.getElementById('passwordResetBtn');
    const originalText = button.innerHTML;
    
    // Disable button and show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Sending...
    `;
    
    fetch('{{ route("organizer.profile.password.reset-link") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            safeShowNotification('Password reset link sent to your email successfully!', 'success');
            // Update button text to show success
            button.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Link Sent Successfully
            `;
            button.classList.remove('bg-primary-500', 'hover:bg-primary-600');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
        } else {
            safeShowNotification('Error: ' + data.message, 'error');
            // Reset button to original state
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        safeShowNotification('Error sending password reset link. Please try again.', 'error');
        // Reset button to original state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>
@endsection
