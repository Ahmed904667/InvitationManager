<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired - Invaro</title>
    <meta name="description" content="Account deletion link has expired">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .expired-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .expired-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .expired-icon {
            width: 80px;
            height: 80px;
            background: #f59e0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #8b2bfa;
            margin-bottom: 1rem;
        }
        .title {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-primary {
            background: #8b2bfa;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 1rem 0.5rem;
        }
        .btn-primary:hover {
            background: #7c3aed;
            transform: translateY(-2px);
        }
        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="expired-container">
        <div class="expired-card">
            <div class="logo">Invaro</div>
            
            <div class="expired-icon">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h1 class="title">Link Expired</h1>
            <p class="subtitle">
                This account deletion link has expired. For security reasons, account deletion links are only valid for 24 hours.
            </p>
            
            <p style="color: #6b7280; margin: 2rem 0;">
                If you still want to delete your account, please request a new deletion link from your account settings.
            </p>
            
            <div>
                <a href="{{ route('home') }}" class="btn-primary">
                    Return to Homepage
                </a>
            </div>
            
            <div class="footer">
                <p>If you have any questions, please contact us at <a href="mailto:support@invaro.com" style="color: #8b2bfa;">support@invaro.com</a></p>
                <p>&copy; {{ date('Y') }} Invaro. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
