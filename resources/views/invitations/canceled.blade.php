<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Canceled</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-red-100 mb-6">
                    <i class="fas fa-calendar-times text-red-500 text-4xl"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Event Canceled
                </h2>
                
                <p class="text-lg text-gray-600 mb-8">
                    We're sorry to inform you that this event has been canceled.
                </p>
                
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            {{ $event->name }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            The event organizer has canceled this event. We apologize for any inconvenience this may cause.
                        </p>
                        
                        @if($event->cancellation_reason)
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-gray-800 mb-2">Reason for Cancellation:</h4>
                                <p class="text-gray-600 text-sm">{{ $event->cancellation_reason }}</p>
                            </div>
                        @endif
                        
                        <!-- Organizer Contact Information -->
                        @if($event->user)
                        <div class="bg-blue-50 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                                Contact the Organizer
                            </h4>
                            <div class="space-y-2">
                                @if($event->user->email)
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-envelope text-gray-400 w-4 mr-3"></i>
                                    <a href="mailto:{{ $event->user->email }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                                        {{ $event->user->email }}
                                    </a>
                                </div>
                                @endif
                                @if($event->user->phone)
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-phone text-gray-400 w-4 mr-3"></i>
                                    <a href="tel:{{ $event->user->phone }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                                        {{ $event->user->phone }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <div class="text-xs text-gray-500">
                            Canceled on {{ \Carbon\Carbon::parse($event->cancelled_at)->format('M j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Return Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
