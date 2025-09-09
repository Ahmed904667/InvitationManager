<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deleted - Invaro</title>
    <meta name="description" content="Your Invaro account has been successfully deleted">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .confirmation-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
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
        .info-box {
            background: #f3f4f6;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }
        .info-box h3 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .info-box ul {
            color: #6b7280;
            list-style: none;
            padding: 0;
        }
        .info-box li {
            padding: 0.5rem 0;
            position: relative;
            padding-left: 1.5rem;
        }
        .info-box li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
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
        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 1rem 0.5rem;
        }
        .btn-secondary:hover {
            background: #4b5563;
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
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="logo">Invaro</div>
            
            <div class="success-icon">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="title">Account Successfully Deleted</h1>
            <p class="subtitle">
                Your Invaro account and all associated data have been permanently removed from our systems.
            </p>
            
            <div class="info-box">
                <h3>What was deleted:</h3>
                <ul>
                    <li>Your profile and account information</li>
                    <li>All event data and guest lists</li>
                    <li>Your settings and preferences</li>
                    <li>All associated data and history</li>
                </ul>
            </div>
            
            <p style="color: #6b7280; margin: 2rem 0;">
                Thank you for using Invaro. We're sorry to see you go, but we respect your decision.
            </p>
            
            <div>
                <a href="{{ route('home') }}" class="btn-primary">
                    Return to Homepage
                </a>
                <a href="{{ route('register') }}" class="btn-secondary">
                    Create New Account
                </a>
            </div>
            
            <div class="footer">
                <p>If you have any questions or concerns, please contact us at <a href="mailto:support@invaro.com" style="color: #8b2bfa;">support@invaro.com</a></p>
                <p>&copy; {{ date('Y') }} Invaro. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
