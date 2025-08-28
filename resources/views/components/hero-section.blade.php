<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Hero Section</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <style>
        :root {
            --primary-500: #8b5cf6;
            --primary-600: #7c3aed;
            --primary-700: #6d28d9;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --border-primary: #e5e7eb;
        }

        [data-theme="dark"] {
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --bg-primary: #1f2937;
            --bg-secondary: #374151;
            --border-primary: #4b5563;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #faf5ff 100%);
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        [data-theme="dark"] .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e1b4b 100%);
        }

        .hero-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }
        }

        /* Left Column Content */
        .hero-content {
            z-index: 10;
            position: relative;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .hero-gradient-text {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-700) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 200% 200%;
            animation: gradient-shift 3s ease-in-out infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-description {
            font-size: 1.25rem;
            line-height: 1.6;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-weight: 400;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        @media (max-width: 1024px) {
            .hero-buttons {
                justify-content: center;
            }
        }

        .hero-btn-primary, .hero-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            min-width: 180px;
        }

        .hero-btn-primary {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            color: white;
            box-shadow: 0 4px 14px 0 rgba(139, 43, 250, 0.3);
            border: none;
        }

        .hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(139, 43, 250, 0.4);
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
        }

        .hero-btn-secondary {
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 2px solid var(--border-primary);
        }

        .hero-btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--primary-500);
            color: var(--primary-500);
            transform: translateY(-2px);
            box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.1);
        }

        /* Right Column - Trial Form */
        .trial-section {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        [data-theme="dark"] .trial-section {
            background: rgba(31, 41, 55, 0.8);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }

        .trial-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .trial-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .trial-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.5;
        }

        .trial-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-primary);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(139, 43, 250, 0.1);
        }

        .contact-toggle {
            display: flex;
            background: var(--bg-secondary);
            border-radius: 0.75rem;
            padding: 0.25rem;
            border: 1px solid var(--border-primary);
        }

        .contact-option {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .contact-option.active {
            background: var(--primary-500);
            color: white;
            box-shadow: 0 2px 4px rgba(139, 43, 250, 0.2);
        }

        .trial-btn {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            color: white;
            padding: 1.25rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px 0 rgba(139, 43, 250, 0.3);
        }

        .trial-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(139, 43, 250, 0.4);
        }

        .trial-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .success-message {
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            text-align: center;
            font-weight: 500;
            display: none;
        }

        /* Decorative Elements */
        .hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.7;
            mix-blend-mode: multiply;
            pointer-events: none;
        }

        .hero-blob-1 {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            width: 20rem;
            height: 20rem;
            top: 10%;
            left: -5%;
            animation: blob 7s infinite;
        }

        .hero-blob-2 {
            background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
            width: 18rem;
            height: 18rem;
            top: 20%;
            right: -5%;
            animation: blob 7s infinite 2s;
        }

        .hero-blob-3 {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            width: 16rem;
            height: 16rem;
            bottom: 20%;
            left: 20%;
            animation: blob 7s infinite 4s;
        }

        [data-theme="dark"] .hero-blob-1 {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
        }

        [data-theme="dark"] .hero-blob-2 {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        }

        [data-theme="dark"] .hero-blob-3 {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        }

        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
            33% { transform: translate(30px, -50px) scale(1.1) rotate(120deg); }
            66% { transform: translate(-20px, 20px) scale(0.9) rotate(240deg); }
            100% { transform: translate(0px, 0px) scale(1) rotate(360deg); }
        }

        /* Features highlight */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .feature-icon {
            width: 1rem;
            height: 1rem;
            color: var(--primary-500);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-container {
                padding: 1.5rem 1rem;
                gap: 2rem;
            }

            .trial-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1.125rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero-blob {
                width: 12rem !important;
                height: 12rem !important;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .hero-blob, .hero-gradient-text {
                animation: none;
            }
            
            .hero-btn-primary:hover, .hero-btn-secondary:hover, .trial-btn:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="hero-container">
            <!-- Left Column - Content -->
            <div class="hero-content">
                <h1 class="hero-title">
                    Professional
                    <span class="hero-gradient-text">
                        Guest Management
                    </span>
                    Made Simple
                </h1>
                <p class="hero-description">
                    Streamline your events with our comprehensive guest management platform. 
                    Perfect for organizers, scanners, and administrators who need reliable, 
                    efficient tools to manage guest lists and check-ins.
                </p>
                <div class="hero-buttons">
                    <a href="#" class="hero-btn-primary">
                        Start Now!
                    </a>
                    <a href="#features" class="hero-btn-secondary">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Right Column - Trial Form -->
            <div class="trial-section">
                <div class="trial-header">
                    <h2 class="trial-title">Try It Free</h2>
                    <p class="trial-subtitle">
                        Get a sample invitation sent to your WhatsApp or email to see our platform in action
                    </p>
                </div>

                <form class="trial-form" id="trialForm">

                    
                    <div class="form-group">
                        <input 
                            type="tel" 
                            id="whatsapp" 
                            name="whatsapp"
                            class="form-input" 
                            placeholder="+1 234 567 8900"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="eventType">Event Type</label>
                        <select id="eventType" name="eventType" class="form-select" required>
                            <option value="">Select event type</option>
                            <option value="wedding">Wedding</option>
                            <option value="birthday">Birthday Party</option>
                            <option value="corporate">Corporate Event</option>
                            <option value="conference">Conference</option>
                            <option value="graduation">Graduation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <button type="submit" class="trial-btn" id="submitBtn">
                        Send Sample Invitation
                    </button>

                    <div class="success-message" id="successMessage">
                        
                    </div>
                </form>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="hero-blob hero-blob-1"></div>
        <div class="hero-blob hero-blob-2"></div>
        <div class="hero-blob hero-blob-3"></div>
    </div>
</body>
</html>