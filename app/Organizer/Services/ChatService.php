<?php

namespace App\Organizer\Services;

use App\Services\EventMessageGenerationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private $eventMessageGenerationService;
    private $geminiApiKey;
    private $guestDataContext = [];
    
    /**
     * Log to the dedicated Chat.log file
     * Accepts common mis-ordered usages and normalizes into (level, message, context)
     */
    private function logToChat($levelOrMessage, $maybeMessage = null, $maybeContext = []): void
    {
        $level = 'info';
        $message = '';
        $context = [];

        // Normalize parameters to (level, message, context)
        if (is_string($levelOrMessage) && in_array(strtolower($levelOrMessage), ['info','warning','error','debug','notice','critical','alert','emergency'])) {
            $level = strtolower($levelOrMessage);
            $message = is_string($maybeMessage) ? $maybeMessage : '';
            $context = is_array($maybeContext) ? $maybeContext : [];
        } else {
            // Called as logToChat(message, [context])
            $message = is_string($levelOrMessage) ? $levelOrMessage : '';
            if (is_array($maybeMessage)) {
                $context = $maybeMessage;
            } elseif (is_array($maybeContext)) {
                $context = $maybeContext;
            }
        }

        $logMessage = '[' . now()->format('Y-m-d H:i:s') . '] ' . strtoupper($level) . ': ' . $message;
        if (!empty($context)) {
            $logMessage .= ' ' . json_encode($context);
        }

        file_put_contents(storage_path('logs/Chat.log'), $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function __construct(EventMessageGenerationService $eventMessageGenerationService)
    {
        $this->eventMessageGenerationService = $eventMessageGenerationService;
        $this->geminiApiKey = config('services.gemini.api_key');
        
        if (empty($this->geminiApiKey)) {
            $this->logToChat('error', '❌ [CONFIG] Gemini API key is missing or empty');
            $this->logToChat('error', '❌ [CONFIG] Please set GEMINI_API_KEY in your .env file');
        } else {
            $this->logToChat('info', '✅ [CONFIG] Gemini API key loaded', ['key_length' => strlen($this->geminiApiKey)]);
            
            // Test the API key with a simple request
            try {
                $testResponse = Http::timeout(5)->withHeaders([
                    'Content-Type' => 'application/json'
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
                    'contents' => [
                        ['parts' => [['text' => 'Hello, please respond with "OK"']]]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 10,
                        'temperature' => 0.1
                    ]
                ]);
                
                if ($testResponse->successful()) {
                    $this->logToChat('info', '✅ [CONFIG] Gemini API key is working');
                } else {
                    $this->logToChat('error', '❌ [CONFIG] Gemini API key test failed', [
                        'status' => $testResponse->status(),
                        'body' => $testResponse->body()
                    ]);
                }
            } catch (\Exception $e) {
                $this->logToChat('error', '❌ [CONFIG] Gemini API key test failed with exception', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Process user message and return response with actions
     */
    public function processMessage(string $message, array $history, array $eventData, array $guestData, array $currentMessages = []): array
    {
        try {
            // Check if we have basic event information
            $eventName = $this->extractEventName($eventData);
            $eventDate = $this->extractEventDate($eventData);
            
            
            // If we're using fallback data, log a warning
            if ($eventData['using_fallback_data'] ?? false) {
                $this->logToChat('warning', '🔵 [CHAT] Using fallback event data - user should complete Step 1', [
                    'event_name' => $eventName,
                    'event_date' => $eventDate
                ]);
            }

            // Store guest data in memory for AI context
            $this->guestDataContext = $guestData;
            
            
            // Check if we have guest data
            if (empty($guestData)) {
                return [
                    'success' => false,
                    'response' => '🚫 **No Guest Lists Found**

            I cannot process your message because no guest lists are currently loaded. This could happen if:',
                    'actions' => []
                ];
            }
            
            // Use AI to analyze the complete user intent including tone and instructions
            $aiAnalysis = null;
            $aiTimeout = false;
            
            try {
                // Set a timeout for AI analysis
                set_time_limit(15); // 15 seconds max
                
                $aiAnalysis = $this->analyzeWithAI($message, $eventData, $guestData, $history);
            } catch (\Exception $aiError) {
                $aiTimeout = true;
            }
            
            // If AI analysis failed or timed out, use fallback
            if ($aiTimeout || !$aiAnalysis) {
                $aiAnalysis = $this->analyzeSimplePattern($message, $guestData);
            }
            
            // Debug: Check if AI analysis is valid
            if (!isset($aiAnalysis['type']) || $aiAnalysis['type'] === 'response_only') {
            }
            
            // Check if this is a multi-action request
            if (isset($aiAnalysis['multi_actions']) && is_array($aiAnalysis['multi_actions'])) {
                $allActions = [];
                $allResponses = [];
                
                foreach ($aiAnalysis['multi_actions'] as $action) {
                    $actions = $this->executeAction($action, $eventData, $guestData, $message);
                    $allActions = array_merge($allActions, $actions);
                    
                    // Generate response for this action
                    $response = $this->generateIntelligentResponse($action, $actions);
                    $allResponses[] = $response;
                }
                
                // Combine all responses
                $combinedResponse = $this->combineMultiActionResponses($allResponses);
                
                
                return [
                    'success' => true,
                    'response' => $combinedResponse,
                    'actions' => $allActions
                ];
            } else {
                // Check if this is a conversational/information request that doesn't need actions
                $conversationalTypes = ['conversational', 'help', 'information'];
                if (in_array($aiAnalysis['type'] ?? '', $conversationalTypes)) {
                    
                    // Return the AI's response directly for conversational requests
                    $response = $aiAnalysis['response'] ?? $this->generateIntelligentResponse($aiAnalysis, []);
                    
                    return [
                        'success' => true,
                        'response' => $response,
                        'actions' => []
                    ];
                }
                
                // Single action processing (existing logic)
                // If this is an edit intent, run edit pipeline with current messages
                if ($this->isEditIntent($aiAnalysis, $message)) {
                    $actions = $this->applyEditIntent($aiAnalysis, $eventData, $guestData, $currentMessages, $message);
                } else {
                    $actions = $this->executeAction($aiAnalysis, $eventData, $guestData, $message);
                }

                // Check if we need to ask for missing information
                if (!empty($actions) && isset($actions[0]['type']) && $actions[0]['type'] === 'ask_for_info') {
                    return [
                        'success' => true,
                        'response' => $actions[0]['message'],
                        'actions' => []
                    ];
                }


                        // Check if actions were generated
        if (empty($actions)) {
            $this->logToChat('warning', '🔵 [CHAT] No actions generated', [
                'intent' => $aiAnalysis,
                'message' => $message,
                'event_data_keys' => array_keys($eventData),
                'event_data_sample' => array_slice($eventData, 0, 5, true)
            ]);
            
            // Provide a more helpful response based on the situation
            if (empty($eventData['name']) || empty($eventData['start_date'])) {
                $response = "I couldn't generate messages because some essential event information is missing.";
            } else {
                // Check if we're using fallback defaults
                $eventName = $this->extractEventName($eventData);
                $eventDate = $this->extractEventDate($eventData);
                
                
            }
        } else {
                    // Generate a more intelligent response based on the action type
                    $response = $this->generateIntelligentResponse($aiAnalysis, $actions);
                }
                
                return [
                    'success' => true,
                    'response' => $response,
                    'actions' => $actions
                ];
            }

        } catch (\Exception $e) {            
            // Try to use fallback analysis
            try {
                $fallbackAnalysis = $this->analyzeSimplePattern($message, $guestData);
                
                if ($fallbackAnalysis['type'] === 'conversational' || $fallbackAnalysis['type'] === 'help') {
                    return [
                        'success' => true,
                        'response' => $fallbackAnalysis['response'],
                        'actions' => []
                    ];
                }
            } catch (\Exception $fallbackError) {
            }
            
            return [
                'success' => false,
                'response' => "I apologize, but I'm having trouble processing your request right now. Please try rephrasing your message or try again in a moment.",
                'actions' => []
            ];
        }
    }

    /**
     * Use AI to analyze user message completely
     */
    private function analyzeWithAI(string $message, array $eventData, array $guestData, array $history): array
    {
        try {
                    $this->logToChat('info', '🔵 [AI ANALYSIS] Building prompt', [
            'message' => $message,
            'guest_data_keys' => array_keys($guestData),
            'event_data_keys' => array_keys($eventData)
        ]);
            
            // Debug guest data structure
            $this->logGuestDataStructure($guestData);
            
            $prompt = $this->buildAnalysisPrompt($message, $eventData, $guestData, $history);
            
            $this->logToChat('info', '🔵 [AI ANALYSIS] Prompt built successfully', [
                'prompt_length' => strlen($prompt),
                'prompt_preview' => substr($prompt, 800, 500), // Show guest list part
                'prompt_too_long' => strlen($prompt) > 30000 // Flag if prompt is very long
            ]);
            
            // If prompt is too long, truncate it
            if (strlen($prompt) > 30000) {
                $this->logToChat('warning', '⚠️ [AI ANALYSIS] Prompt too long, truncating', [
                    'original_length' => strlen($prompt),
                    'truncated_length' => 30000
                ]);
                $prompt = substr($prompt, 0, 30000);
            }
            
            $this->logToChat('info', '🔵 [AI ANALYSIS] Making API call', [
                'api_key_length' => strlen($this->geminiApiKey),
                'prompt_length' => strlen($prompt),
                'timeout' => 10
            ]);
            
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json'
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 800,
                        'temperature' => 0.3
                    ]
                ]);
                
                $this->logToChat('info', '🔵 [AI ANALYSIS] API call completed', [
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);
            } catch (\Exception $apiError) {
                $this->logToChat('error', '❌ [AI ANALYSIS] API call failed with exception', [
                    'error' => $apiError->getMessage(),
                    'error_type' => get_class($apiError)
                ]);
                throw $apiError;
            }
            
            $this->logToChat('info', '🔵 [AI ANALYSIS] API response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $this->logToChat('info', '🔵 [AI ANALYSIS] Raw response', ['text' => $text]);
                
                $analysis = $this->parseAIAnalysis($text);
                
                // If AI failed to find guest_id but detected specific_guest, use fallback
                if ($analysis['type'] === 'specific_guest' && $analysis['guest_id'] === null) {
                    $this->logToChat('warning', 'AI detected specific_guest but no guest_id, using fallback');
                    return $this->analyzeSimplePattern($message, $guestData);
                }
                
                $this->logToChat('info', '✅ [AI ANALYSIS] Success', $analysis);
                return $analysis;
            } else {
                $this->logToChat('warning', 'AI API request failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            $this->logToChat('warning', 'AI analysis failed, using fallback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Fallback - analyze with simple patterns
        return $this->analyzeSimplePattern($message, $guestData);
    }

    /**
     * Analyze user intent from message (fallback method)
     */
    private function analyzeIntent(string $message, array $guestData): array
    {
        // This method is now a fallback - the AI should handle all detection
        // through the enhanced prompt with conversational and action examples
        
        $message = strtolower($message);
        
        // Simple fallback patterns for when AI fails
        if (preg_match('/^(hi|hello|hey|good morning|good afternoon|good evening)$/i', $message)) {
            return [
                'type' => 'conversational',
                'tone' => 'friendly',
                'instructions' => '',
                'response' => 'Hello! 👋 How can I help you with your event invitations today?',
                'original_message' => $message
            ];
        }
        
        if (preg_match('/\b(help|assist|support)\b/i', $message)) {
            return [
                'type' => 'help',
                'tone' => 'friendly',
                'instructions' => '',
                'response' => 'I can help you create personalized invitation messages. I can generate messages for all guests, specific guests, groups, or lists. Just ask me what you need!',
                'original_message' => $message
            ];
        }
        
        if (preg_match('/\b(who|what)\b.*\b(guest|group|list)\b/i', $message)) {
            return [
                'type' => 'information',
                'tone' => 'friendly',
                'instructions' => '',
                'response' => 'I\'ll show you information about your guests, groups, or lists. Let me check and provide you with details.',
                'original_message' => $message
            ];
        }
        
        // Default fallback
        return [
            'type' => 'response_only',
            'tone' => 'friendly',
            'instructions' => '',
            'response' => 'I understand your request, but I need more specific information about what you\'d like me to do. Please try being more specific about which guests or groups you want me to generate messages for, or ask me for help!'
        ];
    }

    /**
     * Extract tone from message
     */
    private function extractTone(string $message): string
    {
        // Check for formal first (more specific)
        if (preg_match('/\b(formal|professional|official|elegant|sophisticated|business|corporate)\b/i', $message)) {
            return 'formal';
        }
        
        // Check for friendly/casual
        if (preg_match('/\b(friendly|casual|informal|fun|warm|relaxed|personal|cozy)\b/i', $message)) {
            return 'friendly';
        }
        
        // Default to friendly for ambiguous cases
        return 'friendly';
    }
    
    /**
     * Extract second tone from message (for multi-action requests)
     */
    private function extractSecondTone(string $message): string
    {
        $message = strtolower($message);
        $tones = ['formal', 'friendly', 'professional', 'casual'];
        $foundTones = [];
        
        foreach ($tones as $tone) {
            if (strpos($message, $tone) !== false) {
                $foundTones[] = $tone;
            }
        }
        
        // Return the second tone found, or default
        return count($foundTones) > 1 ? $foundTones[1] : 'formal';
    }
    
    /**
     * Check if message is conversational/general chat
     */
    private function isConversationalMessage(string $message): bool
    {
        $conversationalPatterns = [
            '/^(hi|hello|hey|good morning|good afternoon|good evening)$/i',
            '/^(how are you|how\'s it going|what\'s up)$/i',
            '/^(thanks|thank you|thx)$/i',
            '/^(bye|goodbye|see you|talk to you later)$/i',
            '/^(yes|no|ok|okay|sure|maybe)$/i'
        ];
        
        foreach ($conversationalPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if message is a help request
     */
    private function isHelpRequest(string $message): bool
    {
        $helpPatterns = [
            '/\b(help|assist|support)\b/i',
            '/\b(what can you do|what do you do|how do you work)\b/i',
            '/\b(how to|how do i|how can i)\b/i',
            '/\b(guide|tutorial|instructions)\b/i'
        ];
        
        foreach ($helpPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if message is an information request
     */
    private function isInformationRequest(string $message): bool
    {
        $infoPatterns = [
            '/\b(who|what|when|where|why|how)\b.*\b(guest|list|group|event)\b/i',
            '/\b(tell me|show me|list|count)\b.*\b(guest|list|group)\b/i',
            '/\b(how many|how much)\b.*\b(guest|list|group)\b/i',
            '/\b(what is|what are)\b.*\b(guest|list|group)\b/i'
        ];
        
        foreach ($infoPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Extract user instructions from message
     */
    private function extractInstructions(string $message): string
    {
        $instructions = [];
        
        // Common patterns - more comprehensive
        $patterns = [
            '/bring.*kids/i' => 'Please bring your kids',
            '/bring.*children/i' => 'Please bring your children', 
            '/bring.*family/i' => 'Please bring your family',
            '/formal.*attire/i' => 'Formal attire required',
            '/dress.*code/i' => 'Please follow the dress code',
            '/plus.*one/i' => 'Plus one welcome',
            '/food.*provided/i' => 'Food will be provided',
            '/mention.*food/i' => 'Food will be provided',
            '/parking.*available/i' => 'Parking is available',
            '/no.*gifts/i' => 'No gifts please',
            '/rsvp.*required/i' => 'RSVP required',
            '/vegetarian.*options/i' => 'Vegetarian options available',
            '/wheelchair.*accessible/i' => 'Venue is wheelchair accessible'
        ];
        
        foreach ($patterns as $pattern => $instruction) {
            if (preg_match($pattern, $message)) {
                $instructions[] = $instruction;
            }
        }
        
        return implode('. ', $instructions);
    }

    /**
     * Find guest by name in message
     */
    private function findGuestByName(string $message, array $guestData): ?string
    {
        foreach ($guestData as $listData) {
            // Check grouped guests
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupData) {
                    foreach ($groupData['guests'] as $guest) {
                        $guestName = strtolower($guest->name);
                        if (strpos($message, $guestName) !== false) {
                            return (string)$guest->id;
                        }
                    }
                }
            }
            
            // Check ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $guestName = strtolower($guest->name);
                    if (strpos($message, $guestName) !== false) {
                        return (string)$guest->id;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Find group by name in message
     */
    private function findGroupByName(string $message, array $guestData): ?array
    {
        $message = strtolower($message);
        
        foreach ($guestData as $listId => $listData) {
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    // Try different possible group name fields
                    $groupName = strtolower($groupData['group_name'] ?? $groupData['name'] ?? '');
                    
                    if ($groupName && strpos($message, $groupName) !== false) {
                        $this->logToChat('🔵 [GROUP DETECTION] Found group', [
                            'group_name' => $groupName,
                            'group_id' => $groupId,
                            'list_id' => $listId,
                            'message' => $message
                        ]);
                        
                        return [
                            'group_id' => (string)$groupId,
                            'list_id' => (string)$listId
                        ];
                    }
                }
            }
        }
        
        $this->logToChat('🔵 [GROUP DETECTION] No group found', [
            'message' => $message,
            'available_groups' => $this->getAvailableGroupNames($guestData)
        ]);
        
        return null;
    }
    
    /**
     * Get available group names for debugging
     */
    private function getAvailableGroupNames(array $guestData): array
    {
        $groups = [];
        foreach ($guestData as $listId => $listData) {
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    $groupName = $groupData['group_name'] ?? $groupData['name'] ?? "Group {$groupId}";
                    $groups[] = $groupName;
                }
            }
        }
        return $groups;
    }
    
    /**
     * Find list by ID in message
     */
    private function findListById(string $message, array $guestData): ?string
    {
        $message = strtolower($message);
        
        // Look for list ID patterns like "list 299", "guest list 299", "list 299"
        if (preg_match('/list\s+(\d+)/i', $message, $matches)) {
            $listId = $matches[1];
            
            // Verify the list exists in guest data
            if (isset($guestData[$listId])) {
                $this->logToChat('🔵 [LIST DETECTION] Found list', [
                    'list_id' => $listId,
                    'message' => $message
                ]);
                return $listId;
            }
        }
        
        $this->logToChat('🔵 [LIST DETECTION] No list found', [
            'message' => $message,
            'available_lists' => array_keys($guestData)
        ]);
        
        return null;
    }

    /**
     * Generate AI response
     */
    private function generateResponse(string $message, array $history, array $eventData): string
    {
        try {
            $prompt = $this->buildPrompt($message, $history, $eventData);
            
            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json'
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 300,
                    'temperature' => 0.7
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return $this->cleanResponse($text);
            }
        } catch (\Exception $e) {
            $this->logToChat('AI response failed', ['error' => $e->getMessage()]);
        }
        
        return "I understand your request. Let me help you with that!";
    }

    /**
     * Build simple prompt for AI
     */
    private function buildPrompt(string $message, array $history, array $eventData): string
    {
        $eventName = $this->extractEventName($eventData);
        $eventDate = $this->extractEventDate($eventData);
        
        $prompt = "You are a helpful assistant for event invitations.\n";
        $prompt .= "Event: {$eventName} on {$eventDate}\n\n";
        $prompt .= "Rules:\n";
        $prompt .= "- Keep responses short and friendly\n";
        $prompt .= "- Be conversational and helpful\n";
        $prompt .= "- Don't include technical details\n\n";
        
        // Add conversation history (last 3 messages)
        $recentHistory = array_slice($history, -3);
        foreach ($recentHistory as $entry) {
            $role = $entry['sender'] === 'user' ? 'User' : 'Assistant';
            $prompt .= "{$role}: {$entry['message']}\n";
        }
        
        $prompt .= "User: {$message}\nAssistant:";
        
        return $prompt;
    }

    /**
     * Clean AI response
     */
    private function cleanResponse(string $response): string
    {
        // Remove any technical artifacts
        $response = preg_replace('/```[\s\S]*?```/', '', $response);
        $response = preg_replace('/\*\*(.*?)\*\*/', '$1', $response);
        $response = preg_replace('/\*(.*?)\*/', '$1', $response);
        $response = trim($response);
        
        return $response ?: "I'm here to help with your event invitations!";
    }

    /**
     * Execute action based on intent with language support
     */
    private function executeAction(array $intent, array $eventData, array $guestData, string $userMessage): array
    {
        // Check if we should generate messages for all languages
        if ($this->shouldGenerateForAllLanguages($intent)) {
            return $this->generateMessagesForAllLanguages($intent, $eventData, $guestData, $userMessage);
        }
        
        // Original single-language logic
        return $this->executeActionSingleLanguage($intent, $eventData, $guestData, $userMessage);
    }

    /**
     * Check if we should generate messages for all languages
     */
    private function shouldGenerateForAllLanguages(array $intent): bool
    {
        // Generate for all languages if it's a general message, all guests, or group message
        $multiLanguageTypes = ['general_message', 'all_guests', 'group_message'];
        $shouldGenerate = in_array($intent['type'] ?? '', $multiLanguageTypes);
        
        $this->logToChat('🔵 [LANGUAGE] Checking if should generate for all languages', [
            'intent_type' => $intent['type'] ?? 'unknown',
            'multi_language_types' => $multiLanguageTypes,
            'should_generate' => $shouldGenerate
        ]);
        
        return $shouldGenerate;
    }

    /**
     * Generate messages for all language groups
     */
    private function generateMessagesForAllLanguages(array $intent, array $eventData, array $guestData, string $userMessage): array
    {
        $languageGroups = $this->organizeGuestsByLanguage($guestData);
        $allActions = [];
        
        $this->logToChat('🔵 [LANGUAGE] Organized guests by language', [
            'language_groups' => array_keys($languageGroups),
            'total_languages' => count($languageGroups)
        ]);
        
        // For general messages, we need to handle differently
        if ($intent['type'] === 'general_message') {
            return $this->generateGeneralMessagesForAllLanguages($intent, $eventData, $guestData, $languageGroups, $userMessage);
        }
        
        // For group messages, we need to use the original guest data structure
        if (($intent['type'] ?? '') === 'group_message') {
            $this->logToChat('🔵 [LANGUAGE] Processing group message with original guest data structure');
            return $this->executeActionSingleLanguage($intent, $eventData, $guestData, $userMessage);
        }
        
        // For other types, process each language group separately
        foreach ($languageGroups as $language => $languageData) {
            $this->logToChat('info', '🔵 [LANGUAGE] Generating messages for language', [
                'language' => $language,
                'total_guests' => $languageData['total_guests']
            ]);

            if (($intent['type'] ?? '') === 'all_guests') {
                // Generate and apply messages for this language group directly
                $languageActions = $this->generateAllGuestMessagesForLanguageGroup($intent, $eventData, $languageData, $language);
            } else {
                // Create a modified intent for this language
                $languageIntent = $intent;
                $languageIntent['preferred_language'] = $language;

                // Generate actions for this language group using single-language path
                $languageActions = $this->executeActionSingleLanguage($languageIntent, $eventData, $languageData, $userMessage);
            }

            // Merge actions
            $allActions = array_merge($allActions, $languageActions);
        }
        
        return $allActions;
    }

    /**
     * Generate general messages for all languages and apply to guests
     */
    private function generateGeneralMessagesForAllLanguages(array $intent, array $eventData, array $guestData, array $languageGroups, string $userMessage): array
    {
        $allActions = [];
        
        $this->logToChat('🔵 [LANGUAGE] Generating general messages for all languages', [
            'languages' => array_keys($languageGroups)
        ]);
        
        // Generate a general message for each language
        foreach ($languageGroups as $language => $languageData) {
            $this->logToChat('🔵 [LANGUAGE] Generating general message for language', [
                'language' => $language,
                'total_guests' => $languageData['total_guests']
            ]);
            
            // Generate the general message for this language
            $generalMessage = $this->generateGeneralMessageForLanguage($intent, $eventData, $language);
            
            if ($generalMessage) {
                // Apply this language-specific message to all guests in this language group
                $guestActions = $this->applyGeneralMessageToLanguageGuests($generalMessage, $languageData, $eventData, $language);
                $allActions = array_merge($allActions, $guestActions);
            }
        }
        
        return $allActions;
    }

    /**
     * Generate and apply "all guests" messages for a single language group
     */
    private function generateAllGuestMessagesForLanguageGroup(array $intent, array $eventData, array $languageData, string $language): array
    {
        $actions = [];

        // Generate a general template for this language
        $generalTemplate = $this->generateGeneralTemplateForLanguage($intent, $eventData, $language);
        if (empty($generalTemplate) || strpos($generalTemplate, 'Please provide the missing event information') !== false) {
            $this->logToChat('warning', 'Failed to generate general template for language', [
                'language' => $language,
                'template' => $generalTemplate
            ]);
            return $actions;
        }

        $this->logToChat('info', '✅ [CHAT] Generated general template for language', [
            'language' => $language,
            'template_length' => strlen($generalTemplate)
        ]);

        // Apply this template to all guests in this language group
        foreach ($languageData['lists'] as $listId => $listData) {
            // Groups
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    if (isset($groupData['guests'])) {
                        foreach ($groupData['guests'] as $guest) {
                            $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);

                            // Handle both object and array guest structures
                            $guestId = is_object($guest) ? ($guest->id ?? null) : ($guest['id'] ?? null);
                            if ($guestId === null) {
                                continue;
                            }

                            $actions[] = [
                                'type' => 'update_guest_message',
                                'guestId' => (string)$guestId,
                                'content' => $personalizedMessage,
                                'language' => $language
                            ];
                        }
                    }
                }
            }

            // Ungrouped
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                    $guestId = is_object($guest) ? ($guest->id ?? null) : ($guest['id'] ?? null);
                    if ($guestId === null) {
                        continue;
                    }
                    $actions[] = [
                        'type' => 'update_guest_message',
                        'guestId' => (string)$guestId,
                        'content' => $personalizedMessage,
                        'language' => $language
                    ];
                }
            }
        }

        return $actions;
    }

    /**
     * Generate a general message for a specific language
     */
    private function generateGeneralMessageForLanguage(array $intent, array $eventData, string $language): string
    {
        $instructions = $intent['instructions'] ?? '';
        
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'additional_notes' => $instructions
        ];

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'general',
            [], // Empty guest data since we're generating a template
            $userInstructions,
            $language
        );

        if ($result['success'] && isset($result['general_message'])) {
            return $result['general_message'];
        }

        return '';
    }

    /**
     * Apply a general message to all guests in a language group
     */
    private function applyGeneralMessageToLanguageGuests(string $generalMessage, array $languageData, array $eventData, string $language): array
    {
        $actions = [];
        
        // Process all guests in this language group
        foreach ($languageData['lists'] as $listId => $listData) {
            // Process grouped guests
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    if (isset($groupData['guests'])) {
                        foreach ($groupData['guests'] as $guest) {
                            $personalizedMessage = $this->personalizeMessageForGuest($generalMessage, $guest, $eventData);
                            $actions[] = [
                                'type' => 'update_guest_message',
                                'guestId' => $guest->id,
                                'content' => $personalizedMessage,
                                'language' => $language
                            ];
                        }
                    }
                }
            }
            
            // Process ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $personalizedMessage = $this->personalizeMessageForGuest($generalMessage, $guest, $eventData);
                    $actions[] = [
                        'type' => 'update_guest_message',
                        'guestId' => $guest->id,
                        'content' => $personalizedMessage,
                        'language' => $language
                    ];
                }
            }
        }
        
        return $actions;
    }

    /**
     * Personalize a general message for a specific guest
     */
    private function personalizeMessageForGuest(string $generalMessage, $guest, array $eventData): string
    {
        $personalized = $generalMessage;
        
        // Handle both object and array guest structures
        $guestName = null;
        if (is_object($guest)) {
            $guestName = $guest->name ?? 'Guest';
        } else {
            $guestName = $guest['name'] ?? 'Guest';
        }
        
        // Replace placeholders
        $personalized = str_replace('{name}', $guestName, $personalized);
        $personalized = str_replace('{event_name}', $this->extractEventName($eventData), $personalized);
        $personalized = str_replace('{date}', $this->extractEventDate($eventData), $personalized);
        
        // Replace event placeholders
        $personalized = $this->replaceEventPlaceholders($personalized, $eventData);
        
        return $personalized;
    }



    /**
     * Organize guests by their preferred language
     */
    private function organizeGuestsByLanguage(array $guestData): array
    {
        $languageGroups = [];
        
        foreach ($guestData as $listId => $listData) {
            // Process grouped guests
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    if (isset($groupData['guests'])) {
                        foreach ($groupData['guests'] as $guest) {
                            // Handle both object and array guest structures
                            $language = null;
                            if (is_object($guest)) {
                                $language = $guest->language ?? 'en';
                            } else {
                                $language = $guest['language'] ?? 'en';
                            }
                            
                            if (!isset($languageGroups[$language])) {
                                $languageGroups[$language] = [
                                    'language' => $language,
                                    'lists' => [],
                                    'total_guests' => 0
                                ];
                            }
                            
                            if (!isset($languageGroups[$language]['lists'][$listId])) {
                                $languageGroups[$language]['lists'][$listId] = [
                                    'list_name' => $listData['list_name'] ?? "List {$listId}",
                                    'groups' => [],
                                    'ungrouped_guests' => []
                                ];
                            }
                            
                            if (!isset($languageGroups[$language]['lists'][$listId]['groups'][$groupId])) {
                                $languageGroups[$language]['lists'][$listId]['groups'][$groupId] = [
                                    'group_name' => $groupData['group_name'] ?? $groupData['name'] ?? "Group {$groupId}",
                                    'group_id' => $groupData['group_id'] ?? $groupId,
                                    'group_description' => $groupData['group_description'] ?? '',
                                    'group_color' => $groupData['group_color'] ?? '',
                                    'guests' => []
                                ];
                            }
                            
                            $languageGroups[$language]['lists'][$listId]['groups'][$groupId]['guests'][] = $guest;
                            $languageGroups[$language]['total_guests']++;
                        }
                    }
                }
            }
            
            // Process ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    // Handle both object and array guest structures
                    $language = null;
                    if (is_object($guest)) {
                        $language = $guest->language ?? 'en';
                    } else {
                        $language = $guest['language'] ?? 'en';
                    }
                    
                    if (!isset($languageGroups[$language])) {
                        $languageGroups[$language] = [
                            'language' => $language,
                            'lists' => [],
                            'total_guests' => 0
                        ];
                    }
                    
                    if (!isset($languageGroups[$language]['lists'][$listId])) {
                        $languageGroups[$language]['lists'][$listId] = [
                            'list_name' => $listData['list_name'] ?? "List {$listId}",
                            'groups' => [],
                            'ungrouped_guests' => []
                        ];
                    }
                    
                    $languageGroups[$language]['lists'][$listId]['ungrouped_guests'][] = $guest;
                    $languageGroups[$language]['total_guests']++;
                }
            }
        }
        return $languageGroups;
    }

    /**
     * Execute action for single language (original logic)
     */
    private function executeActionSingleLanguage(array $intent, array $eventData, array $guestData, string $userMessage): array
    {
        // Add language support to the intent
        $preferredLanguage = $intent['preferred_language'] ?? 'en';
        
        switch ($intent['type']) {
            case 'edit':
                // Edit intents are handled in processMessage to access current messages
                return [];
            case 'all_guests':
                return $this->generateAllGuestMessages($intent, $eventData, $guestData, $preferredLanguage);
                
            case 'specific_guest':
                return $this->generateGuestMessage($intent, $eventData, $guestData, $preferredLanguage);
                
            case 'group_message':
                return $this->generateGroupMessage($intent, $eventData, $guestData, $preferredLanguage);
                
            case 'list_message':
                return $this->generateListMessage($intent, $eventData, $guestData, $preferredLanguage);
                
            case 'conversational':
                return $this->generateConversationalResponse($intent, $eventData, $guestData);
                
            case 'help':
                return $this->generateHelpResponse($intent, $eventData, $guestData);
                
            case 'information':
                return $this->generateInformationResponse($intent, $eventData, $guestData);
                
            case 'general_message':
                return $this->generateGeneralMessage($intent, $eventData, $guestData, $preferredLanguage);
                
            default:
                return [];
        }
    }

    /**
     * Generate messages for all guests
     */
    private function generateAllGuestMessages(array $intent, array $eventData, array $guestData, string $preferredLanguage = 'en'): array
    {   
        // Check for missing critical information first
        $missingInfo = $this->checkMissingEventInfo($eventData);
        if (!empty($missingInfo)) {
            return [
                [
                    'type' => 'ask_for_info',
                    'missing_info' => $missingInfo,
                    'message' => $this->createMissingInfoMessage($missingInfo)
                ]
            ];
        }
        
        // Organize guests by language
        $languageGroups = $this->organizeGuestsByLanguage($guestData);
        
        $allActions = [];
        
        // Generate a message for each language group
        foreach ($languageGroups as $language => $languageData) {            
            // Generate a general template for this language
            $generalTemplate = $this->generateGeneralTemplateForLanguage($intent, $eventData, $language);
            
            if (empty($generalTemplate) || strpos($generalTemplate, 'Please provide the missing event information') !== false) {
                // Use fallback template instead of skipping
                $generalTemplate = $this->getFallbackTemplate($intent);
                
                if (empty($generalTemplate)) {
                    continue;
                }
            }
            
            // Apply this template to all guests in this language group
            $excludeGroups = $intent['exclude_groups'] ?? [];
            
            foreach ($languageData['lists'] as $listId => $listData) {
                if (isset($listData['groups'])) {
                    foreach ($listData['groups'] as $groupId => $groupData) {
                        // Skip excluded groups
                        if (in_array((string)$groupId, $excludeGroups)) {
                            continue;
                        }
                        
                        if (isset($groupData['guests'])) {
                            foreach ($groupData['guests'] as $guest) {
                                $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                                
                                // Handle both object and array guest structures
                                $guestId = null;
                                if (is_object($guest)) {
                                    $guestId = $guest->id;
                                } else {
                                    $guestId = $guest['id'];
                                }
                                
                                $allActions[] = [
                                    'type' => 'update_guest_message',
                                    'guestId' => $guestId,
                                    'content' => $personalizedMessage,
                                    'language' => $language
                                ];
                            }
                        }
                    }
                }
                
                if (isset($listData['ungrouped_guests'])) {
                    foreach ($listData['ungrouped_guests'] as $guest) {
                        $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                        
                        // Handle both object and array guest structures
                        $guestId = null;
                        if (is_object($guest)) {
                            $guestId = $guest->id;
                        } else {
                            $guestId = $guest['id'];
                        }
                        
                        $allActions[] = [
                            'type' => 'update_guest_message',
                            'guestId' => $guestId,
                            'content' => $personalizedMessage,
                            'language' => $language
                        ];
                    }
                }
            }
        }        
        if (empty($allActions)) {
            return [];
        }
        
        return $allActions;
    }

    /**
     * Generate message for specific guest
     */
    private function generateGuestMessage(array $intent, array $eventData, array $guestData, string $preferredLanguage = 'en'): array
    {
        // Check if guest_id is provided and convert to string
        if (!isset($intent['guest_id']) || $intent['guest_id'] === null) {
            $this->logToChat('Guest ID not found for specific guest request', ['intent' => $intent]);
            return [];
        }

        // Ensure guest_id is a string (handle arrays)
        $guestId = $intent['guest_id'];
        if (is_array($guestId)) {
            // If it's an array, use the first guest ID
            $guestId = $guestId[0] ?? null;
            $this->logToChat('Guest ID was an array, using first ID', ['original' => $intent['guest_id'], 'selected' => $guestId]);
        }
        
        if (!$guestId) {
            $this->logToChat('No valid guest ID found', ['intent' => $intent]);
            return [];
        }
        
        $guestId = (string)$guestId;
        
        // Find the guest and get their preferred language
        $guestLanguage = $preferredLanguage;
        $guestName = '';
        
        foreach ($guestData as $listData) {
            // Check grouped guests
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupData) {
                    if (isset($groupData['guests'])) {
                        foreach ($groupData['guests'] as $guest) {
                            $currentGuestId = null;
                            if (is_object($guest)) {
                                $currentGuestId = $guest->id;
                                if ($currentGuestId == $guestId) {
                                    $guestLanguage = $guest->language ?? 'en';
                                    $guestName = $guest->name ?? '';
                                    break 3;
                                }
                            } else {
                                $currentGuestId = $guest['id'];
                                if ($currentGuestId == $guestId) {
                                    $guestLanguage = $guest['language'] ?? 'en';
                                    $guestName = $guest['name'] ?? '';
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
            
            // Check ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $currentGuestId = null;
                    if (is_object($guest)) {
                        $currentGuestId = $guest->id;
                        if ($currentGuestId == $guestId) {
                            $guestLanguage = $guest->language ?? 'en';
                            $guestName = $guest->name ?? '';
                            break 2;
                        }
                    } else {
                        $currentGuestId = $guest['id'];
                        if ($currentGuestId == $guestId) {
                            $guestLanguage = $guest['language'] ?? 'en';
                            $guestName = $guest['name'] ?? '';
                            break 2;
                        }
                    }
                }
            }
        }
        
        $instructions = $intent['instructions'] ?? '';
        
        $this->logToChat('🔵 [CHAT] Generating message for specific guest', [
            'guest_id' => $guestId,
            'guest_name' => $guestName,
            'guest_language' => $guestLanguage,
            'tone' => $intent['tone'],
            'instructions' => $instructions
        ]);

        // Generate a template for the guest's preferred language
        $generalTemplate = $this->generateGeneralTemplateForLanguage($intent, $eventData, $guestLanguage);
        
        if (empty($generalTemplate) || strpos($generalTemplate, 'Please provide the missing event information') !== false) {
            $this->logToChat('warning', 'Failed to generate general template for guest language, using fallback', [
                'language' => $guestLanguage,
                'template' => $generalTemplate
            ]);
            
            // Use fallback template instead of returning empty
            $generalTemplate = $this->getFallbackTemplate($intent);
            
            if (empty($generalTemplate)) {
                $this->logToChat('error', 'Fallback template also failed for guest', [
                    'language' => $guestLanguage
                ]);
                return [];
            }
        }
        
        // Create a mock guest object for personalization
        $mockGuest = (object) [
            'id' => $guestId,
            'name' => $guestName
        ];
        
        $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $mockGuest, $eventData);
        
        return [[
            'type' => 'update_guest_message',
            'guestId' => $guestId,
            'content' => $personalizedMessage,
            'language' => $guestLanguage
        ]];
    }

    /**
     * Generate group message
     */
    private function generateGroupMessage(array $intent, array $eventData, array $guestData, string $preferredLanguage = 'en'): array
    {
        // Handle arrays in list_id and group_id
        $listId = $intent['list_id'];
        $groupId = $intent['group_id'];
        
        if (is_array($listId)) {
            $listId = $listId[0] ?? null;
            $this->logToChat('List ID was an array, using first ID', ['original' => $intent['list_id'], 'selected' => $listId]);
        }
        
        if (is_array($groupId)) {
            $groupId = $groupId[0] ?? null;
            $this->logToChat('Group ID was an array, using first ID', ['original' => $intent['group_id'], 'selected' => $groupId]);
        }

        // Note: We intentionally do not auto-resolve list_id here. The AI must return both list_id and group_id.
        
        if (!$listId || !$groupId) {
            $this->logToChat('Missing list_id or group_id for group message', ['intent' => $intent]);
            return [];
        }
        
        $this->logToChat('🔵 [CHAT] Starting bulk generation for group', [
            'group_id' => $groupId,
            'list_id' => $listId
        ]);
        
        // Check for missing critical information first
        $missingInfo = $this->checkMissingEventInfo($eventData);
        if (!empty($missingInfo)) {
            return [
                [
                    'type' => 'ask_for_info',
                    'missing_info' => $missingInfo,
                    'message' => $this->createMissingInfoMessage($missingInfo)
                ]
            ];
        }
        
        // Find the specific group and its guests
        $groupGuests = [];
        $groupData = null;
        
        $this->logToChat('🔍 [DEBUG] Looking for group', [
            'searching_for_list_id' => $listId,
            'searching_for_group_id' => $groupId,
            'guest_data_keys' => array_keys($guestData)
        ]);
        
        foreach ($guestData as $dataListId => $listData) {
            $this->logToChat('🔍 [DEBUG] Checking list', [
                'data_list_id' => $dataListId,
                'searching_for_list_id' => $listId,
                'list_match' => (string)$dataListId == (string)$listId,
                'has_groups' => isset($listData['groups']),
                'groups_count' => isset($listData['groups']) ? count($listData['groups']) : 0
            ]);
            
            if ((string)$dataListId == (string)$listId && isset($listData['groups'])) {
                $this->logToChat('🔍 [DEBUG] List found, checking groups', [
                    'list_id' => $dataListId,
                    'available_groups' => array_keys($listData['groups'])
                ]);
                
                foreach ($listData['groups'] as $dataGroupId => $groupData) {
                    $this->logToChat('🔍 [DEBUG] Checking group', [
                        'data_group_id' => $dataGroupId,
                        'searching_for_group_id' => $groupId,
                        'group_match' => (string)$dataGroupId == (string)$groupId,
                        'group_name' => $groupData['group_name'] ?? 'unknown'
                    ]);
                    
                    if ((string)$dataGroupId == (string)$groupId) {
                        $this->logToChat('🔍 [DEBUG] Group found!', [
                            'group_id' => $dataGroupId,
                            'group_name' => $groupData['group_name'] ?? 'unknown',
                            'guests_type' => gettype($groupData['guests']),
                            'guests_count' => is_array($groupData['guests']) ? count($groupData['guests']) : (method_exists($groupData['guests'], 'count') ? $groupData['guests']->count() : 'unknown')
                        ]);
                        
                        $guests = $groupData['guests'];
                        if (is_array($guests) || method_exists($guests, 'toArray')) {
                            $guestArray = is_array($guests) ? $guests : $guests->toArray();
                            foreach ($guestArray as $guest) {
                                $groupGuests[] = $guest;
                            }
                        }
                        break;
                    }
                }
                break;
            }
        }
        
        if (empty($groupGuests)) {
            $this->logToChat('No guests found in group', ['group_id' => $groupId, 'list_id' => $listId]);
            return [];
        }
        
        // Organize guests by language within this group
        $languageGroups = [];
        foreach ($groupGuests as $guest) {
            $language = null;
            if (is_object($guest)) {
                $language = $guest->language ?? 'en';
            } else {
                $language = $guest['language'] ?? 'en';
            }
            
            if (!isset($languageGroups[$language])) {
                $languageGroups[$language] = [];
            }
            $languageGroups[$language][] = $guest;
        }
        
        $this->logToChat('🔵 [CHAT] Group guests organized by language', [
            'group_id' => $groupId,
            'languages' => array_keys($languageGroups),
            'total_guests' => count($groupGuests)
        ]);
        
        $actions = [];
        
        // Generate messages for each language in the group
        foreach ($languageGroups as $language => $guestsInLanguage) {
            // Generate a template for this language
            $generalTemplate = $this->generateGeneralTemplateForLanguage($intent, $eventData, $language);
            
            if (!$generalTemplate) {
                $this->logToChat('warning', 'Failed to generate general template for language, using fallback', ['language' => $language]);
                
                // Use fallback template instead of skipping
                $generalTemplate = $this->getFallbackTemplate($intent);
                
                if (empty($generalTemplate)) {
                    $this->logToChat('error', 'Fallback template also failed for group', ['language' => $language]);
                    continue;
                }
            }
            
            $this->logToChat('✅ [CHAT] Generated general template for group language', [
                'language' => $language,
                'template_length' => strlen($generalTemplate)
            ]);
            
            // Add group template (use the first language for the group template)
            if ($language === array_key_first($languageGroups)) {
                $groupTemplate = $this->replaceEventPlaceholders($generalTemplate, $eventData);
                $actions[] = [
                    'type' => 'update_group_template',
                    'listId' => (string)$listId,
                    'groupId' => (string)$groupId,
                    'content' => $groupTemplate
                ];
            }
            
            // Personalize for each guest in this language
            foreach ($guestsInLanguage as $guest) {
                $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                
                $guestId = null;
                if (is_object($guest)) {
                    $guestId = $guest->id;
                } else {
                    $guestId = $guest['id'];
                }
                
                $actions[] = [
                    'type' => 'update_guest_message',
                    'guestId' => (string)$guestId,
                    'content' => $personalizedMessage,
                    'language' => $language
                ];
            }
        }
        
        $this->logToChat('✅ [CHAT] Generated messages for group', ['count' => count($actions)]);
        return $actions;
    }

    /**
     * Generate message for specific list
     */
    private function generateListMessage(array $intent, array $eventData, array $guestData, string $preferredLanguage = 'en'): array
    {
        // Handle arrays in list_id
        $listId = $intent['list_id'];
        
        if (is_array($listId)) {
            $listId = $listId[0] ?? null;
            $this->logToChat('List ID was an array, using first ID', ['original' => $intent['list_id'], 'selected' => $listId]);
        }
        
        if (!$listId) {
            $this->logToChat('Missing list_id for list message', ['intent' => $intent]);
            return [];
        }
        
        $this->logToChat('🔵 [CHAT] Starting bulk generation for list', [
            'list_id' => $listId,
            'tone' => $intent['tone']
        ]);
        
        // Check for missing critical information first
        $missingInfo = $this->checkMissingEventInfo($eventData);
        if (!empty($missingInfo)) {
            return [
                [
                    'type' => 'ask_for_info',
                    'missing_info' => $missingInfo,
                    'message' => $this->createMissingInfoMessage($missingInfo)
                ]
            ];
        }
        
        // Find the specific list and organize guests by language
        if (!isset($guestData[$listId])) {
            $this->logToChat('List not found in guest data', ['list_id' => $listId]);
            return [];
        }
        
        $listData = $guestData[$listId];
        
        // Organize guests in this list by language
        $languageGroups = [];
        
        // Process grouped guests
        if (isset($listData['groups'])) {
            foreach ($listData['groups'] as $groupId => $groupData) {
                if (isset($groupData['guests'])) {
                    $guests = $groupData['guests'];
                    if (is_array($guests) || method_exists($guests, 'toArray')) {
                        $guestArray = is_array($guests) ? $guests : $guests->toArray();
                        foreach ($guestArray as $guest) {
                            $language = is_object($guest) ? ($guest->language ?? 'en') : ($guest['language'] ?? 'en');
                            
                            if (!isset($languageGroups[$language])) {
                                $languageGroups[$language] = [
                                    'language' => $language,
                                    'groups' => [],
                                    'ungrouped_guests' => []
                                ];
                            }
                            
                            if (!isset($languageGroups[$language]['groups'][$groupId])) {
                                $languageGroups[$language]['groups'][$groupId] = [
                                    'group_name' => $groupData['group_name'] ?? $groupData['name'] ?? "Group {$groupId}",
                                    'group_id' => $groupData['group_id'] ?? $groupId,
                                    'guests' => []
                                ];
                            }
                            
                            $languageGroups[$language]['groups'][$groupId]['guests'][] = $guest;
                        }
                    }
                }
            }
        }
        
        // Process ungrouped guests
        if (isset($listData['ungrouped_guests'])) {
            $guests = $listData['ungrouped_guests'];
            if (is_array($guests) || method_exists($guests, 'toArray')) {
                $guestArray = is_array($guests) ? $guests : $guests->toArray();
                foreach ($guestArray as $guest) {
                    $language = is_object($guest) ? ($guest->language ?? 'en') : ($guest['language'] ?? 'en');
                    
                    if (!isset($languageGroups[$language])) {
                        $languageGroups[$language] = [
                            'language' => $language,
                            'groups' => [],
                            'ungrouped_guests' => []
                        ];
                    }
                    
                    $languageGroups[$language]['ungrouped_guests'][] = $guest;
                }
            }
        }
        
        $this->logToChat('🔵 [CHAT] Organized list guests by language', [
            'list_id' => $listId,
            'languages' => array_keys($languageGroups),
            'total_languages' => count($languageGroups)
        ]);
        
        $actions = [];
        
        // Generate messages for each language in the list
        foreach ($languageGroups as $language => $languageData) {
            $this->logToChat('🔵 [CHAT] Generating messages for list language', [
                'language' => $language,
                'groups_count' => count($languageData['groups']),
                'ungrouped_count' => count($languageData['ungrouped_guests'])
            ]);
            
            // Generate a template for this language
            $generalTemplate = $this->generateGeneralTemplateForLanguage($intent, $eventData, $language);
            
            if (empty($generalTemplate) || strpos($generalTemplate, 'Please provide the missing event information') !== false) {
                $this->logToChat('warning', 'Failed to generate general template for list language, using fallback', ['language' => $language]);
                
                // Use fallback template instead of skipping
                $generalTemplate = $this->getFallbackTemplate($intent);
                
                if (empty($generalTemplate)) {
                    $this->logToChat('error', 'Fallback template also failed for list language', ['language' => $language]);
                    continue;
                }
            }
            
            $this->logToChat('✅ [CHAT] Generated general template for list language', [
                'language' => $language,
                'template_length' => strlen($generalTemplate)
            ]);
            
            // Process groups in this language
            foreach ($languageData['groups'] as $groupId => $groupData) {
                // Add group template (use the first language for the group template)
                if ($language === array_key_first($languageGroups)) {
                    $groupTemplate = $this->replaceEventPlaceholders($generalTemplate, $eventData);
                    $actions[] = [
                        'type' => 'update_group_template',
                        'listId' => (string)$listId,
                        'groupId' => (string)$groupId,
                        'content' => $groupTemplate,
                        'language' => $language
                    ];
                }
                
                // Personalize for each guest in this group
                foreach ($groupData['guests'] as $guest) {
                    $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                    
                    $guestId = null;
                    if (is_object($guest)) {
                        $guestId = $guest->id;
                    } else {
                        $guestId = $guest['id'];
                    }
                    
                    $actions[] = [
                        'type' => 'update_guest_message',
                        'guestId' => (string)$guestId,
                        'content' => $personalizedMessage,
                        'language' => $language
                    ];
                }
            }
            
            // Process ungrouped guests in this language
            foreach ($languageData['ungrouped_guests'] as $guest) {
                $personalizedMessage = $this->personalizeMessageForGuest($generalTemplate, $guest, $eventData);
                
                $guestId = null;
                if (is_object($guest)) {
                    $guestId = $guest->id;
                } else {
                    $guestId = $guest['id'];
                }
                
                $actions[] = [
                    'type' => 'update_guest_message',
                    'guestId' => (string)$guestId,
                    'content' => $personalizedMessage,
                    'language' => $language
                ];
            }
        }
        
        $this->logToChat('✅ [CHAT] Generated messages for list', ['list_id' => $listId, 'count' => count($actions)]);
        return $actions;
    }

    /**
     * Generate conversational response
     */
    private function generateConversationalResponse(array $intent, array $eventData, array $guestData): array
    {
        $message = $intent['original_message'] ?? '';
        $message = strtolower(trim($message));
        
        $responses = [
            'hi' => 'Hello! 👋 How can I help you with your event invitations today?',
            'hello' => 'Hi there! 👋 I\'m here to help you create amazing invitation messages for your event.',
            'hey' => 'Hey! 👋 Ready to work on some great invitation messages?',
            'good morning' => 'Good morning! ☀️ How can I assist you with your event invitations today?',
            'good afternoon' => 'Good afternoon! 🌤️ Ready to create some invitation messages?',
            'good evening' => 'Good evening! 🌙 How can I help you with your event today?',
            'how are you' => 'I\'m doing great, thanks for asking! 😊 How can I help you with your event invitations?',
            'how\'s it going' => 'Everything is going well! 👍 Ready to help you create some amazing invitation messages.',
            'what\'s up' => 'Not much, just here to help you with your event invitations! 😄 What would you like to work on?',
            'thanks' => 'You\'re welcome! 😊 Is there anything else I can help you with?',
            'thank you' => 'You\'re very welcome! 😊 Let me know if you need any other assistance.',
            'thx' => 'No problem! 😊 What else can I help you with?',
            'bye' => 'Goodbye! 👋 Have a great day!',
            'goodbye' => 'See you later! 👋 Take care!',
            'see you' => 'See you! 👋 Don\'t hesitate to come back if you need help!',
            'talk to you later' => 'Talk to you later! 👋 Have a wonderful day!',
            'yes' => 'Great! 👍 What would you like me to help you with?',
            'no' => 'No problem! 😊 Let me know if you change your mind.',
            'ok' => 'Perfect! 👍 What\'s next?',
            'okay' => 'Alright! 👍 What would you like to work on?',
            'sure' => 'Excellent! 👍 How can I assist you?',
            'maybe' => 'No rush! 😊 Take your time and let me know when you\'re ready.'
        ];
        
        $response = $responses[$message] ?? 'Hello! 👋 How can I help you with your event invitations today?';
        
        return [[
            'type' => 'conversational_response',
            'content' => $response
        ]];
    }

    /**
     * Generate help response
     */
    private function generateHelpResponse(array $intent, array $eventData, array $guestData): array
    {
        $helpText = "🎉 **Welcome to your Event Invitation Assistant!**\n\n";
        $helpText .= "I can help you create personalized invitation messages for your event. Here's what I can do:\n\n";
        
        $helpText .= "📝 **Message Generation:**\n";
        $helpText .= "• Generate messages for all guests\n";
        $helpText .= "• Create messages for specific guests (by name)\n";
        $helpText .= "• Generate group-specific messages\n";
        $helpText .= "• Create list-specific messages\n";
        $helpText .= "• Generate general message templates\n\n";
        
        $helpText .= "🎨 **Tone Options:**\n";
        $helpText .= "• **Friendly** - Warm and casual\n";
        $helpText .= "• **Formal** - Professional and elegant\n";
        $helpText .= "• **Casual** - Relaxed and informal\n";
        $helpText .= "• **Professional** - Business-like\n\n";
        
        $helpText .= "💡 **Examples:**\n";
        $helpText .= "• \"Generate friendly messages for all guests\"\n";
        $helpText .= "• \"Write formal message for John\"\n";
        $helpText .= "• \"Create casual messages for Family group\"\n";
        $helpText .= "• \"Generate formal messages for list 38\"\n";
        $helpText .= "• \"Write friendly for Family group and formal for others\"\n\n";
        
        $helpText .= "🔍 **Information Queries:**\n";
        $helpText .= "• \"Who are my guests?\"\n";
        $helpText .= "• \"What groups do I have?\"\n";
        $helpText .= "• \"How many guests are in Family group?\"\n";
        $helpText .= "• \"Tell me about my guest lists\"\n\n";
        
        $helpText .= "Just ask me anything! 😊";
        
        return [[
            'type' => 'help_response',
            'content' => $helpText
        ]];
    }

    /**
     * Generate information response
     */
    private function generateInformationResponse(array $intent, array $eventData, array $guestData): array
    {
        $message = strtolower($intent['original_message'] ?? '');
        
        // Guest information
        if (preg_match('/\b(who|what)\b.*\b(guest|guests)\b/i', $message)) {
            $guestCount = 0;
            $guestNames = [];
            
            foreach ($guestData as $listId => $listData) {
                if (isset($listData['groups'])) {
                    foreach ($listData['groups'] as $groupId => $groupData) {
                        if (isset($groupData['guests'])) {
                            $guests = $groupData['guests'];
                            if (is_array($guests) || method_exists($guests, 'toArray')) {
                                $guestArray = is_array($guests) ? $guests : $guests->toArray();
                                foreach ($guestArray as $guest) {
                                    $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                                    if ($guestName) {
                                        $guestNames[] = $guestName;
                                        $guestCount++;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (isset($listData['ungrouped_guests'])) {
                    $guests = $listData['ungrouped_guests'];
                    if (is_array($guests) || method_exists($guests, 'toArray')) {
                        $guestArray = is_array($guests) ? $guests : $guests->toArray();
                        foreach ($guestArray as $guest) {
                            $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                            if ($guestName) {
                                $guestNames[] = $guestName;
                                $guestCount++;
                            }
                        }
                    }
                }
            }
            
            $response = "👥 **Your Guests:**\n\n";
            $response .= "You have **{$guestCount} guests** in total.\n\n";
            
            if (!empty($guestNames)) {
                $response .= "**Guest Names:**\n";
                $response .= "• " . implode("\n• ", $guestNames) . "\n\n";
            }
            
            return [[
                'type' => 'information_response',
                'content' => $response
            ]];
        }
        
        // Group information
        if (preg_match('/\b(group|groups)\b/i', $message)) {
            $groupInfo = [];
            
            foreach ($guestData as $listId => $listData) {
                if (isset($listData['groups'])) {
                    foreach ($listData['groups'] as $groupId => $groupData) {
                        $groupName = $groupData['name'] ?? "Group {$groupId}";
                        $guestCount = 0;
                        $guestNames = [];
                        
                        if (isset($groupData['guests'])) {
                            $guests = $groupData['guests'];
                            if (is_array($guests) || method_exists($guests, 'toArray')) {
                                $guestArray = is_array($guests) ? $guests : $guests->toArray();
                                foreach ($guestArray as $guest) {
                                    $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                                    if ($guestName) {
                                        $guestNames[] = $guestName;
                                        $guestCount++;
                                    }
                                }
                            }
                        }
                        
                        $groupInfo[] = [
                            'name' => $groupName,
                            'count' => $guestCount,
                            'guests' => $guestNames
                        ];
                    }
                }
            }
            
            $response = "📋 **Your Groups:**\n\n";
            
            if (!empty($groupInfo)) {
                foreach ($groupInfo as $group) {
                    $response .= "**{$group['name']}** ({$group['count']} guests)\n";
                    if (!empty($group['guests'])) {
                        $response .= "• " . implode("\n• ", $group['guests']) . "\n\n";
                    }
                }
            } else {
                $response .= "You don't have any groups set up yet.\n\n";
            }
            
            return [[
                'type' => 'information_response',
                'content' => $response
            ]];
        }
        
        // List information
        if (preg_match('/\b(list|lists)\b/i', $message)) {
            $listInfo = [];
            
            foreach ($guestData as $listId => $listData) {
                $listName = $listData['list_name'] ?? "List {$listId}";
                $guestCount = 0;
                $groupCount = 0;
                
                if (isset($listData['groups'])) {
                    $groupCount = count($listData['groups']);
                    foreach ($listData['groups'] as $groupId => $groupData) {
                        if (isset($groupData['guests'])) {
                            $guests = $groupData['guests'];
                            if (is_array($guests) || method_exists($guests, 'toArray')) {
                                $guestArray = is_array($guests) ? $guests : $guests->toArray();
                                $guestCount += count($guestArray);
                            }
                        }
                    }
                }
                
                if (isset($listData['ungrouped_guests'])) {
                    $guests = $listData['ungrouped_guests'];
                    if (is_array($guests) || method_exists($guests, 'toArray')) {
                        $guestArray = is_array($guests) ? $guests : $guests->toArray();
                        $guestCount += count($guestArray);
                    }
                }
                
                $listInfo[] = [
                    'id' => $listId,
                    'name' => $listName,
                    'guests' => $guestCount,
                    'groups' => $groupCount
                ];
            }
            
            $response = "📋 **Your Guest Lists:**\n\n";
            
            if (!empty($listInfo)) {
                foreach ($listInfo as $list) {
                    $response .= "**{$list['name']}** (ID: {$list['id']})\n";
                    $response .= "• {$list['guests']} guests\n";
                    $response .= "• {$list['groups']} groups\n\n";
                }
            } else {
                $response .= "You don't have any guest lists set up yet.\n\n";
            }
            
            return [[
                'type' => 'information_response',
                'content' => $response
            ]];
        }
        
        // Default information response
        $response = "ℹ️ **Event Information:**\n\n";
        $response .= "**Event:** " . $this->extractEventName($eventData) . "\n";
        $response .= "**Date:** " . $this->extractEventDate($eventData) . "\n";
        $response .= "**Location:** " . $this->extractEventLocation($eventData) . "\n";
        $response .= "**Description:** " . $this->extractAdditionalInfo($eventData) . "\n\n";
        
        $response .= "Ask me about your guests, groups, or lists for more specific information!";
        
        return [[
            'type' => 'information_response',
            'content' => $response
        ]];
    }

    /**
     * Generate general message
     */
    private function generateGeneralMessage(array $intent, array $eventData, array $guestData, string $preferredLanguage = 'en'): array
    {
        $instructions = $intent['instructions'] ?? '';
        
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'additional_notes' => $instructions
        ];

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'general',
            $guestData,
            $userInstructions,
            $preferredLanguage
        );

        if ($result['success'] && isset($result['general_message'])) {
            return [[
                'type' => 'update_general_message',
                'content' => $result['general_message'],
                'language' => $preferredLanguage
            ]];
        }

        return [];
    }

    /**
     * Create temporary event for message generation
     */
    private function createTemporaryEvent(array $eventData): \App\Shared\Models\Event
    {
        $event = new \App\Shared\Models\Event();
        $event->id = null;
        $event->name = $this->extractEventName($eventData);
        $event->description = $this->extractAdditionalInfo($eventData);
        $event->start_date = \Carbon\Carbon::parse($this->extractEventDate($eventData) !== 'TBD' ? $this->extractEventDate($eventData) : now());
        $event->end_date = isset($eventData['end_date']) ? \Carbon\Carbon::parse($eventData['end_date']) : null;
        $event->location = $this->extractEventLocation($eventData);
        $event->venue_name = $this->extractEventLocation($eventData); // Use location as venue name if venue_name not available
        $event->venue_address = $this->extractVenueAddress($eventData);
        $event->additional_information = $this->extractAdditionalInfo($eventData);
        $event->rsvp_enabled = $eventData['rsvp_enabled'] ?? false;
        $event->qr_checkin_enabled = $eventData['qr_checkin_enabled'] ?? false;
        $event->invitation_platforms = $eventData['invitation_platforms'] ?? [];
        
        return $event;
    }

    /**
     * Generate a general template with placeholders
     */
    private function generateGeneralTemplate(array $intent, array $eventData): string
    {
        return $this->generateGeneralTemplateForLanguage($intent, $eventData, 'en');
    }

    /**
     * Generate a general template for a specific language
     */
    private function generateGeneralTemplateForLanguage(array $intent, array $eventData, string $language): string
    {
        try {
            $prompt = $this->buildGeneralPromptForLanguage($intent, $eventData, $language);
            
            // If prompt indicates missing info, return a helpful message
            if (strpos($prompt, 'Please provide the missing event information') !== false) {
                return $prompt;
            }
            
            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json'
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 500,
                    'temperature' => 0.7
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return $this->cleanResponse($text);
            }
        } catch (\Exception $e) {
            $this->logToChat('General template generation failed', ['error' => $e->getMessage()]);
        }
        
        // Fallback template
        return $this->getFallbackTemplate($intent);
    }

    /**
     * Build prompt for general template generation
     */
    private function buildGeneralPrompt(array $intent, array $eventData): string
    {
        return $this->buildGeneralPromptForLanguage($intent, $eventData, 'en');
    }

    /**
     * Build prompt for general template generation in a specific language
     */
    private function buildGeneralPromptForLanguage(array $intent, array $eventData, string $language): string
    {
        // Log the event data structure for debugging
        $this->logToChat('info', '🔍 [EVENT_DATA] Building prompt with event data', [
            'event_data_keys' => array_keys($eventData),
            'event_data_sample' => array_slice($eventData, 0, 5, true)
        ]);
        
        // Extract event data with better fallbacks and validation
        $eventName = $this->extractEventName($eventData);
        $eventDate = $this->extractEventDate($eventData);
        $eventLocation = $this->extractEventLocation($eventData);
        $venueAddress = $this->extractVenueAddress($eventData);
        $additionalInfo = $this->extractAdditionalInfo($eventData);
        
        // Log the extracted values
        $this->logToChat('info', '🔍 [EVENT_DATA] Extracted event values', [
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'event_location' => $eventLocation,
            'venue_address' => $venueAddress,
            'additional_info' => $additionalInfo
        ]);
        
        // Check for missing critical information
        $missingInfo = [];
        if (!$eventName || $eventName === 'Event' || $eventName === 'Untitled Event') {
            $missingInfo[] = 'event name';
        }
        if (!$eventDate || $eventDate === 'TBD' || $eventDate === 'Not specified') {
            $missingInfo[] = 'event date and time';
        }
        
        // If critical info is missing, provide a fallback template
        if (!empty($missingInfo)) {
            $this->logToChat('warning', 'Missing critical event information for prompt generation', [
                'missing_info' => $missingInfo,
                'event_name' => $eventName,
                'event_date' => $eventDate
            ]);
            
            // Instead of returning an error message, provide a basic template
            $this->logToChat('info', 'Using fallback template due to missing event information');
            
            // Use a basic template that doesn't require specific event details
            $fallbackTemplate = $this->getFallbackTemplate($intent);
            return $fallbackTemplate;
        }
        
        $tone = strtolower($intent['tone']);
        
        $prompt = "You are an expert event invitation writer specializing in creating personalized, engaging invitations. Your task is to generate a professional invitation message that feels personal and compelling.\n\n";
        
        // Add language instruction
        $languageName = $this->getLanguageName($language);
        $prompt .= "LANGUAGE: Write the message in {$languageName} language.\n\n";
        
        // Event Context
        $prompt .= "EVENT DETAILS:\n";
        $prompt .= "- Event Name: {$eventName}\n";
        $prompt .= "- Date & Time: {$eventDate}\n";
        if ($eventLocation) {
            $prompt .= "- Location: {$eventLocation}\n";
        }
        if ($venueAddress) {
            $prompt .= "- Address: {$venueAddress}\n";
        }
        if ($additionalInfo) {
            $prompt .= "- Additional Info: {$additionalInfo}\n";
        }
        
        $prompt .= "\nTONE & STYLE:\n";
        
        if ($tone === 'friendly' || $tone === 'casual') {
            $prompt .= "Create a warm, casual, and enthusiastic tone:\n";
            $prompt .= "• Start with 'Hi {name}!' or 'Hey {name}!'\n";
            $prompt .= "• Use contractions naturally (we'd, you're, can't, etc.)\n";
            $prompt .= "• Show excitement with exclamation marks\n";
            $prompt .= "• Use friendly phrases like 'We'd love to have you!' or 'It's going to be amazing!'\n";
            $prompt .= "• End warmly: 'Hope to see you there!' or 'Can't wait!'\n";
        } elseif ($tone === 'formal' || $tone === 'professional') {
            $prompt .= "Create an elegant, respectful, and professional tone:\n";
            $prompt .= "• Start with 'Dear {name},' (with comma)\n";
            $prompt .= "• Use formal language without contractions\n";
            $prompt .= "• Keep exclamation marks minimal\n";
            $prompt .= "• Use dignified phrases like 'We cordially invite you' or 'Your presence would be an honor'\n";
            $prompt .= "• End formally: 'Sincerely,' or 'Warm regards,'\n";
        } else {
            $prompt .= "Create a balanced, warm but respectful tone:\n";
            $prompt .= "• Start with 'Hello {name},' or 'Hi {name},'\n";
            $prompt .= "• Mix casual warmth with appropriate formality\n";
            $prompt .= "• Use moderate enthusiasm\n";
            $prompt .= "• End with 'Looking forward to seeing you!'\n";
        }
        
        $prompt .= "\nMESSAGE STRUCTURE:\n";
        $prompt .= "• Use {name} as the guest name placeholder\n";
        $prompt .= "• Include all provided event details naturally in the flow\n";
        $prompt .= "• Write complete, engaging sentences\n";
        $prompt .= "• Keep the message between 50-150 words\n";
        $prompt .= "• Make it feel personal and inviting\n";
        $prompt .= "• Include RSVP and QR code information naturally\n";
        $prompt .= "\nOUTPUT FORMAT (IMPORTANT):\n";
        $prompt .= "• OUTPUT MUST BE MULTI-LINE with explicit newline characters (\\n)\n";
        $prompt .= "• Break the message into short lines: greeting, invite line, details line(s), closing\n";
        $prompt .= "• Do NOT return as a single paragraph. Ensure visible line breaks in plain text\n";
        
        // Add RSVP and QR code instructions based on event settings
        $rsvpEnabled = $eventData['rsvp_enabled'] ?? false;
        $qrEnabled = $eventData['qr_checkin_enabled'] ?? false;
        
        if ($rsvpEnabled || $qrEnabled) {
            $prompt .= "\nRSVP & QR CODE FEATURES:\n";
            $prompt .= "• At the end of the message, mention the link below is for RSVP and QR code\n";
            if ($rsvpEnabled) {
                $prompt .= "• Include that they can RSVP to confirm their attendance\n";
            }
            if ($qrEnabled) {
                $prompt .= "• Mention that a QR code will be available for easy check-in at the event\n";
            }
            $prompt .= "• Use natural language like 'The link below is for you to RSVP and get your QR code for check-in' or similar\n";
            $prompt .= "• Do NOT include the actual link - just mention that a link will be provided below\n";
        }
        
        if ($intent['instructions']) {
            $prompt .= "\nSPECIAL INSTRUCTIONS:\n";
            $prompt .= "• Include this specific instruction: '{$intent['instructions']}'\n";
        }
        
        $prompt .= "\nIMPORTANT GUIDELINES:\n";
        $prompt .= "• Stay consistent with the requested tone throughout the message\n";
        $prompt .= "• Only use information that was provided in the event details\n";
        $prompt .= "• Never use placeholder brackets like [Date], [Location], etc.\n";
        $prompt .= "• Never add placeholder text like '[removed - no details provided]'\n";
        $prompt .= "• Never mention food, activities, games unless specifically provided\n";
        $prompt .= "• Write complete, engaging sentences\n";
        $prompt .= "• Make the message feel personal and inviting\n";
        
        $prompt .= "\nGenerate a compelling invitation message with explicit line breaks (\\n) between lines:";
        
        return $prompt;
    }

    /**
     * Extract event name with better fallbacks
     */
    private function extractEventName(array $eventData): string
    {
        // Try multiple possible keys for event name
        $possibleKeys = ['name', 'event_name', 'title', 'event_title'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key]) && $eventData[$key] !== 'Event') {
                $this->logToChat('info', '🔍 [EXTRACT] Found event name', [
                    'key' => $key,
                    'value' => $eventData[$key]
                ]);
                return $eventData[$key];
            }
        }
        
        $this->logToChat('warning', '🔍 [EXTRACT] No event name found, using default', [
            'available_keys' => array_keys($eventData),
            'name_keys_checked' => $possibleKeys
        ]);
        return 'Event';
    }

    /**
     * Extract event date with better fallbacks
     */
    private function extractEventDate(array $eventData): string
    {
        // Try multiple possible keys for event date
        $possibleKeys = ['start_date', 'event_date', 'date', 'event_start_date'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key]) && $eventData[$key] !== 'TBD') {
                // Try to format the date nicely
                try {
                    $date = \Carbon\Carbon::parse($eventData[$key], 'UTC');
                    $organizerTimezone = \Illuminate\Support\Facades\Auth::user()->timezone ?? 'UTC';
                    $formattedDate = $date->setTimezone($organizerTimezone)->format('F j, Y \a\t g:i A');
                    $this->logToChat('info', '🔍 [EXTRACT] Found event date', [
                        'key' => $key,
                        'value' => $eventData[$key],
                        'formatted' => $formattedDate,
                        'organizer_timezone' => $organizerTimezone
                    ]);
                    return $formattedDate;
                } catch (\Exception $e) {
                    $this->logToChat('info', '🔍 [EXTRACT] Found event date (unformatted)', [
                        'key' => $key,
                        'value' => $eventData[$key],
                        'parse_error' => $e->getMessage()
                    ]);
                    return $eventData[$key];
                }
            }
        }
        
        $this->logToChat('warning', '🔍 [EXTRACT] No event date found, using default', [
            'available_keys' => array_keys($eventData),
            'date_keys_checked' => $possibleKeys
        ]);
        return 'TBD';
    }

    /**
     * Extract event location with better fallbacks
     */
    private function extractEventLocation(array $eventData): string
    {
        // Try multiple possible keys for location
        $possibleKeys = ['location', 'venue_name', 'venue', 'event_location'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key])) {
                return $eventData[$key];
            }
        }
        
        return '';
    }

    /**
     * Extract venue address with better fallbacks
     */
    private function extractVenueAddress(array $eventData): string
    {
        // Try multiple possible keys for venue address
        $possibleKeys = ['venue_address', 'address', 'event_address', 'location_address'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key])) {
                return $eventData[$key];
            }
        }
        
        return '';
    }

    /**
     * Extract additional information with better fallbacks
     */
    private function extractAdditionalInfo(array $eventData): string
    {
        // Try multiple possible keys for additional info
        $possibleKeys = ['additional_information', 'description', 'event_description', 'notes', 'event_notes'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key])) {
                return $eventData[$key];
            }
        }
        
        return '';
    }

    /**
     * Replace event placeholders in message
     */
    private function replaceEventPlaceholders(string $message, array $eventData): string
    {
        $replacements = [
            '{event_name}' => $this->extractEventName($eventData),
            '{date}' => $this->extractEventDate($eventData),
            '{location}' => $this->extractEventLocation($eventData),
            '{venue_name}' => $this->extractEventLocation($eventData), // Use location as venue name if venue_name not available
            '{venue_address}' => $this->extractVenueAddress($eventData)
        ];
        
        foreach ($replacements as $placeholder => $value) {
            if ($value && $value !== 'TBD' && $value !== 'Event') {
                $message = str_replace($placeholder, $value, $message);
            }
        }
        
        // Remove any remaining empty placeholders
        $message = preg_replace('/\{[^}]+\}/', '', $message);
        $message = preg_replace('/\s+/', ' ', $message); // Clean up extra spaces
        
        return trim($message);
    }

    /**
     * Get fallback template if AI generation fails
     */
    private function getFallbackTemplate(array $intent): string
    {
        $tone = strtolower($intent['tone']);
        $instructions = $intent['instructions'] ?? '';
        
        if ($tone === 'friendly' || $tone === 'casual') {
            $template = "Hi {name}!\n\n";
            $template .= "You're invited to join us for a special celebration! ";
            $template .= "We'd absolutely love to have you there with us.\n\n";
            if ($instructions) {
                $template .= $instructions . "\n\n";
            }
            $template .= "Hope to see you there!\n\n";
            $template .= "Please let us know if you can make it!";
        } elseif ($tone === 'formal' || $tone === 'professional') {
            $template = "Subject: Invitation to Special Event\n\n";
            $template .= "Dear {name},\n\n";
            $template .= "You are cordially invited to attend our special event. ";
            $template .= "Your presence would be greatly appreciated.\n\n";
            if ($instructions) {
                $template .= "Please note: " . $instructions . "\n\n";
            }
            $template .= "Kindly RSVP at your earliest convenience.\n\n";
            $template .= "Warm regards";
        } else {
            // Balanced tone
            $template = "Subject: You're Invited!\n\n";
            $template .= "Hello {name},\n\n";
            $template .= "We would like to invite you to join us for a special event. ";
            $template .= "It would be wonderful to have you celebrate with us.\n\n";
            if ($instructions) {
                $template .= $instructions . "\n\n";
            }
            $template .= "Looking forward to seeing you!\n\n";
            $template .= "Please let us know if you can attend.";
        }
        
        return $template;
    }

    /**
     * Build prompt for AI analysis of user message
     */
    private function buildAnalysisPrompt(string $message, array $eventData, array $guestData, array $history): string
    {
        $prompt = "You are an intelligent AI assistant helping with wedding/event invitation message generation. Analyze the user's request and provide a structured response.\n\n";
        $prompt .= "USER REQUEST: \"{$message}\"\n\n";
        
        // Enhanced Event context
        $prompt .= "📅 EVENT DETAILS:\n";
        $prompt .= "- Event Name: " . $this->extractEventName($eventData) . "\n";
        $prompt .= "- Date & Time: " . $this->extractEventDate($eventData) . "\n";
        $prompt .= "- Location: " . $this->extractEventLocation($eventData) . "\n";
        $prompt .= "- Description: " . $this->extractAdditionalInfo($eventData) . "\n";
        $prompt .= "- RSVP Enabled: " . (($eventData['rsvp_enabled'] ?? false) ? 'Yes' : 'No') . "\n";
        $prompt .= "- QR Check-in: " . (($eventData['qr_checkin_enabled'] ?? false) ? 'Yes' : 'No') . "\n\n";
        
        // Enhanced Guest and group context with language information
        $prompt .= "👥 GUEST LISTS & GROUPS:\n";
        foreach ($guestData as $listId => $listData) {
            $prompt .= "📋 Guest List: " . ($listData['list_name'] ?? "List {$listId}") . " (ID: {$listId})\n";
            
            if (isset($listData['groups']) && is_array($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    $groupName = $groupData['group_name'] ?? $groupData['name'] ?? "Group {$groupId}";
                    $prompt .= "  - Group {$groupId}: {$groupName} (ID:{$groupId}) - ";
                    $guestNames = [];
                    $languages = [];
                    if (isset($groupData['guests'])) {
                        $guests = $groupData['guests'];
                        if (is_array($guests) || method_exists($guests, 'toArray')) {
                            $guestArray = is_array($guests) ? $guests : $guests->toArray();
                            foreach ($guestArray as $guest) {
                                $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                                $guestId = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                                $guestLanguage = is_object($guest) ? ($guest->language ?? 'en') : ($guest['language'] ?? 'en');
                                if ($guestName && $guestId) {
                                    $guestNames[] = $guestName . " (ID:{$guestId}, Lang:{$guestLanguage})";
                                    if (!in_array($guestLanguage, $languages)) {
                                        $languages[] = $guestLanguage;
                                    }
                                }
                            }
                        }
                    }
                    $prompt .= implode(', ', $guestNames);
                    if (!empty($languages)) {
                        $prompt .= " [Languages: " . implode(', ', $languages) . "]";
                    }
                    $prompt .= "\n";
                }
            }
            
            if (isset($listData['ungrouped_guests'])) {
                $prompt .= "  - Ungrouped: ";
                $ungroupedNames = [];
                $languages = [];
                $guests = $listData['ungrouped_guests'];
                if (is_array($guests) || method_exists($guests, 'toArray')) {
                    $guestArray = is_array($guests) ? $guests : $guests->toArray();
                    foreach ($guestArray as $guest) {
                        $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                        $guestId = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                        $guestLanguage = is_object($guest) ? ($guest->language ?? 'en') : ($guest['language'] ?? 'en');
                        if ($guestName && $guestId) {
                            $ungroupedNames[] = $guestName . " (ID:{$guestId}, Lang:{$guestLanguage})";
                            if (!in_array($guestLanguage, $languages)) {
                                $languages[] = $guestLanguage;
                            }
                        }
                    }
                }
                $prompt .= implode(', ', $ungroupedNames);
                if (!empty($languages)) {
                    $prompt .= " [Languages: " . implode(', ', $languages) . "]";
                }
                $prompt .= "\n";
            }
        }
        
        $prompt .= "\n🎯 INTELLIGENT ANALYSIS INSTRUCTIONS:\n\n";
        $prompt .= "📝 UNDERSTANDING USER INTENT:\n";
        $prompt .= "- FIRST: Determine if this is a conversational request or an action request\n";
        $prompt .= "- CONVERSATIONAL: Greetings, help requests, information queries, casual chat\n";
        $prompt .= "- ACTION: Message generation, guest targeting, group targeting, list targeting\n";
        $prompt .= "- Analyze the user's request for tone, target audience, and special instructions\n";
        $prompt .= "- Detect if they want messages for all guests, specific guests, or groups\n";
        $prompt .= "- Identify any special requirements (dress code, food, kids, etc.)\n";
        $prompt .= "- IMPORTANT: Look for complex requests with multiple actions (e.g., 'friendly for Family group AND formal for others')\n";
        $prompt .= "- LANGUAGE AWARENESS: The system will automatically generate messages in each guest's preferred language\n\n";
        
        $prompt .= "🎨 TONE DETECTION:\n";
        $prompt .= "- 'formal': Professional, traditional, respectful language\n";
        $prompt .= "- 'friendly': Warm, casual, personal, welcoming\n";
        $prompt .= "- 'professional': Business-like, structured, clear\n";
        $prompt .= "- 'casual': Relaxed, informal, conversational\n\n";
        
        $prompt .= "👥 TARGET DETECTION:\n";
        $prompt .= "- 'all_guests': When user mentions 'everyone', 'all', 'all guests', 'for all guests', 'generate message for everyone', 'generate messages for everyone', 'create messages for everyone', 'write messages for everyone'\n";
        $prompt .= "- 'specific_guest': When a specific name is mentioned (like 'John', 'Hanaa')\n";
        $prompt .= "- 'group_message': When a group name is mentioned. IMPORTANT: Match the EXACT group name from the list above (e.g., 'Malaysia Number', 'Saudi Number', 'Family', 'Work'). Use partial matching if needed (e.g., 'malaysia' should match 'Malaysia Number')\n";
        $prompt .= "- 'list_message': When user mentions 'list' or 'guest list' with a specific list ID\n";
        $prompt .= "- 'general_message': When user wants a general template\n";
        $prompt .= "- 'response_only': ONLY when you cannot determine a specific action\n\n";
        $prompt .= "🔄 MULTI-ACTION DETECTION:\n";
        $prompt .= "- Look for keywords like 'AND', 'for', 'make', 'generate' with multiple targets\n";
        $prompt .= "- Examples: 'friendly for Family AND formal for others', 'casual for John AND professional for everyone else'\n";
        $prompt .= "- When you see multiple actions, use 'multi_actions' array\n\n";
        
        $prompt .= "📋 EXAMPLES:\n\n";
        $prompt .= "🎭 CONVERSATIONAL EXAMPLES:\n";
        $prompt .= "User: 'hi' → Return: {\"type\": \"conversational\", \"response\": \"Hello! 👋 How can I help you with your event invitations today?\"}\n";
        $prompt .= "User: 'hello' → Return: {\"type\": \"conversational\", \"response\": \"Hi there! 👋 I'm here to help you create amazing invitation messages for your event.\"}\n";
        $prompt .= "User: 'how are you' → Return: {\"type\": \"conversational\", \"response\": \"I'm doing great, thanks for asking! 😊 How can I help you with your event invitations?\"}\n";
        $prompt .= "User: 'help' → Return: {\"type\": \"help\", \"response\": \"🎉 **Welcome to your Event Invitation Assistant!**\\n\\n**What I can do for you:**\\n\\n📝 **Message Generation:**\\n• Generate messages for all guests in their preferred languages\\n• Create personalized messages for specific guests\\n• Generate group-specific messages\\n• Create messages for specific guest lists\\n\\n🌍 **Multi-Language Support:**\\n• Automatically detects guest preferred languages\\n• Generates messages in each guest's language\\n• Supports English, Spanish, French, German, and more\\n\\n🎨 **Message Tones:**\\n• **Friendly:** Warm and personal\\n• **Formal:** Professional and respectful\\n• **Casual:** Relaxed and informal\\n• **Professional:** Business-like and structured\\n\\n💡 **Examples:**\\n• \\\"Generate friendly messages for all guests\\\"\\n• \\\"Write formal message for John\\\"\\n• \\\"Create casual messages for Family group\\\"\\n• \\\"Generate professional messages for Work group\\\"\\n\\nJust tell me what you need! 😊\"}\n";
        $prompt .= "User: 'what can you do' → Return: {\"type\": \"help\", \"response\": \"🤖 **Your AI Event Assistant**\\n\\n**My Capabilities:**\\n\\n📋 **Guest Management:**\\n• View all your guests and groups\\n• Get guest counts and group information\\n• See guest list details\\n\\n✉️ **Message Creation:**\\n• Generate personalized invitation messages\\n• Create different tones (friendly, formal, casual, professional)\\n• Target specific guests, groups, or entire lists\\n• Handle complex multi-action requests\\n\\n🌍 **Multi-Language Support:**\\n• Automatically generates messages in each guest's preferred language\\n• Supports multiple languages simultaneously\\n• Ensures all guests receive messages in their language\\n\\n🎯 **Smart Features:**\\n• Natural language understanding\\n• Multi-action processing (e.g., \\\"friendly for Family AND formal for others\\\")\\n• Context-aware responses\\n• Intelligent guest and group detection\\n\\nAsk me anything about your event invitations! 🎉\"}\n";
        $prompt .= "User: 'who are my guests' → Return: {\"type\": \"information\", \"response\": \"📊 **Guest Summary:**\\n\\n• **Family Group:** 1 guest\\n• **Work Group:** 1 guest\\n• **Friends Group:** 1 guest\\n• **Extended Family Group:** 6 guests\\n• **Colleagues Group:** 1 guest\\n• **Close Friends Group:** 1 guest\\n• **Ungrouped Guests:** 3 guests\\n\\n**Total:** 14 guests across all lists\"}\n";
        $prompt .= "User: 'what groups do I have' → Return: {\"type\": \"information\", \"response\": \"📋 **Your Guest Groups:**\\n\\n• Family\\n• Work\\n• Friends\\n• Extended Family\\n• Colleagues\\n• Close Friends\\n\\nYou can generate messages for any of these groups!\"}\n";
        $prompt .= "User: 'how many guest i have' → Return: {\"type\": \"information\", \"response\": \"📊 **Guest Count Summary:**\\n\\n• **Family Group:** 1 guest\\n• **Work Group:** 1 guest\\n• **Friends Group:** 1 guest\\n• **Extended Family Group:** 6 guests\\n• **Colleagues Group:** 1 guest\\n• **Close Friends Group:** 1 guest\\n• **Ungrouped Guests:** 3 guests\\n\\n**Total:** 14 guests across all lists\"}\n";
        $prompt .= "User: 'thanks' → Return: {\"type\": \"conversational\", \"response\": \"You're welcome! 😊 Is there anything else I can help you with?\"}\n";
        $prompt .= "User: 'bye' → Return: {\"type\": \"conversational\", \"response\": \"Goodbye! 👋 Have a great day!\"}\n\n";
        
        $prompt .= "⚡ ACTION EXAMPLES:\n";
        $prompt .= "User: 'Generate friendly message for Hanaa' → Return: {\"type\": \"specific_guest\", \"guest_id\": \"426\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Write formal messages for all guests' → Return: {\"type\": \"all_guests\", \"tone\": \"formal\"}\n";
        $prompt .= "User: 'Make messages kid-friendly' → Return: {\"type\": \"all_guests\", \"instructions\": \"kid-friendly\"}\n";
        $prompt .= "User: 'Generate messages for Family group' → Return: {\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate messages for Group 50' → Return: {\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Write formal messages for Work group' → Return: {\"type\": \"group_message\", \"group_id\": \"45\", \"list_id\": \"37\", \"tone\": \"formal\"}\n";
        $prompt .= "User: 'Create casual messages for Friends group' → Return: {\"type\": \"group_message\", \"group_id\": \"46\", \"list_id\": \"37\", \"tone\": \"casual\"}\n";
        $prompt .= "User: 'Generate message for group malaysia number' → Return: {\"type\": \"group_message\", \"group_id\": \"1\", \"list_id\": \"1\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate message for malaysia number group' → Return: {\"type\": \"group_message\", \"group_id\": \"1\", \"list_id\": \"1\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate message for saudi number group' → Return: {\"type\": \"group_message\", \"group_id\": \"2\", \"list_id\": \"1\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate formal message for all guests in list 299' → Return: {\"type\": \"list_message\", \"list_id\": \"299\", \"tone\": \"formal\"}\n";
        $prompt .= "User: 'Write messages for everyone' → Return: {\"type\": \"all_guests\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate message for everyone' → Return: {\"type\": \"all_guests\", \"tone\": \"friendly\"}\n";
        $prompt .= "User: 'Generate messages for everyone' → Return: {\"type\": \"all_guests\", \"tone\": \"friendly\"}\n\n";
        $prompt .= "MULTI-ACTION EXAMPLES:\n";
        $prompt .= "User: 'Generate friendly messages for Family group and formal messages for other guests'\n";
        $prompt .= "→ Return: {\"multi_actions\": [{\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"}, {\"type\": \"all_guests\", \"tone\": \"formal\", \"exclude_groups\": [\"50\"]}]}\n\n";
        $prompt .= "User: 'Make Family group friendly and Work group formal'\n";
        $prompt .= "→ Return: {\"multi_actions\": [{\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"}, {\"type\": \"group_message\", \"group_id\": \"51\", \"list_id\": \"39\", \"tone\": \"formal\"}]}\n\n";
        $prompt .= "User: 'Generate message for guests within the family group and make it friendly and for the other guests make the message formal'\n";
        $prompt .= "→ Return: {\"multi_actions\": [{\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"}, {\"type\": \"all_guests\", \"tone\": \"formal\", \"exclude_groups\": [\"50\"]}]}\n\n";

        // Edit intent examples
        $prompt .= "EDIT ACTION EXAMPLES:\n";
        $prompt .= "User: 'Make the Family group message shorter and remove the RSVP line' → Return: {\"type\": \"edit\", \"target\": \"group_message\", \"list_id\": \"39\", \"group_id\": \"50\", \"instructions\": \"shorten; remove RSVP\"}\n";
        $prompt .= "User: 'Change Hanaa's message to be more formal and add dress code' → Return: {\"type\": \"edit\", \"target\": \"specific_guest\", \"guest_id\": \"426\", \"instructions\": \"more formal; include dress code\"}\n";
        $prompt .= "User: 'Make all guest messages shorter' → Return: {\"type\": \"edit\", \"target\": \"all_guests\", \"instructions\": \"shorten\"}\n\n";
        
        $prompt .= "🔍 RETURN THIS JSON STRUCTURE:\n";
        $prompt .= "For single actions:\n";
        $prompt .= "{\n";
        $prompt .= "  \"type\": \"conversational|help|information|all_guests|specific_guest|group_message|list_message|general_message|response_only\",\n";
        $prompt .= "  \"tone\": \"friendly|formal|professional|casual\",\n";
        $prompt .= "  \"guest_id\": \"exact_guest_id_from_list_above\",\n";
        $prompt .= "  \"list_id\": \"exact_list_id_from_above\",\n";
        $prompt .= "  \"group_id\": \"exact_group_id_from_above\",\n";
        $prompt .= "  \"instructions\": \"special_instructions_detected\",\n";
        $prompt .= "  \"response\": \"friendly_confirmation_of_what_you_understood\"\n";
        $prompt .= "}\n\n";
        $prompt .= "For multiple actions:\n";
        $prompt .= "{\n";
        $prompt .= "  \"multi_actions\": [\n";
        $prompt .= "    {\"type\": \"group_message\", \"group_id\": \"50\", \"list_id\": \"39\", \"tone\": \"friendly\"},\n";
        $prompt .= "    {\"type\": \"all_guests\", \"tone\": \"formal\", \"exclude_groups\": [\"50\"]}\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        
        $prompt .= "⚠️ CRITICAL RULES:\n";
        $prompt .= "1. FIRST: Determine if this is conversational or action-based\n";
        $prompt .= "2. CONVERSATIONAL: Greetings, help, information queries → Return type 'conversational', 'help', or 'information'\n";
        $prompt .= "3. ACTION: Message generation → Find exact IDs from the guest lists above\n";
        $prompt .= "4. Return SINGLE IDs (strings), never arrays\n";
        $prompt .= "5. If multiple matches, choose the first one\n";
        $prompt .= "6. Provide a helpful, friendly response\n";
        $prompt .= "7. Detect special instructions (kids, dress code, food, etc.)\n";
        $prompt .= "8. For complex requests with multiple actions, use 'multi_actions' array\n";
        $prompt .= "9. Each action in multi_actions should be a complete action object\n";
        $prompt .= "10. Use 'exclude_groups' when targeting 'all_guests' but excluding specific groups\n";
        $prompt .= "11. FORMATTING: Use **bold** for emphasis and \\n for line breaks in responses\n";
        $prompt .= "12. For edit requests, set type=\\\"edit\\\", include target (all_guests|specific_guest|group_message|general_message), and provide IDs where applicable.\n";
        $prompt .= "13. Edits must modify existing text; do NOT rewrite from scratch.\n";
        $prompt .= "14. GROUP MATCHING: When user mentions a group name, look for EXACT or PARTIAL matches in the group names above.\n\n";
        
        $prompt .= "Return ONLY the JSON response, no additional text.";
        
        // Log the complete prompt for debugging
        $this->logToChat('🔵 [AI PROMPT] Complete AI Analysis Prompt', [
            'prompt_length' => strlen($prompt),
            'prompt' => $prompt
        ]);
        
        return $prompt;
    }

    /**
     * Generate intelligent response based on AI analysis and actions
     */
    private function generateIntelligentResponse(array $aiAnalysis, array $actions): string
    {
        $type = $aiAnalysis['type'] ?? 'response_only';
        $tone = $aiAnalysis['tone'] ?? 'friendly';
        $instructions = $aiAnalysis['instructions'] ?? '';
        
        $response = '';
        
        switch ($type) {
            case 'all_guests':
                $response = "✅ **Message Generation Complete!**\n\n";
                $response .= "🎯 **Action:** Generated {$tone} messages for all guests";
                
                // Add language information
                $languages = $this->getLanguagesFromActions($actions);
                if (!empty($languages)) {
                    $languageNames = array_map([$this, 'getLanguageName'], $languages);
                    $response .= "\n🌍 **Languages:** " . implode(', ', $languageNames);
                }
                
                if ($instructions) {
                    $response .= "\n📝 **Special Instructions:** {$instructions}";
                }
                $response .= "\n\n📋 **Next Steps:**\n• Check the message boxes below to see the personalized messages\n• Review and edit any messages as needed\n• Messages are ready for sending!";
                break;
                
            case 'specific_guest':
                $guestName = $this->getGuestNameById($aiAnalysis['guest_id'] ?? null);
                $response = "✅ **Personalized Message Created!**\n\n";
                $response .= "👤 **Guest:** {$guestName}\n";
                $response .= "🎨 **Tone:** {$tone}";
                if ($instructions) {
                    $response .= "\n📝 **Special Instructions:** {$instructions}";
                }
                $response .= "\n\n📋 **Status:** The message has been updated in their text box and is ready for review.";
                break;
                
            case 'group_message':
                $groupName = $this->getGroupNameById($aiAnalysis['list_id'] ?? null, $aiAnalysis['group_id'] ?? null);
                $response = "✅ **Group Messages Generated!**\n\n";
                $response .= "👥 **Group:** {$groupName}\n";
                $response .= "🎨 **Tone:** {$tone}";
                if ($instructions) {
                    $response .= "\n📝 **Special Instructions:** {$instructions}";
                }
                $response .= "\n\n📋 **Status:** Each guest in the group now has a personalized message ready for review.";
                break;
                
            case 'general_message':
                $response = "✅ Created a {$tone} general message template";
                if ($instructions) {
                    $response .= " with special instructions: {$instructions}";
                }
                $response .= ". You can use this as a base for all your invitations.";
                break;
            
            case 'edit':
                $response = "✏️ **Applied your edits**";
                if (!empty($instructions)) {
                    $response .= "\n\n📝 **Instructions:** {$instructions}";
                }
                $response .= "\n\n📋 **Status:** The existing message(s) were updated without rewriting from scratch.";
                break;
                
            case 'conversational':
                // For conversational responses, use the AI's response or return content from actions
                if (!empty($actions) && isset($actions[0]['content'])) {
                    return $actions[0]['content'];
                }
                // Use the AI's response if available
                if (isset($aiAnalysis['response'])) {
                    return $aiAnalysis['response'];
                }
                $response = "Hello! 👋 How can I help you with your event invitations today?";
                break;
                
            case 'help':
                // For help responses, use the AI's response or return content from actions
                if (!empty($actions) && isset($actions[0]['content'])) {
                    return $actions[0]['content'];
                }
                // Use the AI's response if available
                if (isset($aiAnalysis['response'])) {
                    return $aiAnalysis['response'];
                }
                $response = "I'm here to help you create amazing invitation messages! Ask me about generating messages for guests, groups, or lists.";
                break;
                
            case 'information':
                // For information responses, use the AI's response or return content from actions
                if (!empty($actions) && isset($actions[0]['content'])) {
                    return $actions[0]['content'];
                }
                // Use the AI's response if available
                if (isset($aiAnalysis['response'])) {
                    return $aiAnalysis['response'];
                }
                $response = "I can provide information about your guests, groups, and lists. What would you like to know?";
                break;
                
            case 'list_message':
                $response = "✅ **List Messages Generated!**\n\n";
                $response .= "📋 **Target:** All guests in the specified list\n";
                $response .= "🎨 **Tone:** {$tone}";
                if ($instructions) {
                    $response .= "\n📝 **Special Instructions:** {$instructions}";
                }
                $response .= "\n\n📋 **Status:** Check the message boxes below to see the personalized messages for all guests in this list.";
                break;
                
            default:
                $this->logToChat('🔵 [CHAT] Unknown action type', [
                    'type' => $type,
                    'analysis' => $aiAnalysis
                ]);
                $response = $aiAnalysis['response'] ?? "I understand your request, but I need more specific information about what you'd like me to do. Please try being more specific about which guests or groups you want me to generate messages for.";
        }
        
        return $response;
    }
    
    /**
     * Get guest name by ID
     */
    private function getGuestNameById(?string $guestId): string
    {
        if (!$guestId) return 'the selected guest';
        
        foreach ($this->guestDataContext as $listData) {
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupData) {
                    if (isset($groupData['guests'])) {
                        $guests = $groupData['guests'];
                        if (is_array($guests) || method_exists($guests, 'toArray')) {
                            $guestArray = is_array($guests) ? $guests : $guests->toArray();
                            foreach ($guestArray as $guest) {
                                $guestIdFromData = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                                if ((string)$guestIdFromData === (string)$guestId) {
                                    return is_object($guest) ? ($guest->name ?? 'the guest') : ($guest['name'] ?? 'the guest');
                                }
                            }
                        }
                    }
                }
            }
            
            if (isset($listData['ungrouped_guests'])) {
                $guests = $listData['ungrouped_guests'];
                if (is_array($guests) || method_exists($guests, 'toArray')) {
                    $guestArray = is_array($guests) ? $guests : $guests->toArray();
                    foreach ($guestArray as $guest) {
                        $guestIdFromData = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                        if ((string)$guestIdFromData === (string)$guestId) {
                            return is_object($guest) ? ($guest->name ?? 'the guest') : ($guest['name'] ?? 'the guest');
                        }
                    }
                }
            }
        }
        
        return 'the selected guest';
    }
    
    /**
     * Combine multiple action responses into a single coherent response
     */
    private function combineMultiActionResponses(array $responses): string
    {
        if (empty($responses)) {
            return "✅ Request Processed Successfully!\n\nI've completed your request. Check the message boxes below for the generated messages.";
        }
        
        if (count($responses) === 1) {
            return $responses[0];
        }
        
        // Combine multiple responses intelligently
        $combined = "✅ Multiple Actions Completed!\n\n";
        $combined .= "🎯 Actions Performed:\n\n";
        
        foreach ($responses as $index => $response) {
            $bullet = $index + 1;
            // Clean up the response to avoid double formatting
            $cleanResponse = str_replace(['✅ ', '🎯 ', '👤 ', '👥 ', '📋 ', '🎨 ', '📝 ', '🌍 '], '', $response);
            $cleanResponse = str_replace(['\n\n📋 Next Steps:', '\n\n📋 Status:', '\n\n📋 Usage:'], '', $cleanResponse);
            $combined .= "{$bullet}. " . $cleanResponse . "\n";
        }
        
        $combined .= "\n📋 Status: All messages have been updated in their respective text boxes and are ready for review.";
        
        return $combined;
    }
    
    /**
     * Get group name by IDs
     */
    private function getGroupNameById(?string $listId, ?string $groupId): string
    {
        if (!$listId || !$groupId) return 'the selected group';
        
        if (isset($this->guestDataContext[$listId]['groups'][$groupId])) {
            $groupData = $this->guestDataContext[$listId]['groups'][$groupId];
            return $groupData['group_name'] ?? 'the selected group';
        }
        
        return 'the selected group';
    }

    /**
     * Determine if the analysis/user message indicates an edit request
     */
    private function isEditIntent(array $analysis, string $userMessage): bool
    {
        if (($analysis['type'] ?? '') === 'edit') {
            return true;
        }
        return (bool)preg_match('/\b(edit|update|modify|change|shorten|remove|add)\b/i', $userMessage);
    }

    /**
     * Apply edit intent to existing messages without rewriting from scratch
     */
    private function applyEditIntent(array $analysis, array $eventData, array $guestData, array $currentMessages, string $userMessage): array
    {
        $target = $analysis['target'] ?? '';
        $instructions = $analysis['instructions'] ?? $this->extractInstructions($userMessage);
        $actions = [];

        // Helper to apply a minimal edit using AI with the old text as context
        $applyEdit = function (string $oldText) use ($instructions): string {
            try {
                $edited = app(\App\Services\EventMessageGenerationService::class)->editMessage($oldText, $instructions);
                return trim($edited) !== '' ? $edited : $oldText;
            } catch (\Throwable $e) {
                $this->logToChat('Edit AI failed, keeping original', ['error' => $e->getMessage()]);
                return $oldText;
            }
        };

        if ($target === 'general_message') {
            $old = $currentMessages['general'] ?? '';
            if ($old !== '') {
                $new = $applyEdit($old);
                $actions[] = [
                    'type' => 'update_general_message',
                    'content' => $new
                ];
            }
            return $actions;
        }

        if ($target === 'group_message') {
            $listId = (string)($analysis['list_id'] ?? '');
            $groupId = (string)($analysis['group_id'] ?? '');
            if ($listId && $groupId) {
                $key = $listId . '_' . $groupId;
                $old = $currentMessages['group_templates'][$key] ?? '';
                if ($old !== '') {
                    $new = $applyEdit($old);
                    $actions[] = [
                        'type' => 'update_group_template',
                        'listId' => $listId,
                        'groupId' => $groupId,
                        'content' => $new
                    ];
                }
                // Also edit each guest message in this group if present
                if (isset($guestData[$listId]['groups'][$groupId]['guests'])) {
                    $guests = $guestData[$listId]['groups'][$groupId]['guests'];
                    $guestArray = is_array($guests) ? $guests : (method_exists($guests, 'toArray') ? $guests->toArray() : []);
                    foreach ($guestArray as $guest) {
                        $guestId = is_object($guest) ? ($guest->id ?? null) : ($guest['id'] ?? null);
                        if ($guestId && isset($currentMessages['per_guest_messages'][$guestId])) {
                            $oldGuest = $currentMessages['per_guest_messages'][$guestId];
                            $newGuest = $applyEdit($oldGuest);
                            $actions[] = [
                                'type' => 'update_guest_message',
                                'guestId' => (string)$guestId,
                                'content' => $newGuest
                            ];
                        }
                    }
                }
            }
            return $actions;
        }

        if ($target === 'specific_guest' || $target === '') {
            // If guest_id not provided, try to find by name mentioned in user message
            $guestId = (string)($analysis['guest_id'] ?? '');
            if ($guestId === '') {
                $byName = $this->findGuestByNameInData(strtolower($userMessage), $guestData);
                if ($byName) {
                    $guestId = (string)$byName['id'];
                    $this->logToChat('🔵 [EDIT] Resolved guest by name in edit intent', ['guest_id' => $guestId, 'name' => $byName['name']]);
                }
            }
            if ($guestId && isset($currentMessages['per_guest_messages'][$guestId])) {
                $old = $currentMessages['per_guest_messages'][$guestId];
                $new = $applyEdit($old);
                $actions[] = [
                    'type' => 'update_guest_message',
                    'guestId' => $guestId,
                    'content' => $new
                ];
            }
            return $actions;
        }

        if ($target === 'all_guests') {
            foreach ($currentMessages['per_guest_messages'] ?? [] as $guestId => $old) {
                if ($old === '') continue;
                $new = $applyEdit($old);
                $actions[] = [
                    'type' => 'update_guest_message',
                    'guestId' => (string)$guestId,
                    'content' => $new
                ];
            }
            return $actions;
        }

        // If target unspecified but we have a focused group textarea open, we could infer, but default to no-op
        return $actions;
    }

    /**
     * Parse AI analysis response
     */
    private function parseAIAnalysis(string $text): array
    {
        // Clean and extract JSON
        $text = trim($text);
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);
        
        try {
            $analysis = json_decode($text, true);
            
            if ($analysis && is_array($analysis)) {
                $this->logToChat('🔵 [PARSE] Raw AI analysis', ['analysis' => $analysis]);
                
                // Enforce returning list_id for group_message if missing and resolvable
                if (($analysis['type'] ?? null) === 'group_message') {
                    $hasGroup = isset($analysis['group_id']) && $analysis['group_id'] !== null && $analysis['group_id'] !== '';
                    $hasList = isset($analysis['list_id']) && $analysis['list_id'] !== null && $analysis['list_id'] !== '';
                    if ($hasGroup && !$hasList) {
                        $resolvedListId = $this->resolveListIdFromGroupId((string)$analysis['group_id']);
                        if ($resolvedListId !== null) {
                            $analysis['list_id'] = (string)$resolvedListId;
                            $this->logToChat('🔵 [PARSE] Auto-filled missing list_id from group_id', [
                                'group_id' => (string)$analysis['group_id'],
                                'resolved_list_id' => (string)$resolvedListId
                            ]);
                        }
                    }
                }

                // Check for multi_actions first
                if (isset($analysis['multi_actions']) && is_array($analysis['multi_actions'])) {
                    // Ensure each group_message action includes list_id; resolve from group_id when missing
                    foreach ($analysis['multi_actions'] as $idx => $action) {
                        if (($action['type'] ?? null) === 'group_message') {
                            $hasGroup = isset($action['group_id']) && $action['group_id'] !== null && $action['group_id'] !== '';
                            $hasList = isset($action['list_id']) && $action['list_id'] !== null && $action['list_id'] !== '';
                            if ($hasGroup && !$hasList) {
                                $resolvedListId = $this->resolveListIdFromGroupId((string)$action['group_id']);
                                if ($resolvedListId !== null) {
                                    $analysis['multi_actions'][$idx]['list_id'] = (string)$resolvedListId;
                                    $this->logToChat('🔵 [PARSE] Auto-filled missing list_id in multi_actions from group_id', [
                                        'group_id' => (string)$action['group_id'],
                                        'resolved_list_id' => (string)$resolvedListId,
                                        'index' => $idx
                                    ]);
                                }
                            }
                        }
                    }
                    $this->logToChat('🔵 [PARSE] Found multi_actions', ['multi_actions' => $analysis['multi_actions']]);
                    return [
                        'multi_actions' => $analysis['multi_actions'],
                        'type' => 'multi_action',
                        'tone' => $analysis['tone'] ?? 'friendly',
                        'guest_id' => $analysis['guest_id'] ?? null,
                        'list_id' => $analysis['list_id'] ?? null,
                        'group_id' => $analysis['group_id'] ?? null,
                        'instructions' => $analysis['instructions'] ?? '',
                        'response' => $analysis['response'] ?? 'I understand your request.'
                    ];
                }
                
                // Standard single action
                return [
                    'type' => $analysis['type'] ?? 'response_only',
                    'tone' => $analysis['tone'] ?? 'friendly',
                    'guest_id' => $analysis['guest_id'] ?? null,
                    'list_id' => $analysis['list_id'] ?? null,
                    'group_id' => $analysis['group_id'] ?? null,
                    'instructions' => $analysis['instructions'] ?? '',
                    'response' => $analysis['response'] ?? 'I understand your request.'
                ];
            }
        } catch (\Exception $e) {
            $this->logToChat('Failed to parse AI analysis JSON', ['error' => $e->getMessage(), 'text' => $text]);
        }
        
        // Fallback
        return [
            'type' => 'response_only',
            'tone' => 'friendly',
            'instructions' => '',
            'response' => 'I understand your request.'
        ];
    }

    /**
     * Resolve list_id from a given group_id by scanning the current guestDataContext
     */
    private function resolveListIdFromGroupId(string $groupId): ?string
    {
        foreach ($this->guestDataContext as $listId => $listData) {
            if (isset($listData['groups']) && is_array($listData['groups'])) {
                foreach (array_keys($listData['groups']) as $dataGroupId) {
                    if ((string)$dataGroupId === (string)$groupId) {
                        return (string)$listId;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Simple pattern analysis fallback
     */
    private function analyzeSimplePattern(string $message, array $guestData): array
    {
        $message = strtolower($message);
        
        $result = [
            'type' => 'response_only',
            'tone' => 'friendly',
            'instructions' => '',
            'response' => 'I understand your request.'
        ];

        // Detect conversational messages first
        if (preg_match('/^(hi|hello|hey|good morning|good afternoon|good evening)$/i', $message)) {
            $result['type'] = 'conversational';
            $result['response'] = 'Hello! 👋 How can I help you with your event invitations today?';
            $this->logToChat('info', '🔄 [FALLBACK] Detected conversational message', $result);
            return $result;
        }
        
        if (preg_match('/\b(help|assist|support)\b/i', $message)) {
            $result['type'] = 'help';
            $result['response'] = 'I can help you create personalized invitation messages. I can generate messages for all guests, specific guests, groups, or lists. Just ask me what you need!';
            $this->logToChat('info', '🔄 [FALLBACK] Detected help request', $result);
            return $result;
        }

        // Detect edit intent quickly
        if (preg_match('/\b(edit|update|modify|change|shorten|make.*shorter|remove|add)\b/i', $message)) {
            $result['type'] = 'edit';
        }
        
        // Detect tone
        if (preg_match('/\b(formal|professional|official)\b/', $message)) {
            $result['tone'] = 'formal';
        } elseif (preg_match('/\b(friendly|casual|fun)\b/', $message)) {
            $result['tone'] = 'friendly';
        }
        
        // Detect type
        if (preg_match('/\b(all|every|everyone|write.*message.*everyone|generate.*message.*everyone)\b/', $message)) {
            $result['type'] = 'all_guests';
            $result['response'] = "I'll generate {$result['tone']} messages for all guests in their preferred languages.";
            $this->logToChat('info', '🔄 [FALLBACK] Detected all_guests request', $result);
        } elseif (preg_match('/\b(list|guest list)\b.*\d+/i', $message)) {
            // Try to find list by ID
            $listId = $this->findListById($message, $guestData);
            if ($listId) {
                $result['type'] = 'list_message';
                $result['list_id'] = $listId;
                $result['response'] = "I'll generate {$result['tone']} messages for all guests in list {$listId}.";
            }
        } elseif (preg_match('/\b(group|family|friends)\b/', $message)) {
            $result['type'] = 'group_message';
            $result['response'] = "I'll generate {$result['tone']} messages for the group.";
            
            // Try to find a group (use first available group as fallback)
            foreach ($guestData as $listId => $listData) {
                if (isset($listData['groups']) && !empty($listData['groups'])) {
                    $firstGroupId = array_key_first($listData['groups']);
                    $result['list_id'] = (string)$listId;
                    $result['group_id'] = (string)$firstGroupId;
                    break;
                }
            }
        } else {
            // Try to find specific guest by name
            $guestInfo = $this->findGuestByNameInData($message, $guestData);
            if ($guestInfo) {
                $result['type'] = 'specific_guest';
                $result['guest_id'] = (string)$guestInfo['id'];
                $result['response'] = "I'll generate a {$result['tone']} message for {$guestInfo['name']}.";
            }
        }
        
        // Detect instructions
        if (preg_match('/bring.*kids/', $message)) {
            $result['instructions'] = 'Please bring your kids';
        } elseif (preg_match('/dress.*code/', $message)) {
            $result['instructions'] = 'Please follow the dress code';
        } elseif (preg_match('/food.*provided/', $message)) {
            $result['instructions'] = 'Food will be provided';
        }
        
        $this->logToChat('info', '🔵 [SIMPLE ANALYSIS] Fallback analysis', $result);
        
        return $result;
    }

    /**
     * Find guest by name in guest data
     */
    private function findGuestByNameInData(string $message, array $guestData): ?array
    {
        $message = strtolower($message);
        
        foreach ($guestData as $listId => $listData) {
            // Check ungrouped guests
            if (isset($listData['ungrouped_guests'])) {
                $guests = $listData['ungrouped_guests'];
                if (is_array($guests) || method_exists($guests, 'toArray')) {
                    $guestArray = is_array($guests) ? $guests : $guests->toArray();
                    foreach ($guestArray as $guest) {
                        $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                        $guestId = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                        if ($guestName && strpos($message, strtolower($guestName)) !== false) {
                            return ['id' => $guestId, 'name' => $guestName];
                        }
                    }
                }
            }
            
            // Check grouped guests
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupData) {
                    if (isset($groupData['guests'])) {
                        $guests = $groupData['guests'];
                        if (is_array($guests) || method_exists($guests, 'toArray')) {
                            $guestArray = is_array($guests) ? $guests : $guests->toArray();
                            foreach ($guestArray as $guest) {
                                $guestName = is_object($guest) ? ($guest->name ?? '') : ($guest['name'] ?? '');
                                $guestId = is_object($guest) ? ($guest->id ?? '') : ($guest['id'] ?? '');
                                if ($guestName && strpos($message, strtolower($guestName)) !== false) {
                                    return ['id' => $guestId, 'name' => $guestName];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Log guest data structure for debugging
     */
    private function logGuestDataStructure(array $guestData): void
    {
        $debugInfo = [];
        foreach ($guestData as $listId => $listData) {
            $debugInfo[$listId] = [
                'has_groups' => isset($listData['groups']),
                'has_ungrouped' => isset($listData['ungrouped_guests']),
                'groups_count' => isset($listData['groups']) ? count($listData['groups']) : 0,
                'ungrouped_count' => isset($listData['ungrouped_guests']) ? count($listData['ungrouped_guests']) : 0
            ];
            
            // Log some guest names for verification
            if (isset($listData['ungrouped_guests'])) {
                $guests = $listData['ungrouped_guests'];
                if (is_array($guests) || method_exists($guests, 'toArray')) {
                    $guestArray = is_array($guests) ? $guests : $guests->toArray();
                    $debugInfo[$listId]['ungrouped_names'] = array_slice(
                        array_map(function($guest) { 
                            return is_object($guest) ? ($guest->name ?? 'unnamed') : ($guest['name'] ?? 'unnamed');
                        }, $guestArray), 
                        0, 3
                    );
                }
            }
            
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    if (isset($groupData['guests'])) {
                        $guests = $groupData['guests'];
                        if (is_array($guests) || method_exists($guests, 'toArray')) {
                            $guestArray = is_array($guests) ? $guests : $guests->toArray();
                            $debugInfo[$listId]['group_' . $groupId . '_names'] = array_slice(
                                array_map(function($guest) { 
                                    return is_object($guest) ? ($guest->name ?? 'unnamed') : ($guest['name'] ?? 'unnamed');
                                }, $guestArray), 
                                0, 3
                            );
                        }
                    }
                }
            }
        }
        
        $this->logToChat('🔍 [DEBUG] Guest data structure', $debugInfo);
    }

    /**
     * Check for missing critical event information
     */
    private function checkMissingEventInfo(array $eventData): array
    {
        $missingInfo = [];
        
        $eventName = $this->extractEventName($eventData);
        $eventDate = $this->extractEventDate($eventData);
        
        if (!$eventName || $eventName === 'Event' || $eventName === 'Untitled Event') {
            $missingInfo[] = 'event name';
        }
        
        if (!$eventDate || $eventDate === 'TBD' || $eventDate === 'Not specified') {
            $missingInfo[] = 'event date and time';
        }
        
        return $missingInfo;
    }

    /**
     * Create a message asking for missing information
     */
    private function createMissingInfoMessage(array $missingInfo): string
    {
        $message = "I need some additional information to generate the invitation messages:\n\n";
        
        foreach ($missingInfo as $index => $info) {
            $message .= ($index + 1) . ". Please provide the " . $info . "\n";
        }
        
        $message .= "\nOnce you provide this information, I'll be able to generate personalized invitation messages for your guests.";
        
        return $message;
    }



    /**
     * Execute action for a specific language group
     */
    private function executeActionForLanguageGroup(array $intent, array $eventData, array $languageData, string $userMessage, string $language): array
    {
        $actions = [];
        
        try {
            switch ($intent['type']) {
                case 'general_message':
                    $message = $this->generateMessageForLanguage($intent, $eventData, $languageData, $language);
                    if ($message) {
                        $actions[] = [
                            'type' => 'update_general_message',
                            'content' => $message,
                            'language' => $language
                        ];
                    }
                    break;
                    
                case 'group_message':
                    if (isset($intent['group_id'])) {
                        $message = $this->generateGroupMessageForLanguage($intent, $eventData, $languageData, $language);
                        if ($message) {
                            $actions[] = [
                                'type' => 'update_group_template',
                                'listId' => $intent['list_id'],
                                'groupId' => $intent['group_id'],
                                'content' => $message,
                                'language' => $language
                            ];
                        }
                    }
                    break;
                    
                case 'all_guests':
                    $messages = $this->generateAllGuestMessagesForLanguage($intent, $eventData, $languageData, $language);
                    foreach ($messages as $messageData) {
                        $actions[] = $messageData;
                    }
                    break;
                    
                case 'specific_guest':
                    if (isset($intent['guest_id'])) {
                        $message = $this->generateGuestMessageForLanguage($intent, $eventData, $languageData, $language);
                        if ($message) {
                            $actions[] = [
                                'type' => 'update_guest_message',
                                'guestId' => $intent['guest_id'],
                                'content' => $message,
                                'language' => $language
                            ];
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('❌ [LANGUAGE] Error generating messages for language', [
                'language' => $language,
                'error' => $e->getMessage(),
                'intent' => $intent
            ]);
        }
        
        return $actions;
    }

    /**
     * Generate general message for a specific language
     */
    private function generateMessageForLanguage(array $intent, array $eventData, array $languageData, string $language): string
    {
        $prompt = $this->buildLanguageSpecificPrompt($intent, $eventData, $languageData, $language);
        return $this->callAIForLanguage($prompt, $language);
    }

    /**
     * Generate group message for a specific language
     */
    private function generateGroupMessageForLanguage(array $intent, array $eventData, array $languageData, string $language): string
    {
        $prompt = $this->buildLanguageSpecificPrompt($intent, $eventData, $languageData, $language);
        return $this->callAIForLanguage($prompt, $language);
    }

    /**
     * Generate all guest messages for a specific language
     */
    private function generateAllGuestMessagesForLanguage(array $intent, array $eventData, array $languageData, string $language): array
    {
        $actions = [];
        
        // Generate general message for this language
        $generalMessage = $this->generateMessageForLanguage($intent, $eventData, $languageData, $language);
        if ($generalMessage) {
            $actions[] = [
                'type' => 'update_general_message',
                'content' => $generalMessage,
                'language' => $language
            ];
        }
        
        // Generate group messages for this language
        foreach ($languageData['lists'] as $listId => $listData) {
            foreach ($listData['groups'] as $groupId => $groupData) {
                $groupIntent = $intent;
                $groupIntent['type'] = 'group_message';
                $groupIntent['list_id'] = $listId;
                $groupIntent['group_id'] = $groupId;
                
                $groupMessage = $this->generateGroupMessageForLanguage($groupIntent, $eventData, $languageData, $language);
                if ($groupMessage) {
                    $actions[] = [
                        'type' => 'update_group_template',
                        'listId' => $listId,
                        'groupId' => $groupId,
                        'content' => $groupMessage,
                        'language' => $language
                    ];
                }
            }
        }
        
        return $actions;
    }

    /**
     * Generate guest message for a specific language
     */
    private function generateGuestMessageForLanguage(array $intent, array $eventData, array $languageData, string $language): string
    {
        $prompt = $this->buildLanguageSpecificPrompt($intent, $eventData, $languageData, $language);
        return $this->callAIForLanguage($prompt, $language);
    }

    /**
     * Build language-specific prompt
     */
    private function buildLanguageSpecificPrompt(array $intent, array $eventData, array $languageData, string $language): string
    {
        $prompt = "You are an expert event invitation writer. Generate a professional, engaging invitation message.\n\n";
        
        // Add language instruction
        $languageName = $this->getLanguageName($language);
        $prompt .= "IMPORTANT: Generate the message in {$languageName} language.\n\n";
        
        // Add event context
        $prompt .= "EVENT DETAILS:\n";
        $prompt .= "- Event Name: " . $this->extractEventName($eventData) . "\n";
        $prompt .= "- Date & Time: " . $this->extractEventDate($eventData) . "\n";
        $prompt .= "- Location: " . $this->extractEventLocation($eventData) . "\n";
        $prompt .= "- Description: " . $this->extractAdditionalInfo($eventData) . "\n\n";
        
        // Add guest context for this language
        $prompt .= "GUEST INFORMATION (Language: {$languageName}):\n";
        $prompt .= "- Total guests in this language: {$languageData['total_guests']}\n";
        
        // Add tone and style instructions
        $tone = $intent['tone'] ?? 'friendly';
        $prompt .= "- Tone: {$tone}\n";
        
        // Add special instructions
        if (isset($intent['special_instructions'])) {
            $prompt .= "- Special Instructions: {$intent['special_instructions']}\n";
        }
        
        $prompt .= "\nIMPORTANT GUIDELINES:\n";
        $prompt .= "• Only use information that was explicitly provided in the event details above\n";
        $prompt .= "• Never add placeholder text like '[removed - no details provided]' or similar\n";
        $prompt .= "• Never mention food, activities, games, or other details unless specifically provided\n";
        $prompt .= "• Write complete, engaging sentences\n";
        $prompt .= "• Make the message feel personal and inviting\n\n";
        
        // Add RSVP and QR code instructions based on event settings
        $rsvpEnabled = $eventData['rsvp_enabled'] ?? false;
        $qrEnabled = $eventData['qr_checkin_enabled'] ?? false;
        
        if ($rsvpEnabled || $qrEnabled) {
            $prompt .= "\nRSVP & QR CODE FEATURES:\n";
            $prompt .= "• At the end of the message, mention the link below is for RSVP and QR code\n";
            if ($rsvpEnabled) {
                $prompt .= "• Include that they can RSVP to confirm their attendance\n";
            }
            if ($qrEnabled) {
                $prompt .= "• Mention that a QR code will be available for easy check-in at the event\n";
            }
            $prompt .= "• Use natural language like 'The link below is for you to RSVP and get your QR code for check-in' or similar\n";
            $prompt .= "• Do NOT include the actual link - just mention that a link will be provided below\n";
        }
        
        $prompt .= "\nMESSAGE STRUCTURE:\n";
        $prompt .= "• Personal greeting using {name} placeholder\n";
        $prompt .= "• Event details (only what was provided above)\n";
        $prompt .= "• Warm, inviting closing\n";
        $prompt .= "• At the end: mention the link below is for RSVP and QR code (if enabled)\n\n";
        
        $prompt .= "OUTPUT FORMAT (IMPORTANT):\n";
        $prompt .= "• OUTPUT MUST BE MULTI-LINE with explicit newline characters (\\n)\n";
        $prompt .= "• Break the message into short lines: greeting, invite line, details line(s), closing\n";
        $prompt .= "• Do NOT return as a single paragraph. Ensure visible line breaks in plain text\n\n";
        
        $prompt .= "Generate a compelling {$tone} invitation message in {$languageName} with explicit line breaks (\\n) between lines:";
        
        return $prompt;
    }

    /**
     * Call AI for specific language
     */
    private function callAIForLanguage(string $prompt, string $language): string
    {
        try {
            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'application/json'
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$this->geminiApiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 500,
                    'temperature' => 0.7
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return $this->cleanResponse($text);
            }
        } catch (\Exception $e) {
            $this->logToChat('AI response failed for language', [
                'language' => $language,
                'error' => $e->getMessage()
            ]);
        }
        
        return '';
    }

    /**
     * Get unique languages from actions
     */
    private function getLanguagesFromActions(array $actions): array
    {
        $languages = [];
        foreach ($actions as $action) {
            if (isset($action['language']) && !in_array($action['language'], $languages)) {
                $languages[] = $action['language'];
            }
        }
        return $languages;
    }

    /**
     * Get language name from language code
     */
    private function getLanguageName(string $languageCode): string
    {
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
            'nl' => 'Dutch',
            'sv' => 'Swedish',
            'no' => 'Norwegian',
            'da' => 'Danish',
            'fi' => 'Finnish',
            'pl' => 'Polish',
            'tr' => 'Turkish',
            'he' => 'Hebrew'
        ];
        
        return $languages[$languageCode] ?? $languageCode;
    }
}