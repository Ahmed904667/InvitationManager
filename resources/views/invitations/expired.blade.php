<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Expired</title>
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
                    <i class="fas fa-times-circle text-red-500 text-4xl"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Invitation Expired
                </h2>
                
                <p class="text-lg text-gray-600 mb-8">
                    This invitation has been cancelled and is no longer valid.
                </p>
                
                <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                    <div class="text-center">
                        <i class="fas fa-calendar-times text-red-400 text-2xl mb-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            Invitation Cancelled
                        </h3>
                        <p class="text-gray-600 text-sm">
                            The event organizer has cancelled this invitation. 
                            If you have any questions, please contact the event organizer directly.
                        </p>
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



