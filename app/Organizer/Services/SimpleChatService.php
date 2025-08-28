<?php

namespace App\Organizer\Services;

use App\Services\EventMessageGenerationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimpleChatService
{
    private $eventMessageGenerationService;
    private $geminiApiKey;

    public function __construct(EventMessageGenerationService $eventMessageGenerationService)
    {
        $this->eventMessageGenerationService = $eventMessageGenerationService;
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Process user message and return response with actions
     */
    public function processMessage(string $message, array $history, array $eventData, array $guestData): array
    {
        try {
            Log::info('🔵 [CHAT] Processing message', ['message' => $message]);

            // Determine user intent
            $intent = $this->analyzeIntent($message, $guestData);
            Log::info('🔵 [CHAT] Intent detected', $intent);
            
            // Generate AI response
            $aiResponse = $this->generateResponse($message, $history, $eventData);
            
            // Execute the action based on intent
            $actions = $this->executeAction($intent, $eventData, $guestData, $message);
            Log::info('🔵 [CHAT] Actions generated', ['actions_count' => count($actions)]);

            Log::info('✅ [CHAT] Success', [
                'intent' => $intent['type'],
                'actions_count' => count($actions)
            ]);

            return [
                'success' => true,
                'response' => $aiResponse,
                'actions' => $actions
            ];

        } catch (\Exception $e) {
            Log::error('❌ [CHAT] Error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'response' => "I'm having trouble processing your request. Please try again.",
                'actions' => []
            ];
        }
    }

    /**
     * Analyze user intent from message
     */
    private function analyzeIntent(string $message, array $guestData): array
    {
        $message = strtolower($message);
        
        // All guests - enhanced patterns
        if (preg_match('/(all|every|everyone).*(guest|people)/i', $message) ||
            preg_match('/(write|generate|create|make).*(message|invitation).*(all|everyone)/i', $message) ||
            preg_match('/message.*(for|to).*(all|everyone)/i', $message)) {
            return [
                'type' => 'all_guests',
                'tone' => $this->extractTone($message),
                'instructions' => $this->extractInstructions($message)
            ];
        }
        
        // Specific guest by name
        $guestId = $this->findGuestByName($message, $guestData);
        if ($guestId && preg_match('/(write|generate|create|make|message)/i', $message)) {
            return [
                'type' => 'specific_guest',
                'guest_id' => $guestId,
                'tone' => $this->extractTone($message),
                'instructions' => $this->extractInstructions($message)
            ];
        }
        
        // Group message
        $groupInfo = $this->findGroupByName($message, $guestData);
        if ($groupInfo && preg_match('/(write|generate|create|make|message)/i', $message)) {
            return [
                'type' => 'group_message',
                'group_id' => $groupInfo['group_id'],
                'list_id' => $groupInfo['list_id'],
                'tone' => $this->extractTone($message),
                'instructions' => $this->extractInstructions($message)
            ];
        }
        
        // General message
        if (preg_match('/(general|template|default)/i', $message) ||
            preg_match('/(write|generate|create|make).*(general|template|default)/i', $message)) {
            return [
                'type' => 'general_message',
                'tone' => $this->extractTone($message),
                'instructions' => $this->extractInstructions($message)
            ];
        }
        
        // Default: just respond
        return [
            'type' => 'response_only',
            'tone' => 'friendly',
            'instructions' => ''
        ];
    }

    /**
     * Extract tone from message
     */
    private function extractTone(string $message): string
    {
        if (preg_match('/(friendly|casual|informal|fun)/i', $message)) {
            return 'friendly';
        }
        if (preg_match('/(formal|professional|official)/i', $message)) {
            return 'formal';
        }
        return 'friendly'; // Default to friendly
    }

    /**
     * Extract user instructions from message
     */
    private function extractInstructions(string $message): string
    {
        $instructions = [];
        
        // Common patterns
        $patterns = [
            '/bring.*kids/' => 'Please bring your kids',
            '/bring.*children/' => 'Please bring your children', 
            '/bring.*family/' => 'Please bring your family',
            '/formal.*attire/' => 'Formal attire required',
            '/plus.*one/' => 'Plus one welcome'
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
        foreach ($guestData as $listId => $listData) {
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupId => $groupData) {
                    $groupName = strtolower($groupData['group_name']);
                    if (strpos($message, $groupName) !== false) {
                        return [
                            'group_id' => (string)$groupId,
                            'list_id' => (string)$listId
                        ];
                    }
                }
            }
        }
        
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
            Log::warning('AI response failed', ['error' => $e->getMessage()]);
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
     * Execute action based on intent
     */
    private function executeAction(array $intent, array $eventData, array $guestData, string $userMessage): array
    {
        switch ($intent['type']) {
            case 'all_guests':
                return $this->generateAllGuestMessages($intent, $eventData, $guestData);
                
            case 'specific_guest':
                return $this->generateGuestMessage($intent, $eventData, $guestData);
                
            case 'group_message':
                return $this->generateGroupMessage($intent, $eventData, $guestData);
                
            case 'general_message':
                return $this->generateGeneralMessage($intent, $eventData, $guestData);
                
            default:
                return [];
        }
    }

    /**
     * Generate messages for all guests
     */
    private function generateAllGuestMessages(array $intent, array $eventData, array $guestData): array
    {
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'guests' => []
        ];
        
        // Add all guests
        foreach ($guestData as $listData) {
            if (isset($listData['groups'])) {
                foreach ($listData['groups'] as $groupData) {
                    foreach ($groupData['guests'] as $guest) {
                        $userInstructions['guests'][$guest->id] = $intent['instructions'] ?: $intent['tone'];
                    }
                }
            }
            if (isset($listData['ungrouped_guests'])) {
                foreach ($listData['ungrouped_guests'] as $guest) {
                    $userInstructions['guests'][$guest->id] = $intent['instructions'] ?: $intent['tone'];
                }
            }
        }

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'per_guest',
            $guestData,
            $userInstructions
        );

        if ($result['success'] && isset($result['per_guest_messages'])) {
            $actions = [];
            foreach ($result['per_guest_messages'] as $guestId => $message) {
                $actions[] = [
                    'type' => 'update_guest_message',
                    'guestId' => $guestId,
                    'content' => $message
                ];
            }
            return $actions;
        }

        return [];
    }

    /**
     * Generate message for specific guest
     */
    private function generateGuestMessage(array $intent, array $eventData, array $guestData): array
    {
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'guests' => [
                $intent['guest_id'] => $intent['instructions'] ?: $intent['tone']
            ]
        ];

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'per_guest',
            $guestData,
            $userInstructions
        );

        if ($result['success'] && isset($result['per_guest_messages'][$intent['guest_id']])) {
            return [[
                'type' => 'update_guest_message',
                'guestId' => $intent['guest_id'],
                'content' => $result['per_guest_messages'][$intent['guest_id']]
            ]];
        }

        return [];
    }

    /**
     * Generate group message
     */
    private function generateGroupMessage(array $intent, array $eventData, array $guestData): array
    {
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'groups' => [
                $intent['list_id'] . '_' . $intent['group_id'] => $intent['instructions'] ?: $intent['tone']
            ]
        ];

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'group',
            $guestData,
            $userInstructions
        );

        if ($result['success']) {
            $actions = [];
            
            if (isset($result['group_messages'])) {
                foreach ($result['group_messages'] as $key => $message) {
                    if ($key === $intent['list_id'] . '_' . $intent['group_id']) {
                        $actions[] = [
                            'type' => 'update_group_template',
                            'listId' => $intent['list_id'],
                            'groupId' => $intent['group_id'],
                            'content' => $message
                        ];
                    }
                }
            }
            
            if (isset($result['per_guest_messages'])) {
                foreach ($result['per_guest_messages'] as $guestId => $message) {
                    $actions[] = [
                        'type' => 'update_guest_message',
                        'guestId' => $guestId,
                        'content' => $message
                    ];
                }
            }
            
            return $actions;
        }

        return [];
    }

    /**
     * Generate general message
     */
    private function generateGeneralMessage(array $intent, array $eventData, array $guestData): array
    {
        $userInstructions = [
            'tone' => $intent['tone'],
            'style' => $intent['tone'] === 'friendly' ? 'casual' : 'professional',
            'additional_notes' => $intent['instructions']
        ];

        $result = $this->eventMessageGenerationService->generateMessages(
            $this->createTemporaryEvent($eventData),
            'general',
            $guestData,
            $userInstructions
        );

        if ($result['success'] && isset($result['general_message'])) {
            return [[
                'type' => 'update_general_message',
                'content' => $result['general_message']
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
     * Extract event name with better fallbacks
     */
    private function extractEventName(array $eventData): string
    {
        // Try multiple possible keys for event name
        $possibleKeys = ['name', 'event_name', 'title', 'event_title'];
        
        foreach ($possibleKeys as $key) {
            if (isset($eventData[$key]) && !empty($eventData[$key]) && $eventData[$key] !== 'Event') {
                return $eventData[$key];
            }
        }
        
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
                    $date = \Carbon\Carbon::parse($eventData[$key]);
                    return $date->format('F j, Y \a\t g:i A');
                } catch (\Exception $e) {
                    return $eventData[$key];
                }
            }
        }
        
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
}