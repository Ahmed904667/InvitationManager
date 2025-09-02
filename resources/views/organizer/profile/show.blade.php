@extends('layouts.organizer')

@section('title', 'Profile')

@section('content')
<div class="min-h-screen bg-primary py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-primary-600">Profile</h1>
                <p class="mt-2 text-secondary">Your account information and preferences</p>
            </div>
            <a href="{{ route('organizer.profile.edit') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Edit Profile
            </a>
        </div>

        <!-- Profile Photo Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
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
                    <h2 class="text-xl font-semibold text-primary mb-2">{{ $user->name }}</h2>
                    <p class="text-secondary mb-1">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="text-secondary">{{ $user->phone }}</p>
                    @endif
                    @if($user->bio)
                        <p class="text-secondary mt-2">{{ $user->bio }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Profile Information Display -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Profile Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Full Name</label>
                    <p class="text-primary font-medium">{{ $user->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Email Address</label>
                    <p class="text-primary">{{ $user->email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Phone Number</label>
                    <p class="text-primary">{{ $user->phone ?: 'Not provided' }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-secondary mb-1">Bio</label>
                    <p class="text-primary">{{ $user->bio ?: 'No bio provided' }}</p>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-primary mb-4">Account Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Role</label>
                    <p class="text-primary font-medium">{{ $user->getRoleDisplayName() }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Member Since</label>
                    <p class="text-primary">{{ $user->created_at->format('M d, Y') }}</p>
                </div>

                @if($user->last_login_at)
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">Last Login</label>
                        <p class="text-primary">{{ $user->last_login_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                @endif

                @if($user->timezone)
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">Timezone</label>
                        <p class="text-primary">{{ $user->timezone }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
