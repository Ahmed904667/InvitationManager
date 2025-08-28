<?php

namespace App\Services;

use App\Shared\Models\Event;
use App\Shared\Models\Guest;
use App\Shared\Models\GuestList;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EventMessageGenerationService
{
    private $openaiApiKey;
    private $geminiApiKey;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    /**
     * Generate messages for an event based on the customization mode
     */
    public function generateMessages(
        Event $event,
        string $mode,
        array $guestData,
        array $userInstructions = [],
        ?string $preferredLanguage = null
    ): array {
        try {
            $eventContext = $this->buildEventContext($event);
            
            switch ($mode) {
                case 'general':
                    return $this->generateGeneralMessage($eventContext, $userInstructions, $preferredLanguage);
                
                case 'group':
                    return $this->generateGroupMessages($eventContext, $guestData, $userInstructions, $preferredLanguage);
                
                case 'per_guest':
                    return $this->generatePerGuestMessages($eventContext, $guestData, $userInstructions, $preferredLanguage);
                
                case 'list':
                    return $this->generateListMessages($eventContext, $guestData, $userInstructions, $preferredLanguage);
                
                default:
                    throw new \InvalidArgumentException("Invalid mode: {$mode}");
            }
        } catch (\Exception $e) {
            Log::error('EventMessageGenerationService error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'mode' => $mode,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to generate messages: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate a single general message for all guests
     */
    private function generateGeneralMessage(array $eventContext, array $userInstructions, ?string $preferredLanguage): array
    {
        $prompt = $this->buildGeneralPrompt($eventContext, $userInstructions);
        $message = $this->callAI($prompt, $preferredLanguage);
        
        return [
            'success' => true,
            'mode' => 'general',
            'general_message' => $message,
            'ai_generated' => true
        ];
    }

    /**
     * Generate messages for each group
     */
    private function generateGroupMessages(array $eventContext, array $guestData, array $userInstructions, ?string $preferredLanguage): array
    {
        $groupMessages = [];
        $guestMessages = [];
        
        Log::info('🔵 [EVENT MESSAGE SERVICE] Starting group message generation', [
            'user_instructions' => $userInstructions,
            'guest_data_keys' => array_keys($guestData),
            'guest_data_structure' => array_map(function($listData) {
                return [
                    'has_groups' => isset($listData['groups']),
                    'groups_count' => isset($listData['groups']) ? count($listData['groups']) : 0,
                    'group_ids' => isset($listData['groups']) ? array_keys($listData['groups']) : []
                ];
            }, $guestData)
        ]);
        
        // Find the specific group that was requested
        $requestedGroupKey = null;
        foreach ($userInstructions['groups'] as $key => $tone) {
            $requestedGroupKey = $key;
            break; // Take the first (and should be only) group requested
        }
        
        if (!$requestedGroupKey) {
            Log::warning('❌ [EVENT MESSAGE SERVICE] No specific group requested');
            return [
                'success' => false,
                'error' => 'No specific group requested'
            ];
        }
        
        // Parse the group key to get listId and groupId
        $parts = explode('_', $requestedGroupKey);
        $requestedListId = $parts[0];
        $requestedGroupId = $parts[1];
        
        Log::info('🔵 [EVENT MESSAGE SERVICE] Looking for group', [
            'requested_list_id' => $requestedListId,
            'requested_group_id' => $requestedGroupId
        ]);
        
        foreach ($guestData as $listId => $listData) {
            Log::info('🔵 [EVENT MESSAGE SERVICE] Processing list', [
                'list_id' => $listId,
                'requested_list_id' => $requestedListId,
                'matches' => $listId == $requestedListId
            ]);
            
            // Only process the requested list (handle both string and integer IDs)
            if ((string)$listId !== (string)$requestedListId) {
                continue;
            }
            
            // Handle groups within this list
            if (isset($listData['groups'])) {
                Log::info('🔵 [EVENT MESSAGE SERVICE] Found groups in list', [
                    'groups_count' => count($listData['groups']),
                    'group_ids' => array_keys($listData['groups'])
                ]);
                
                foreach ($listData['groups'] as $groupId => $groupData) {
                    Log::info('🔵 [EVENT MESSAGE SERVICE] Processing group', [
                        'group_id' => $groupId,
                        'requested_group_id' => $requestedGroupId,
                        'matches' => $groupId == $requestedGroupId
                    ]);
                    
                    // Only process the requested group (handle both string and integer IDs)
                    if ((string)$groupId !== (string)$requestedGroupId) {
                        continue;
                    }
                    
                    $groupName = $groupData['group_name'];
                    $key = $listId . '_' . $groupId;
                    $groupInstructions = $userInstructions['groups'][$key] ?? $userInstructions['default'] ?? '';
                    
                    Log::info('🔵 [EVENT MESSAGE SERVICE] Generating message for group', [
                        'group_name' => $groupName,
                        'group_instructions' => $groupInstructions
                    ]);
                    
                    $prompt = $this->buildGroupPrompt($eventContext, $groupName, $groupInstructions);
                    $groupTemplate = $this->callAI($prompt, $preferredLanguage);
                    
                    // Store the group template
                    $groupMessages[$key] = $groupTemplate;
                    
                    // Process each guest in this group
                    foreach ($groupData['guests'] as $guest) {
                        // Replace event placeholders in the template
                        $processedTemplate = $this->replaceEventPlaceholders($groupTemplate, $eventContext);
                        
                        // Replace {name} with actual guest name
                        $guestMessage = str_replace('{name}', $guest->name, $processedTemplate);
                        
                        // Store individual guest message
                        $guestMessages[$guest->id] = $guestMessage;
                    }
                    
                    Log::info('🔵 [EVENT MESSAGE SERVICE] Generated messages', [
                        'group_messages_count' => count($groupMessages),
                        'guest_messages_count' => count($guestMessages)
                    ]);
                }
            } else {
                Log::info('🔵 [EVENT MESSAGE SERVICE] No groups found, using list as group');
                // Fallback: generate for the list itself (only if it's the requested list)
                $groupName = $listData['list_name'];
                $groupInstructions = $userInstructions['groups'][$listId] ?? $userInstructions['default'] ?? '';
                
                $prompt = $this->buildGroupPrompt($eventContext, $groupName, $groupInstructions);
                $message = $this->callAI($prompt, $preferredLanguage);
                
                $groupMessages[$listId] = $message;
            }
        }
        
        Log::info('🔵 [EVENT MESSAGE SERVICE] Final result', [
            'success' => true,
            'group_messages_count' => count($groupMessages),
            'guest_messages_count' => count($guestMessages)
        ]);
        
        return [
            'success' => true,
            'mode' => 'group',
            'group_messages' => $groupMessages,
            'per_guest_messages' => $guestMessages,
            'ai_generated' => true
        ];
    }

    /**
     * Generate individual messages for each guest
     */
    private function generatePerGuestMessages(array $eventContext, array $guestData, array $userInstructions, ?string $preferredLanguage): array
    {
        $perGuestMessages = [];
        
        // Check if we're processing all guests or a specific guest
        $requestedGuestIds = array_keys($userInstructions['guests'] ?? []);
        
        if (empty($requestedGuestIds)) {
            return [
                'success' => false,
                'error' => 'No guests specified for message generation'
            ];
        }
        
        $processAllGuests = count($requestedGuestIds) > 1;
        
        foreach ($guestData as $listId => $listData) {
            // Handle guests in groups
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    foreach ($groupData['guests'] as $guest) {
                        // Only process requested guests (handle both string and integer IDs)
                        if (!$processAllGuests && !in_array((string)$guest->id, array_map('strval', $requestedGuestIds))) {
                            continue;
                        }
                        
                        $guestLanguage = $guest->preferred_language ?? $preferredLanguage;
                        $guestInstructions = $userInstructions['guests'][$guest->id] ?? $userInstructions['default'] ?? '';
                        
                        $prompt = $this->buildPerGuestPrompt($eventContext, $guest, $guestInstructions);
                        $message = $this->callAI($prompt, $guestLanguage);
                        
                        // Replace event placeholders in the message
                        $processedMessage = $this->replaceEventPlaceholders($message, $eventContext);
                        
                        // Replace {name} with actual guest name
                        $finalMessage = str_replace('{name}', $guest->name, $processedMessage);
                        
                        $perGuestMessages[$guest->id] = $finalMessage;
                    }
                }
            }
            
            // Handle ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    // Only process requested guests (handle both string and integer IDs)
                    if (!$processAllGuests && !in_array((string)$guest->id, array_map('strval', $requestedGuestIds))) {
                        continue;
                    }
                    
                    $guestLanguage = $guest->preferred_language ?? $preferredLanguage;
                    $guestInstructions = $userInstructions['guests'][$guest->id] ?? $userInstructions['default'] ?? '';
                    
                    $prompt = $this->buildPerGuestPrompt($eventContext, $guest, $guestInstructions);
                    $message = $this->callAI($prompt, $guestLanguage);
                    
                    // Replace event placeholders in the message
                    $processedMessage = $this->replaceEventPlaceholders($message, $eventContext);
                    
                    // Replace {name} with actual guest name
                    $finalMessage = str_replace('{name}', $guest->name, $processedMessage);
                    
                    $perGuestMessages[$guest->id] = $finalMessage;
                }
            }
        }
        
        return [
            'success' => true,
            'mode' => 'per_guest',
            'per_guest_messages' => $perGuestMessages,
            'ai_generated' => true
        ];
    }

    /**
     * Generate messages for an entire list (groups + guests)
     */
    private function generateListMessages(array $eventContext, array $guestData, array $userInstructions, ?string $preferredLanguage): array
    {
        $groupMessages = [];
        $perGuestMessages = [];
        
        foreach ($guestData as $listId => $listData) {
            // Generate group templates
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    $groupName = $groupData['group_name'];
                    $key = $listId . '_' . $groupId;
                    $groupInstructions = $userInstructions['groups'][$key] ?? $userInstructions['default'] ?? '';
                    
                    $prompt = $this->buildGroupPrompt($eventContext, $groupName, $groupInstructions);
                    $message = $this->callAI($prompt, $preferredLanguage);
                    
                    $groupMessages[$key] = $message;
                }
            }
            
            // Generate individual guest messages
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    foreach ($groupData['guests'] as $guest) {
                        $guestLanguage = $guest->preferred_language ?? $preferredLanguage;
                        $guestInstructions = $userInstructions['guests'][$guest->id] ?? $userInstructions['default'] ?? '';
                        
                        $prompt = $this->buildPerGuestPrompt($eventContext, $guest, $guestInstructions);
                        $message = $this->callAI($prompt, $guestLanguage);
                        
                        // Replace event placeholders in the message
                        $processedMessage = $this->replaceEventPlaceholders($message, $eventContext);
                        
                        // Replace {name} with actual guest name
                        $finalMessage = str_replace('{name}', $guest->name, $processedMessage);
                        
                        $perGuestMessages[$guest->id] = $finalMessage;
                    }
                }
            }
            
            // Handle ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $guestLanguage = $guest->preferred_language ?? $preferredLanguage;
                    $guestInstructions = $userInstructions['guests'][$guest->id] ?? $userInstructions['default'] ?? '';
                    
                    $prompt = $this->buildPerGuestPrompt($eventContext, $guest, $guestInstructions);
                    $message = $this->callAI($prompt, $guestLanguage);
                    
                    // Replace event placeholders in the message
                    $processedMessage = $this->replaceEventPlaceholders($message, $eventContext);
                    
                    // Replace {name} with actual guest name
                    $finalMessage = str_replace('{name}', $guest->name, $processedMessage);
                    
                    $perGuestMessages[$guest->id] = $finalMessage;
                }
            }
        }
        
        return [
            'success' => true,
            'mode' => 'list',
            'group_messages' => $groupMessages,
            'per_guest_messages' => $perGuestMessages,
            'ai_generated' => true
        ];
    }

    /**
     * Build comprehensive event context for AI prompts
     */
    private function buildEventContext(Event $event): array
    {
        return [
            'name' => $event->name,
            'description' => $event->description,
            'start_date' => $event->start_date->format('l, F j, Y \a\t g:i A'),
            'end_date' => $event->end_date ? $event->end_date->format('g:i A') : null,
            'location' => $event->location,
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'additional_information' => $event->additional_information,
            'rsvp_enabled' => $event->rsvp_enabled ?? false,
            'qr_checkin_enabled' => $event->qr_checkin_enabled ?? false,
            'invitation_platforms' => $event->invitation_platforms ?? [],
            'event_type' => $this->determineEventType($event->name, $event->description),
            'formality_level' => $this->determineFormalityLevel($event->name, $event->description)
        ];
    }

    /**
     * Build prompt for general message generation
     */
    private function buildGeneralPrompt(array $eventContext, array $userInstructions): string
    {
        $tone = $userInstructions['tone'] ?? 'professional';
        $style = $userInstructions['style'] ?? 'friendly';
        $additionalNotes = $userInstructions['additional_notes'] ?? '';
        
        // Override formality based on tone for better consistency
        if ($tone === 'friendly' || $tone === 'casual') {
            $eventContext['formality_level'] = 'casual';
        } elseif ($tone === 'formal' || $tone === 'professional') {
            $eventContext['formality_level'] = 'formal';
        }
        
        return "You are an expert event invitation writer. Create a compelling invitation message for the following event:

EVENT DETAILS:
- Name: {$eventContext['name']}
- Date & Time: {$eventContext['start_date']}" . 
        ($eventContext['end_date'] ? "\n- End Time: {$eventContext['end_date']}" : "") .
        ($eventContext['venue_name'] ? "\n- Location: {$eventContext['venue_name']}" : "") . "
- Type: {$eventContext['event_type']}
- Description: {$eventContext['description']}" .
($eventContext['additional_information'] ? "\n- Additional Info: {$eventContext['additional_information']}" : "") . "

FEATURES:
- RSVP: " . ($eventContext['rsvp_enabled'] ? 'Required' : 'Not required') . "
- QR Check-in: " . ($eventContext['qr_checkin_enabled'] ? 'Available' : 'Not available') . "
- Platforms: " . implode(', ', $eventContext['invitation_platforms'] ?? []) . "

REQUIREMENTS:
- Tone: {$tone}
- Style: {$style}
- Formality: {$eventContext['formality_level']}
- DO NOT include placeholders - use actual event details
- Length: 2-4 sentences
- Only include subject line if tone is formal or professional
- Call to action for RSVP if enabled
- Mention QR check-in if available
        - FORMATTING: Use proper line breaks and paragraphs for readability
        - When referencing location in the message body, use the venue name only (no address/city)
 - Use ONLY the provided event details and user instructions. Do NOT invent, assume, or add any information not explicitly provided above.
 - If a detail (like location, time, dress code, RSVP, QR) is not provided above, omit it entirely.
 - Do NOT add dress code, parking, venue specifics, or any extra context unless present in the event details or the user instructions.

Additional Notes: {$additionalNotes}

Generate an invitation message that matches the requested tone and style:
- For CASUAL/FRIENDLY tone: Write a conversational message WITHOUT any subject line. Start directly with the guest's name or a greeting.
- For FORMAL/PROFESSIONAL tone: Include a proper subject line followed by a formal message.
- Always write complete sentences. Never end with incomplete phrases like 'Kindly.' or 'and we will.'
- Include all necessary event information naturally in the message body.
- FORMATTING: Use line breaks (\\n) to separate paragraphs and improve readability. For formal messages, separate the subject line from the body with a line break.";
    }

    /**
     * Build prompt for group-specific message generation
     */
    private function buildGroupPrompt(array $eventContext, string $groupName, string $groupInstructions): string
    {
        $basePrompt = $this->buildGeneralPrompt($eventContext, []);
        
        return $basePrompt . "

GROUP-SPECIFIC REQUIREMENTS:
- Group: {$groupName}
- Custom Instructions: {$groupInstructions}
- FORMATTING: Use line breaks (\\n) to separate paragraphs and improve readability
 - Use ONLY the provided event/group details and instructions. Do NOT invent extra details.
 - If a detail is not provided, omit it.
 - When mentioning location, include the venue name only (no address/city)

Tailor the message specifically for the {$groupName} group while maintaining the overall event context.";
    }

    /**
     * Build prompt for individual guest message generation
     */
    private function buildPerGuestPrompt(array $eventContext, Guest $guest, string $guestInstructions): string
    {
        $basePrompt = $this->buildGeneralPrompt($eventContext, []);
        
        $guestInfo = "GUEST INFORMATION:
- Name: {$guest->name}
- Email: {$guest->email}
- Phone: {$guest->phone}
- Preferred Language: " . ($guest->preferred_language ?? 'Not specified') . "
- Notes: {$guest->notes}";
        
        return $basePrompt . "

{$guestInfo}

PERSONALIZATION REQUIREMENTS:
- Custom Instructions: {$guestInstructions}
- Personalize the message for {$guest->name}
- Consider their preferred language and any special requirements
- If user instructions include specific requests (like 'bring kids', 'bring brother'), include them naturally in the message
- Use the guest's actual name, not placeholders
- FORMATTING: Use line breaks (\\n) to separate paragraphs and improve readability
 - Use ONLY the provided event/guest details and instructions. Do NOT invent extra details. If a detail is missing, omit it.
 - When referencing location, include the venue name only (no address/city)

Create a personalized invitation message specifically for {$guest->name}. Follow the tone and style requirements above, and incorporate any specific user instructions naturally into the message.";
    }

    /**
     * Call AI service (OpenAI or Gemini)
     */
    private function callAI(string $prompt, ?string $preferredLanguage = null): string
    {
        // Add language instruction if specified
        if ($preferredLanguage) {
            $prompt .= "\n\nIMPORTANT: Generate the message in {$preferredLanguage} language.";
        }

        Log::info('🔵 [AI GENERATION] Starting AI call', [
            'prompt_length' => strlen($prompt),
            'preferred_language' => $preferredLanguage,
            'prompt_preview' => substr($prompt, 0, 200) . '...'
        ]);

        // Use Gemini only, fallback to local generation
        try {
            if ($this->geminiApiKey) {
                Log::info('🔵 [AI GENERATION] Attempting Gemini');
                $result = $this->callGemini($prompt);
                
                // Post-process the result to add proper line breaks
                $result = $this->formatMessageWithLineBreaks($result);
                $result = $this->sanitizeGeneratedMessage($result);
                $result = $this->appendGenerationSource($result, 'Gemini');
                
                Log::info('✅ [AI GENERATION] Gemini Success', [
                    'model' => 'gemini-1.5-pro',
                    'generated_length' => strlen($result),
                    'generated_preview' => substr($result, 0, 100) . '...'
                ]);
                return $result;
            }
        } catch (\Exception $e) {
            Log::warning('❌ [AI GENERATION] Gemini Failed', [
                'error' => $e->getMessage(),
                'model' => 'gemini-1.5-pro'
            ]);
        }

        // Fallback to local message generation
        Log::info('🔵 [AI GENERATION] Using Local Fallback');
        $result = $this->generateLocalMessage($prompt);
        
        // Post-process the result to add proper line breaks
        $result = $this->formatMessageWithLineBreaks($result);
        $result = $this->sanitizeGeneratedMessage($result);
        $result = $this->appendGenerationSource($result, 'Local');
        
        Log::info('✅ [AI GENERATION] Local Success', [
            'model' => 'local-fallback',
            'generated_length' => strlen($result),
            'generated_preview' => substr($result, 0, 100) . '...'
        ]);
        return $result;
    }

    /**
     * Append a consistent generation source marker to the message
     */
    private function appendGenerationSource(string $text, string $source): string
    {
        $marker = $source === 'Gemini' ? 'Generated using Gemini' : 'Generated locally';
        if (stripos($text, $marker) !== false) {
            return $text;
        }
        return rtrim($text) . "\n\n[{$marker}]";
    }

    /**
     * Public helper to minimally edit an existing message based on user instructions
     */
    public function editMessage(string $oldText, string $instructions): string
    {
        $prompt = "You are an expert copy editor. Make MINIMAL edits to the existing invitation text based on the user's instructions.\n" .
            "Do NOT rewrite from scratch. Preserve tone and content as much as possible.\n\n" .
            "INSTRUCTIONS: {$instructions}\n\n" .
            "EXISTING TEXT:\n{$oldText}\n\n" .
            "Return ONLY the edited text.";

        $edited = $this->callAI($prompt);
        $edited = $this->sanitizeGeneratedMessage($edited);
        return trim($edited) !== '' ? $edited : $oldText;
    }

    /**
     * Remove undesirable bracketed placeholders/redactions from generated text
     */
    private function sanitizeGeneratedMessage(string $text): string
    {
        // Remove explicit redaction-style markers commonly produced by models
        $patterns = [
            '/\[\s*removed\s*-\s*no\s*details\s*provided\s*\]/i',
            '/\[\s*no\s*details\s*provided\s*\]/i',
            '/\[\s*redacted\s*\]/i',
        ];
        $text = preg_replace($patterns, '', $text);

        // Collapse any leftover multiple spaces and tidy whitespace
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $prompt): string
    {
        Log::info('🔵 [OPENAI] Making API call', [
            'model' => 'gpt-3.5-turbo',
            'prompt_length' => strlen($prompt),
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert event invitation writer. Generate professional, engaging invitation messages.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);

        if ($response->successful()) {
            $result = trim($response->json('choices.0.message.content'));
            Log::info('✅ [OPENAI] API call successful', [
                'model' => 'gpt-3.5-turbo',
                'response_length' => strlen($result),
                'response_preview' => substr($result, 0, 100) . '...'
            ]);
            return $result;
        }

        $errorResponse = $response->json();
        Log::error('❌ [OPENAI] API call failed', [
            'model' => 'gpt-3.5-turbo',
            'status' => $response->status(),
            'error' => $errorResponse
        ]);
        throw new \Exception('OpenAI API error: ' . $response->body());
    }

    /**
     * Call Gemini API
     */
    private function callGemini(string $prompt): string
    {
        Log::info('🔵 [GEMINI] Making API call', [
            'model' => 'gemini-1.5-pro',
            'prompt_length' => strlen($prompt),
            'max_output_tokens' => 1000,
            'temperature' => 0.7
        ]);

        $response = Http::timeout(15)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 1000,
                'temperature' => 0.7,
                'topP' => 0.8,
                'topK' => 40
            ]
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $result = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
                Log::info('✅ [GEMINI] API call successful', [
                    'model' => 'gemini-1.5-pro',
                    'response_length' => strlen($result),
                    'response_preview' => substr($result, 0, 100) . '...'
                ]);
                return $result;
            }
            Log::error('❌ [GEMINI] Invalid response format', [
                'model' => 'gemini-1.5-pro',
                'response_data' => $responseData
            ]);
            throw new \Exception('Invalid response format from Gemini API');
        }

        $errorResponse = $response->json();
        Log::error('❌ [GEMINI] API call failed', [
            'model' => 'gemini-1.5-pro',
            'status' => $response->status(),
            'error' => $errorResponse
        ]);
        throw new \Exception('Gemini API error: ' . $response->body());
    }

    /**
     * Format message with proper line breaks for readability
     */
    private function formatMessageWithLineBreaks(string $message): string
    {
        // Add line breaks after common patterns
        $message = preg_replace('/(Subject:.*?)(Dear|We|I|Thank)/i', "$1\n\n$2", $message);
        $message = preg_replace('/(Dear.*?)(We|I|Thank|Please|While|Gifts|Please)/i', "$1\n\n$2", $message);
        $message = preg_replace('/(We.*?)(Please|Thank|While|Gifts|Best|Sincerely)/i', "$1\n\n$2", $message);
        $message = preg_replace('/(Please.*?)(We|Thank|Best|Sincerely)/i', "$1\n\n$2", $message);
        $message = preg_replace('/(Thank.*?)(Best|Sincerely)/i', "$1\n\n$2", $message);
        $message = preg_replace('/(Best regards|Sincerely|Yours truly)/i', "\n\n$1", $message);
        
        // Add line breaks for lists or bullet points
        $message = preg_replace('/(\d+\.|•|\*)\s*/', "\n$1 ", $message);
        
        // Clean up multiple line breaks
        $message = preg_replace('/\n{3,}/', "\n\n", $message);
        
        return trim($message);
    }

    /**
     * Generate message locally when AI services are unavailable
     */
    private function generateLocalMessage(string $prompt): string
    {
        Log::info('🔵 [LOCAL] Using local message generation as fallback', [
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 200) . '...'
        ]);
        
        // Extract event details from the prompt
        $eventName = $this->extractFromPrompt($prompt, 'Name:', 'Date');
        $eventDate = $this->extractFromPrompt($prompt, 'Date & Time:', 'Location');
        $eventLocation = $this->extractFromPrompt($prompt, 'Location:', 'Type');
        $eventType = $this->extractFromPrompt($prompt, 'Type:', 'Description');
        $eventDescription = $this->extractFromPrompt($prompt, 'Description:', 'FEATURES');
        
        // Clean up extracted data
        $eventName = trim($eventName);
        $eventDate = trim($eventDate);
        $eventLocation = trim($eventLocation);
        $eventDescription = trim($eventDescription);
        
        // Determine tone from prompt
        $tone = 'professional';
        if (str_contains(strtolower($prompt), 'formal')) {
            $tone = 'formal';
        } elseif (str_contains(strtolower($prompt), 'casual') || str_contains(strtolower($prompt), 'friendly')) {
            $tone = 'casual';
        }
        
        Log::info('🔵 [LOCAL] Extracted parameters', [
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'event_location' => $eventLocation,
            'event_type' => $eventType,
            'event_description' => $eventDescription,
            'tone' => $tone
        ]);
        
        // Generate appropriate message based on tone
        switch ($tone) {
            case 'formal':
                $message = "Dear {name},\n\n";
                $message .= "You are cordially invited to attend {$eventName}.\n\n";
                if ($eventDate && $eventDate !== '-') {
                    $message .= "Date: {$eventDate}\n";
                }
                if ($eventLocation && $eventLocation !== 'TBD' && $eventLocation !== '-') {
                    $message .= "Location: {$eventLocation}\n";
                }
                if ($eventDescription && $eventDescription !== '-') {
                    $message .= "\n{$eventDescription}\n";
                }
                $message .= "\nWe would be honored by your presence at this special occasion.\n\n";
                $message .= "Best regards,\nEvent Organizer";
                break;
                
            case 'casual':
                $message = "Hey {name}!\n\n";
                $message .= "You're invited to {$eventName}!\n\n";
                if ($eventDate && $eventDate !== '-') {
                    $message .= "When: {$eventDate}\n";
                }
                if ($eventLocation && $eventLocation !== 'TBD' && $eventLocation !== '-') {
                    $message .= "Where: {$eventLocation}\n";
                }
                if ($eventDescription && $eventDescription !== '-') {
                    $message .= "\n{$eventDescription}\n";
                }
                $message .= "\nHope you can make it!\n\n";
                $message .= "Cheers,\nEvent Organizer";
                break;
                
            default: // professional
                $message = "Dear {name},\n\n";
                $message .= "You are invited to attend {$eventName}.\n\n";
                if ($eventDate && $eventDate !== '-') {
                    $message .= "Date: {$eventDate}\n";
                }
                if ($eventLocation && $eventLocation !== 'TBD' && $eventLocation !== '-') {
                    $message .= "Location: {$eventLocation}\n";
                }
                if ($eventDescription && $eventDescription !== '-') {
                    $message .= "\n{$eventDescription}\n";
                }
                $message .= "\nWe look forward to your presence.\n\n";
                $message .= "Best regards,\nEvent Organizer";
                break;
        }
        
        Log::info('✅ [LOCAL] Generated local message', [
            'tone' => $tone,
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 100) . '...'
        ]);
        
        return $message;
    }

    /**
     * Replace event placeholders in a template with actual values
     */
    private function replaceEventPlaceholders(string $template, array $eventContext): string
    {
        // Only replace placeholders if we have the actual data
        $replacements = [];
        
        if (!empty($eventContext['name'])) {
            $replacements['{event_name}'] = $eventContext['name'];
        }
        
        if (!empty($eventContext['start_date'])) {
            $replacements['{date}'] = $eventContext['start_date'];
        }
        
        if (!empty($eventContext['end_date'])) {
            $replacements['{end_date}'] = $eventContext['end_date'];
        }
        
        if (!empty($eventContext['venue_name']) || !empty($eventContext['location'])) {
            $replacements['{location}'] = $eventContext['venue_name'] ?? $eventContext['location'];
        }
        
        if (!empty($eventContext['venue_name'])) {
            $replacements['{venue_name}'] = $eventContext['venue_name'];
        }
        
        if (!empty($eventContext['venue_address'])) {
            $replacements['{venue_address}'] = $eventContext['venue_address'];
        }
        
        if (!empty($eventContext['description'])) {
            $replacements['{description}'] = $eventContext['description'];
        }
        
        if (!empty($eventContext['additional_information'])) {
            $replacements['{additional_information}'] = $eventContext['additional_information'];
        }
        
        // Apply replacements
        $processedTemplate = $template;
        foreach ($replacements as $placeholder => $value) {
            $processedTemplate = str_replace($placeholder, $value, $processedTemplate);
        }
        
        // Remove any remaining placeholders and their surrounding text
        $processedTemplate = preg_replace('/[.,\s]*\{[^}]+\}[.,\s]*/', ' ', $processedTemplate);
        
        // Remove placeholder-related incomplete phrases
        $processedTemplate = preg_replace('/please\s+RSVP\s+by\s+\[.*?\]/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/RSVP\s+by\s+\[.*?\]/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/Kindly\s+RSVP\s+by\s+\[.*?\]/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/\[.*?Date.*?\]/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/\[.*?TBD.*?\]/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/location\s+is\s+still\s+to\s+be\s+determined/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/the\s+location\s+is\s+still\s+being\s+finalized/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/and\s+will\s+be\s+shared\s+soon/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/so\s+we\s+can\s+get\s+a\s+headcount/i', '', $processedTemplate);
        $processedTemplate = preg_replace('/so\s+we\s+can\s+keep\s+you\s+updated/i', '', $processedTemplate);
        
        // Remove duplicate date patterns more aggressively
        $processedTemplate = preg_replace('/on\s+(Saturday|Sunday|Monday|Tuesday|Wednesday|Thursday|Friday),?\s+([^,]+),?\s+at\s+([^,]+),?\s+([^,]+),?\s+at\s+\3/i', 'on $1, $2 at $3', $processedTemplate);
        $processedTemplate = preg_replace('/([^,]+),?\s+at\s+([^,]+),?\s+\1,?\s+at\s+\2/i', '$1 at $2', $processedTemplate);
        
        // Clean up extra whitespace, punctuation, and empty lines
        $processedTemplate = preg_replace('/[.,\s]*[.,]\s*[.,]/', '.', $processedTemplate); // Remove duplicate punctuation
        $processedTemplate = preg_replace('/\s+/', ' ', $processedTemplate); // Normalize spaces
        $processedTemplate = preg_replace('/[.,]\s*\./', '.', $processedTemplate); // Fix double periods
        $processedTemplate = preg_replace('/\.\s*,/', '.', $processedTemplate); // Fix period comma
        $processedTemplate = preg_replace('/,\s*\./', '.', $processedTemplate); // Fix comma period
        $processedTemplate = preg_replace('/\s+[.,]/', '.', $processedTemplate); // Fix spacing before punctuation
        $processedTemplate = trim($processedTemplate);
        
        return $processedTemplate;
    }

    /**
     * Extract text from prompt between two markers
     */
    private function extractFromPrompt(string $prompt, string $startMarker, string $endMarker): string
    {
        $startPos = strpos($prompt, $startMarker);
        if ($startPos === false) {
            return '';
        }
        
        $startPos += strlen($startMarker);
        $endPos = strpos($prompt, $endMarker, $startPos);
        
        if ($endPos === false) {
            $endPos = strlen($prompt);
        }
        
        return trim(substr($prompt, $startPos, $endPos - $startPos));
    }

    /**
     * Determine event type based on name and description
     */
    private function determineEventType(string $name, ?string $description): string
    {
        $text = strtolower($name . ' ' . ($description ?? ''));
        
        if (str_contains($text, 'wedding') || str_contains($text, 'marriage')) {
            return 'Wedding';
        } elseif (str_contains($text, 'birthday') || str_contains($text, 'party')) {
            return 'Birthday Party';
        } elseif (str_contains($text, 'conference') || str_contains($text, 'seminar')) {
            return 'Conference';
        } elseif (str_contains($text, 'meeting') || str_contains($text, 'business')) {
            return 'Business Meeting';
        } elseif (str_contains($text, 'gala') || str_contains($text, 'fundraiser')) {
            return 'Gala/Fundraiser';
        } elseif (str_contains($text, 'workshop') || str_contains($text, 'training')) {
            return 'Workshop/Training';
        } else {
            return 'Event';
        }
    }

    /**
     * Determine formality level based on event details
     */
    private function determineFormalityLevel(string $name, ?string $description): string
    {
        $text = strtolower($name . ' ' . ($description ?? ''));
        
        if (str_contains($text, 'formal') || str_contains($text, 'gala') || str_contains($text, 'black tie')) {
            return 'formal';
        } elseif (str_contains($text, 'casual') || str_contains($text, 'party') || str_contains($text, 'fun')) {
            return 'casual';
        } else {
            return 'professional';
        }
    }

    /**
     * Preview how a message will appear for a specific guest
     */
    public function previewMessageForGuest(string $message, Guest $guest, Event $event): string
    {
        $replacements = [
            '{name}' => $guest->name,
            '{event_name}' => $event->name,
            '{date}' => $event->start_date->format('l, F j, Y'),
            '{time}' => $event->start_date->format('g:i A'),
            '{location}' => $event->venue_name ?: $event->location ?: 'TBD'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Validate message generation parameters
     */
    public function validateParameters(Event $event, string $mode, array $guestData): array
    {
        $errors = [];

        if (empty($event->name)) {
            $errors[] = 'Event name is required';
        }

        if (empty($guestData)) {
            $errors[] = 'No guest data provided';
        }

        if (!in_array($mode, ['general', 'group', 'per_guest'])) {
            $errors[] = 'Invalid mode specified';
        }

        if ($mode === 'group') {
            foreach ($guestData as $listId => $listData) {
                if (empty($listData['list_name'])) {
                    $errors[] = 'Guest list name is required for group mode';
                }
            }
        }

        return $errors;
    }
} 