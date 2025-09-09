@extends('layouts.organizer')

@section('title', 'Help & Support')

@section('content')
<div class="min-h-screen bg-primary py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-primary-600 mb-4">Help & Support</h1>
            <p class="text-secondary text-lg">Get help with Invaro and find answers to common questions</p>
        </div>

        <!-- Quick Help Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-secondary rounded-xl shadow-sm border border-white/20 p-6 text-center">
                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-primary mb-2">FAQ</h3>
                <p class="text-secondary text-sm">Find answers to frequently asked questions</p>
            </div>

            <div class="bg-secondary rounded-xl shadow-sm border border-white/20 p-6 text-center">
                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-primary mb-2">Contact Us</h3>
                <p class="text-secondary text-sm">Get in touch with our support team</p>
            </div>

            <div class="bg-secondary rounded-xl shadow-sm border border-white/20 p-6 text-center">
                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-primary mb-2">Documentation</h3>
                <p class="text-secondary text-sm">Learn how to use Invaro effectively</p>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8">
            <div class="px-6 py-4 border-b border-white/20">
                <h2 class="text-xl font-semibold text-primary-600">Frequently Asked Questions</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <!-- FAQ Item 1 -->
                    <div class="border-b border-white/10 pb-4">
                        <button class="flex items-center justify-between w-full text-left" onclick="toggleFAQ('faq1')">
                            <h3 class="text-lg font-medium text-primary">How do I create my first event?</h3>
                            <svg id="faq1-icon" class="w-5 h-5 text-primary-600 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq1-content" class="hidden mt-3 text-secondary">
                            <p>To create your first event:</p>
                            <ol class="list-decimal list-inside mt-2 space-y-1">
                                <li>Click "Create Event" on your dashboard</li>
                                <li>Fill in your event details (name, date, location)</li>
                                <li>Add your guest list</li>
                                <li>Customize your invitation message</li>
                                <li>Send invitations to your guests</li>
                            </ol>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="border-b border-white/10 pb-4">
                        <button class="flex items-center justify-between w-full text-left" onclick="toggleFAQ('faq2')">
                            <h3 class="text-lg font-medium text-primary">How do I manage my guest list?</h3>
                            <svg id="faq2-icon" class="w-5 h-5 text-primary-600 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq2-content" class="hidden mt-3 text-secondary">
                            <p>You can manage your guest list by:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Adding guests manually or importing from a CSV file</li>
                                <li>Organizing guests into groups</li>
                                <li>Tracking RSVP responses</li>
                                <li>Sending reminders to guests who haven't responded</li>
                                <li>Exporting your guest list for other purposes</li>
                            </ul>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="border-b border-white/10 pb-4">
                        <button class="flex items-center justify-between w-full text-left" onclick="toggleFAQ('faq3')">
                            <h3 class="text-lg font-medium text-primary">How do I send invitations?</h3>
                            <svg id="faq3-icon" class="w-5 h-5 text-primary-600 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq3-content" class="hidden mt-3 text-secondary">
                            <p>To send invitations:</p>
                            <ol class="list-decimal list-inside mt-2 space-y-1">
                                <li>Go to your event's guest list</li>
                                <li>Click "Send Invitations"</li>
                                <li>Choose your preferred method (Email or WhatsApp)</li>
                                <li>Customize your message if needed</li>
                                <li>Select which guests to send to</li>
                                <li>Click "Send" to deliver your invitations</li>
                            </ol>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="border-b border-white/10 pb-4">
                        <button class="flex items-center justify-between w-full text-left" onclick="toggleFAQ('faq4')">
                            <h3 class="text-lg font-medium text-primary">Can I track RSVP responses?</h3>
                            <svg id="faq4-icon" class="w-5 h-5 text-primary-600 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq4-content" class="hidden mt-3 text-secondary">
                            <p>Yes! Invaro provides comprehensive RSVP tracking:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Real-time RSVP status updates</li>
                                <li>Visual dashboard showing response rates</li>
                                <li>Automatic reminders for non-responders</li>
                                <li>Export RSVP data for planning purposes</li>
                                <li>Guest check-in at the event</li>
                            </ul>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="pb-4">
                        <button class="flex items-center justify-between w-full text-left" onclick="toggleFAQ('faq5')">
                            <h3 class="text-lg font-medium text-primary">How do I change my account settings?</h3>
                            <svg id="faq5-icon" class="w-5 h-5 text-primary-600 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="faq5-content" class="hidden mt-3 text-secondary">
                            <p>To update your account settings:</p>
                            <ol class="list-decimal list-inside mt-2 space-y-1">
                                <li>Go to Settings from your dashboard</li>
                                <li>Update your profile information</li>
                                <li>Change notification preferences</li>
                                <li>Update your password if needed</li>
                                <li>Manage your account security</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Support Section -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20 mb-8">
            <div class="px-6 py-4 border-b border-white/20">
                <h2 class="text-xl font-semibold text-primary-600">Contact Support</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-medium text-primary mb-4">Get in Touch</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-primary font-medium">Email Support</p>
                                    <p class="text-secondary text-sm">support@invaro.com</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-primary font-medium">Response Time</p>
                                    <p class="text-secondary text-sm">Within 24 hours</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-primary font-medium">Business Hours</p>
                                    <p class="text-secondary text-sm">Monday - Friday, 9 AM - 6 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div>
                        <h3 class="text-lg font-medium text-primary mb-4">Send us a Message</h3>
                        <form class="space-y-4">
                            <div>
                                <label for="subject" class="block text-sm font-medium text-primary mb-2">Subject</label>
                                <select id="subject" name="subject" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select a topic</option>
                                    <option value="general">General Question</option>
                                    <option value="technical">Technical Issue</option>
                                    <option value="billing">Billing Question</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="bug">Bug Report</option>
                                </select>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-primary mb-2">Message</label>
                                <textarea id="message" name="message" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Describe your question or issue..."></textarea>
                            </div>

                            <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-secondary rounded-xl shadow-sm border border-white/20">
            <div class="px-6 py-4 border-b border-white/20">
                <h2 class="text-xl font-semibold text-primary-600">Quick Links</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('organizer.dashboard') }}" class="flex items-center p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors duration-200">
                        <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        <span class="text-primary font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('organizer.settings') }}" class="flex items-center p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors duration-200">
                        <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-primary font-medium">Settings</span>
                    </a>

                    <a href="{{ route('organizer.guest-lists.index') }}" class="flex items-center p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors duration-200">
                        <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span class="text-primary font-medium">Guest Lists</span>
                    </a>

                    <a href="{{ route('organizer.profile.show') }}" class="flex items-center p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors duration-200">
                        <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-primary font-medium">Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFAQ(faqId) {
    const content = document.getElementById(faqId + '-content');
    const icon = document.getElementById(faqId + '-icon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection
