<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Invitation Expired</title>
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
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-full bg-orange-100 mb-6">
                    <i class="fas fa-clock text-orange-500 text-4xl"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Trial Invitation Expired
                </h2>
                
                <p class="text-lg text-gray-600 mb-8">
                    This trial invitation has expired. Trial invitations are valid for 7 days from creation.
                </p>
                
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
                    <div class="text-center">
                        <i class="fas fa-calendar-times text-orange-400 text-2xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            Trial Demo Expired
                        </h3>
                        <p class="text-gray-600 text-sm">
                            The trial invitation for "{{ $trial->event_type }}" created by {{ $trial->name }} has expired. 
                            You can request a new trial demo or sign up to create your own events!
                        </p>
                    </div>
                </div>
                
                <div class="mt-8 space-y-4">
                    <a href="{{ route('home') }}" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                        <i class="fas fa-rocket mr-2"></i>
                        Request New Trial
                    </a>
                    <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Sign Up Free
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
