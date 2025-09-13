<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Completed</title>
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
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-green-100 mb-6">
                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Event Completed
                </h2>
                
                <p class="text-lg text-gray-600 mb-8">
                    This event has already concluded. Thank you for your interest!
                </p>
                
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                    <div class="text-center">
                        <i class="fas fa-calendar-check text-green-400 text-2xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            {{ $event->name }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            The event has successfully concluded. We hope you had a great time if you attended, 
                            and we look forward to seeing you at future events!
                        </p>
                        
                        @if($event->end_date)
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-gray-800 mb-2">Event Details:</h4>
                                <p class="text-gray-600 text-sm">
                                    <strong>Started:</strong> {{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}<br>
                                    <strong>Ended:</strong> {{ \Carbon\Carbon::parse($event->end_date)->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-gray-800 mb-2">Event Details:</h4>
                                <p class="text-gray-600 text-sm">
                                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="text-xs text-gray-500">
                            Event completed on {{ \Carbon\Carbon::parse($event->end_date ?? $event->start_date)->format('M j, Y') }}
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
