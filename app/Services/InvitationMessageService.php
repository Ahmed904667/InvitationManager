<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvitationMessageService
{
    private $openaiApiKey;
    private $openaiUrl = 'https://api.openai.com/v1/chat/completions';
    private $geminiApiKey;
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent';

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    /**
     * Generate invitation message using AI with fallback
     */
    public function generateInvitationMessage(string $contact, string $name, string $eventType): array
    {
        // Generate AI prompt based on contact method
        $contactMethod = $this->detectContactMethod($contact);
        if ($contactMethod === 'whatsapp') {
            $prompt = $this->generateWhatsAppPrompt($contact, $name, $eventType);
        } else {
            $prompt = $this->generatePrompt($contact, $name, $eventType);
        }

        // Try OpenAI first
        $response = $this->callOpenAI($prompt);
        $aiService = 'openai';
        
        // If OpenAI fails, try Gemini as backup
        if (!$response && $this->geminiApiKey) {
            Log::info('OpenAI failed, trying Gemini as backup');
            $response = $this->callGemini($prompt);
            $aiService = 'gemini';
        }

        // If both AI services fail, return error
        if (!$response) {
            Log::info('Both AI services failed, returning error');
            return [
                'success' => false,
                'errors' => ['AI services are currently unavailable. Please try again later.']
            ];
        }

        // Parse AI response
        $result = $this->parseAIResponse($response);
        
        if ($result['success']) {
            Log::info('AI validation successful', ['contact' => $contact, 'name' => $name, 'ai_service' => $aiService]);
            return [
                'success' => true,
                'invitation_message' => $result['invitation_message'],
                'subject' => $result['subject'] ?? null,
                'contact_method' => $contactMethod,
                'ai_service' => $aiService
            ];
        } else {
            Log::info('AI validation failed', ['errors' => $result['errors']]);
            return [
                'success' => false,
                'errors' => $result['errors']
            ];
        }
    }

    /**
     * Detect contact method
     */
    private function detectContactMethod(string $contact): string
    {
        return filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $prompt): ?string
    {
        if (!$this->openaiApiKey) {
            Log::error('OpenAI API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->openaiUrl, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            } else {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Call Gemini API
     */
    private function callGemini(string $prompt): ?string
    {
        if (!$this->geminiApiKey) {
            Log::error('Gemini API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->geminiUrl . '?key=' . $this->geminiApiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 1000,
                    'temperature' => 0.7
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            } else {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate AI prompt
     */
    private function generatePrompt(string $contact, string $name, string $eventType): string
    {
        return <<<EOT
Please generate a **complete email invitation** with subject line and personalized content based on the event type, detected language, cultural context, and appropriate tone.

CONTACT: {$contact}
NAME: {$name}
EVENT TYPE: {$eventType}

---

### INSTRUCTIONS (IMPORTANT):
1. **NEVER use placeholders or brackets** (like [Number], [Inviter's Name], [Venue], etc.) in the output. If you detect a placeholder or missing info, always fill it with a realistic, culturally-appropriate dummy value. Never output brackets or the word 'placeholder'.
2. **Detect language** from the event type or name:
   - If the event type or name is in English, respond in English.
   - If the text is in another language (e.g., Arabic, French), generate the response in that language.
3. **Cultural adaptation (be specific):**
   - For Middle Eastern events, use Arabic names, venues, and customs (e.g., Al Bustan Palace, Muscat; Omar & Layla; Arabic cuisine references).
   - For Western events, use Western names, venues, and customs (e.g., The Grand Ballroom, New York; Emily & John; Western cuisine references).
   - For other regions, adapt names, venues, and customs to fit the detected culture and country code.
4. **Adapt tone** to both the **event type** and **regional/cultural context**:
   - Example: a birthday in the Middle East should sound warm and family-oriented; a business meeting in Japan should be formal and respectful.
5. **Generate realistic dummy data** for missing details:
   - **Date:** Use a realistic future date (next 2-4 weeks)
   - **Time:** Use appropriate time for the event type (e.g., 7:00 PM for parties, 9:00 AM for business meetings)
   - **Location:** Create realistic venue names based on event type and culture
   - **Dress code:** Add appropriate dress code if relevant
   - **RSVP details:** Include realistic contact information
6. **Use emojis if appropriate** to enhance tone and clarity, but only when culturally acceptable.
7. **Use one language** - no translations.
8. **Do not include sender name prefixes** like "Inviter:" or "From:" in the message.
9. **Format as complete email** with subject line and body.
10. **Include all essential invitation elements**:
    - Compelling subject line
    - Warm greeting
    - Event details (date, time, location)
    - What to expect
    - RSVP information
    - Warm closing

---

Respond with:
SUCCESS:
SUBJECT: [compelling subject line]

[complete email invitation with all details]
EOT;
    }

    /**
     * Generate WhatsApp AI prompt
     */
    private function generateWhatsAppPrompt(string $contact, string $name, string $eventType): string
    {
        return <<<EOT
Please generate a **complete WhatsApp invitation message** with a friendly, natural tone, using proper WhatsApp formatting (short paragraphs, emojis if appropriate, no subject line, and sender/receiver roles if relevant). Adapt the message to the event type, detected language, cultural context, and appropriate tone.

CONTACT: {$contact}
NAME: {$name}
EVENT TYPE: {$eventType}

---

### INSTRUCTIONS (IMPORTANT):
1. **NEVER use placeholders or brackets** (like [Number], [Inviter's Name], [Venue], etc.) in the output. If you detect a placeholder or missing info, always fill it with a realistic, culturally-appropriate dummy value. Never output brackets or the word 'placeholder'.
2. **Detect language** from the event type or name:
   - If the event type or name is in English, respond in English.
   - If the text is in another language (e.g., Arabic, French), generate the response in that language.
3. **Cultural adaptation (be specific):**
   - For Middle Eastern events, use Arabic names, venues, and customs (e.g., Al Bustan Palace, Muscat; Omar & Layla; Arabic cuisine references).
   - For Western events, use Western names, venues, and customs (e.g., The Grand Ballroom, New York; Emily & John; Western cuisine references).
   - For other regions, adapt names, venues, and customs to fit the detected culture and country code.
4. **Adapt tone** to both the **event type** and **regional/cultural context**:
   - Example: a birthday in the Middle East should sound warm and family-oriented; a business meeting in Japan should be formal and respectful.
5. **Generate realistic dummy data** for missing details:
   - **Date:** Use a realistic future date (next 2-4 weeks)
   - **Time:** Use appropriate time for the event type (e.g., 7:00 PM for parties, 9:00 AM for business meetings)
   - **Location:** Create realistic venue names based on event type and culture
   - **Dress code:** Add appropriate dress code if relevant
   - **RSVP details:** Include realistic contact information
6. **Use emojis if appropriate** to enhance tone and clarity, but only when culturally acceptable.
7. **Use one language** - no translations.
8. **Do not include sender name prefixes** like "Inviter:" or "From:" in the message.
9. **Format as a WhatsApp message**: no subject line, use line breaks, keep it concise and friendly, and use sender/receiver roles if it makes sense.
10. **Include all essential invitation elements**:
    - Warm greeting
    - Event details (date, time, location)
    - What to expect
    - RSVP information
    - Warm closing

---

Respond with:
SUCCESS:
[complete WhatsApp invitation message]
EOT;
    }

    /**
     * Parse AI response
     */
    private function parseAIResponse(string $response): array
    {
        $response = trim($response);
        // Log the raw AI response for debugging
        \Log::info('Raw AI response:', ['response' => $response]);

        if (str_starts_with($response, 'SUCCESS:')) {
            $content = trim(substr($response, 8));
            
            // Remove any "Inviter:" prefixes that might be added by AI
            $content = preg_replace('/^Inviter:\s*/i', '', $content);
            $content = preg_replace('/^From:\s*/i', '', $content);
            
            // Check if response contains subject line
            if (preg_match('/SUBJECT:\s*(.+?)(?:\n|$)/i', $content, $subjectMatch)) {
                $subject = trim($subjectMatch[1]);
                $body = trim(preg_replace('/SUBJECT:\s*.+?(?:\n|$)/i', '', $content));
                return [
                    'success' => true,
                    'invitation_message' => $body,
                    'subject' => $subject
                ];
            } else {
                // Fallback to old format
                return [
                    'success' => true,
                    'invitation_message' => $content
                ];
            }
        } elseif (str_starts_with($response, 'ERROR:')) {
            $errorMessage = 'the erreor in line 299 php' + trim(substr($response, 6));
            return [
                'success' => false,
                'errors' => [$errorMessage]
            ];
        } else {
            // If AI response format is unclear, treat as error
            return [
                'success' => false,
                'errors' => ['AI response was not understood. Please check your input.'],
                'ai_raw_response' => $response
            ];
        }
    }

    /**
     * Generate fallback invitation message
     */
    private function generateFallbackMessage(string $name, string $eventType): string
    {
        $eventContent = $this->getEventSpecificContent($eventType);
        
        return "Hi {$name},

{$eventContent}

Best regards,
Your Host";
    }

    /**
     * Get event-specific content
     */
    private function getEventSpecificContent(string $eventType): string
    {
        $eventType = strtolower($eventType);
        
        if (str_contains($eventType, 'birthday')) {
            return "You're invited to celebrate a special birthday! Join us for cake, fun, and great memories.";
        } elseif (str_contains($eventType, 'wedding')) {
            return "We're excited to invite you to our wedding celebration. Your presence would mean the world to us.";
        } elseif (str_contains($eventType, 'party')) {
            return "You're invited to an amazing party! Come join us for food, drinks, and great company.";
        } elseif (str_contains($eventType, 'meeting')) {
            return "You're invited to an important meeting. We look forward to your valuable input and participation.";
        } elseif (str_contains($eventType, 'conference')) {
            return "You're invited to attend our conference. Don't miss this opportunity to learn and network.";
        } else {
            return "You're invited to our {$eventType}! We can't wait to see you there.";
        }
    }
} 