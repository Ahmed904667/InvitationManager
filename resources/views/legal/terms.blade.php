<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Invaro</title>
    <meta name="description" content="Terms and Conditions for Invaro guest management platform">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/Logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo.jpg') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .legal-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            line-height: 1.6;
        }
        .legal-content h1 {
            color: #8b2bfa;
            margin-bottom: 1rem;
        }
        .legal-content h2 {
            color: #6d28d9;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .legal-content p {
            margin-bottom: 1rem;
        }
        .legal-content ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .legal-content li {
            margin-bottom: 0.5rem;
        }
        .close-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: #8b2bfa;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
        }
        .close-btn:hover {
            background: #7c3aed;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <button class="close-btn" onclick="window.close()">Close</button>
    
    <div class="legal-content bg-white dark:bg-gray-800 rounded-lg shadow-lg">
        <h1>Terms and Conditions</h1>
        <p><strong>Last updated:</strong> {{ date('F d, Y') }}</p>
        
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using Invaro ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
        
        <h2>2. Use License</h2>
        <p>Permission is granted to temporarily use Invaro for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
        <ul>
            <li>modify or copy the materials</li>
            <li>use the materials for any commercial purpose or for any public display</li>
            <li>attempt to reverse engineer any software contained on the website</li>
            <li>remove any copyright or other proprietary notations from the materials</li>
        </ul>
        
        <h2>3. User Accounts</h2>
        <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities that occur under your account.</p>
        
        <h2>4. Prohibited Uses</h2>
        <p>You may not use our service:</p>
        <ul>
            <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
            <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
            <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
            <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
            <li>To submit false or misleading information</li>
        </ul>
        
        <h2>5. Content</h2>
        <p>Our service allows you to post, link, store, share and otherwise make available certain information, text, graphics, videos, or other material. You are responsible for the content that you post to the service, including its legality, reliability, and appropriateness.</p>
        
        <h2>6. Privacy Policy</h2>
        <p>Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the service, to understand our practices.</p>
        
        <h2>7. Termination</h2>
        <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
        
        <h2>8. Disclaimer</h2>
        <p>The information on this website is provided on an "as is" basis. To the fullest extent permitted by law, this Company excludes all representations, warranties, conditions and terms relating to our website and the use of this website.</p>
        
        <h2>9. Limitation of Liability</h2>
        <p>In no event shall Invaro, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the service.</p>
        
        <h2>10. Governing Law</h2>
        <p>These Terms shall be interpreted and governed by the laws of the jurisdiction in which Invaro operates, without regard to its conflict of law provisions.</p>
        
        <h2>11. Changes to Terms</h2>
        <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days notice prior to any new terms taking effect.</p>
        
        <h2>12. Contact Information</h2>
        <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
        <p>
            <strong>Email:</strong> support@invaro.com<br>
            <strong>Website:</strong> <a href="/" class="text-primary-600 hover:text-primary-700">invaro.com</a>
        </p>
    </div>
</body>
</html>
