<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrialEventGenerationService
{
    /**
     * Generate sample event data based on event type using AI
     */
    public function generateSampleEventData(string $eventType, string $organizerName): array
    {
        try {
            // Use Google Gemini API to generate realistic event data
            $apiKey = config('services.gemini.api_key');
            if (!$apiKey) {
                Log::warning('Gemini API key not configured, using fallback data');
                return $this->getFallbackEventData($eventType, $organizerName);
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are an expert event planner. Generate realistic sample event data based on the event type provided. Return ONLY a valid JSON object with the following structure: {\"name\": \"Event Name\", \"description\": \"Event description\", \"venue_name\": \"Venue Name\", \"venue_address\": \"Full address\", \"start_date\": \"YYYY-MM-DD HH:MM:SS\", \"end_date\": \"YYYY-MM-DD HH:MM:SS\", \"invitation_title\": \"Invitation Title\", \"invitation_subtitle\": \"Invitation Subtitle\", \"invitation_message\": \"Personal invitation message\", \"rsvp_message\": \"RSVP instructions\", \"additional_information\": \"Additional event details\", \"parking_info\": \"Parking information\"}. Make the event realistic and professional.\n\nGenerate sample event data for a '{$eventType}' event organized by {$organizerName}. Make it realistic and engaging."
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Clean the response to extract JSON
                $jsonStart = strpos($content, '{');
                $jsonEnd = strrpos($content, '}') + 1;
                
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart);
                    $eventData = json_decode($jsonString, true);
                    
                    if ($eventData && is_array($eventData)) {
                        // Set default dates (next Saturday from now)
                        $nextSaturday = now()->next('Saturday')->setTime(18, 0); // 6 PM
                        $eventData['start_date'] = $eventData['start_date'] ?? $nextSaturday->format('Y-m-d H:i:s');
                        $eventData['end_date'] = $eventData['end_date'] ?? $nextSaturday->addHours(3)->format('Y-m-d H:i:s');
                        
                        return $eventData;
                    }
                }
            }
            
            Log::warning('Failed to generate AI event data', [
                'event_type' => $eventType,
                'organizer_name' => $organizerName,
                'response' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
        }

        // Fallback to predefined templates
        return $this->getFallbackEventData($eventType, $organizerName);
    }

    /**
     * Get fallback event data when AI generation fails
     */
    private function getFallbackEventData(string $eventType, string $organizerName): array
    {
        $templates = [
            'wedding' => [
                'name' => 'Wedding Celebration',
                'description' => 'Join us for a beautiful wedding celebration filled with love, joy, and unforgettable memories.',
                'venue_name' => 'Garden Manor',
                'venue_address' => '123 Garden Lane, Beautiful City, BC 12345',
                'invitation_title' => 'You\'re Invited to Our Wedding',
                'invitation_subtitle' => 'Celebrate with us as we begin our journey together',
                'invitation_message' => 'We would be honored to have you join us for our special day.',
                'rsvp_message' => 'Please RSVP by [date] to help us plan for your attendance.',
                'additional_information' => 'Dress code: Semi-formal. Ceremony will be followed by dinner and dancing.',
                'parking_info' => 'Complimentary valet parking available.',
            ],
            'birthday' => [
                'name' => 'Birthday Celebration',
                'description' => 'Come celebrate another year of life, laughter, and wonderful memories with us!',
                'venue_name' => 'Party Palace',
                'venue_address' => '456 Celebration Street, Fun City, FC 67890',
                'invitation_title' => 'Birthday Party Invitation',
                'invitation_subtitle' => 'Let\'s celebrate together!',
                'invitation_message' => 'We\'d love to celebrate with you on this special day.',
                'rsvp_message' => 'Please let us know if you can make it by [date].',
                'additional_information' => 'Casual attire. Food, drinks, and entertainment provided.',
                'parking_info' => 'Free parking available in the venue lot.',
            ],
            'corporate' => [
                'name' => 'Corporate Event',
                'description' => 'Join us for an evening of networking, presentations, and professional development.',
                'venue_name' => 'Business Center',
                'venue_address' => '789 Corporate Plaza, Business District, BD 54321',
                'invitation_title' => 'Corporate Event Invitation',
                'invitation_subtitle' => 'Professional networking and development',
                'invitation_message' => 'We invite you to join us for this important corporate event.',
                'rsvp_message' => 'Please confirm your attendance by [date].',
                'additional_information' => 'Business attire required. Refreshments will be served.',
                'parking_info' => 'Valet parking available for $10.',
            ],
            'conference' => [
                'name' => 'Annual Conference',
                'description' => 'Join industry leaders and experts for a day of learning, networking, and innovation.',
                'venue_name' => 'Convention Center',
                'venue_address' => '321 Conference Blvd, Tech City, TC 98765',
                'invitation_title' => 'Conference Invitation',
                'invitation_subtitle' => 'Learn, network, and grow',
                'invitation_message' => 'We\'re excited to have you join us for this educational event.',
                'rsvp_message' => 'Please register by [date] to secure your spot.',
                'additional_information' => 'Professional attire. Lunch and materials included.',
                'parking_info' => 'Complimentary parking in the convention center garage.',
            ],
            'graduation' => [
                'name' => 'Graduation Ceremony',
                'description' => 'Celebrate the achievements and future success of our graduates.',
                'venue_name' => 'University Auditorium',
                'venue_address' => '654 Education Avenue, College Town, CT 13579',
                'invitation_title' => 'Graduation Invitation',
                'invitation_subtitle' => 'Celebrating achievements and new beginnings',
                'invitation_message' => 'Join us in celebrating this milestone achievement.',
                'rsvp_message' => 'Please confirm your attendance by [date].',
                'additional_information' => 'Formal attire. Reception to follow the ceremony.',
                'parking_info' => 'Free parking available in university lots.',
            ]
        ];

        $template = $templates[strtolower($eventType)] ?? $templates['corporate'];
        
        // Set default dates (next Saturday from now)
        $nextSaturday = now()->next('Saturday')->setTime(18, 0); // 6 PM
        $template['start_date'] = $nextSaturday->format('Y-m-d H:i:s');
        $template['end_date'] = $nextSaturday->addHours(3)->format('Y-m-d H:i:s');

        return $template;
    }

    /**
     * Extract JSON from AI response
     */
    private function extractJsonFromResponse(string $content): ?array
    {
        // Try to find JSON in the response
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}') + 1;
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart);
            $data = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }
        
        return null;
    }
}