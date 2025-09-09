<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Invaro</title>
    <meta name="description" content="Privacy Policy for Invaro guest management platform">
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
        <h1>Privacy Policy</h1>
        <p><strong>Last updated:</strong> {{ date('F d, Y') }}</p>
        
        <h2>1. Information We Collect</h2>
        <p>We collect information you provide directly to us, such as when you create an account, use our services, or contact us for support. This may include:</p>
        <ul>
            <li>Name and contact information (email address, phone number)</li>
            <li>Account credentials and profile information</li>
            <li>Event and guest list data</li>
            <li>Communication preferences</li>
            <li>Payment information (processed securely through third-party providers)</li>
        </ul>
        
        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Provide, maintain, and improve our services</li>
            <li>Process transactions and send related information</li>
            <li>Send technical notices, updates, security alerts, and support messages</li>
            <li>Respond to your comments, questions, and requests</li>
            <li>Monitor and analyze trends, usage, and activities</li>
            <li>Personalize and improve your experience</li>
        </ul>
        
        <h2>3. Information Sharing and Disclosure</h2>
        <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except in the following circumstances:</p>
        <ul>
            <li><strong>Service Providers:</strong> We may share information with trusted third parties who assist us in operating our website, conducting our business, or serving our users</li>
            <li><strong>Legal Requirements:</strong> We may disclose information when required by law or to protect our rights, property, or safety</li>
            <li><strong>Business Transfers:</strong> In connection with any merger, sale of assets, or acquisition of all or a portion of our business</li>
        </ul>
        
        <h2>4. Data Security</h2>
        <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet or electronic storage is 100% secure.</p>
        
        <h2>5. Data Retention</h2>
        <p>We retain your personal information for as long as necessary to provide our services and fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.</p>
        
        <h2>6. Cookies and Tracking Technologies</h2>
        <p>We use cookies and similar tracking technologies to enhance your experience on our website. You can control cookie settings through your browser preferences, but disabling cookies may affect the functionality of our services.</p>
        
        <h2>7. Third-Party Services</h2>
        <p>Our service may contain links to third-party websites or services. We are not responsible for the privacy practices of these third parties. We encourage you to read their privacy policies before providing any personal information.</p>
        
        <h2>8. Children's Privacy</h2>
        <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us.</p>
        
        <h2>9. International Data Transfers</h2>
        <p>Your information may be transferred to and processed in countries other than your own. We ensure that such transfers comply with applicable data protection laws and implement appropriate safeguards.</p>
        
        <h2>10. Your Rights</h2>
        <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
        <ul>
            <li>The right to access and receive a copy of your personal information</li>
            <li>The right to rectify or update your personal information</li>
            <li>The right to erase your personal information</li>
            <li>The right to restrict or object to certain processing activities</li>
            <li>The right to data portability</li>
        </ul>
        
        <h2>11. Changes to This Privacy Policy</h2>
        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
        
        <h2>12. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy or our privacy practices, please contact us at:</p>
        <p>
            <strong>Email:</strong> privacy@invaro.com<br>
            <strong>Website:</strong> <a href="/" class="text-primary-600 hover:text-primary-700">invaro.com</a>
        </p>
    </div>
</body>
</html>
