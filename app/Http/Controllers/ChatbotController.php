<?php

namespace App\Http\Controllers;

use App\Services\CloudflareAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected CloudflareAIService $aiService;
    protected string $storageFile;

    public function __construct(CloudflareAIService $aiService)
    {
        $this->aiService = $aiService;
        $this->storageFile = storage_path('app/chat_conversations.json');
    }

    /**
     * Handle incoming chatbot message.
     */
    public function sendMessage(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:500',
            'session_id' => 'nullable|string|max:64',
        ]);

        $ip = $request->ip() ?? '127.0.0.1';
        $sessionId = $validated['session_id'] ?? 'sess_' . md5($ip . microtime());
        $userMessage = trim(strip_tags($validated['message']));

        // 2. Rate Limiting: 25 requests per minute per session/IP
        $rateLimitKey = 'chatbot:' . $sessionId;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 25)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'error' => 'rate_limit',
                'reply' => "You have sent messages too quickly. Please wait {$seconds} seconds before sending another message, or call us at (907) 733-3030.",
                'session_id' => $sessionId
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // 3. Load Conversation History
        $conversations = $this->loadConversations();
        $history = $conversations[$sessionId]['messages'] ?? [];

        // Add user message to history
        $history[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->toISOString()
        ];

        // 4. Call Cloudflare Workers AI
        $result = $this->aiService->chat($history);

        $botReply = $result['reply'] ?? "I apologize, but I am momentarily unavailable. Please call our shop directly at (907) 733-3030.";

        // Append assistant reply to history
        $history[] = [
            'role' => 'assistant',
            'content' => $botReply,
            'timestamp' => now()->toISOString()
        ];

        // Save conversation
        $conversations[$sessionId] = [
            'session_id' => $sessionId,
            'ip' => $ip,
            'user_agent' => $request->userAgent(),
            'updated_at' => now()->toISOString(),
            'messages' => array_slice($history, -20) // Keep last 20 messages for persistence
        ];
        $this->saveConversations($conversations);

        return response()->json([
            'success' => true,
            'reply' => $botReply,
            'session_id' => $sessionId,
            'timestamp' => now()->format('h:i A')
        ]);
    }

    /**
     * Submit an appointment request from the chatbot.
     */
    public function bookAppointment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'vehicle_year' => 'required|string|max:10',
            'vehicle_make' => 'required|string|max:50',
            'vehicle_model' => 'required|string|max:50',
            'service' => 'required|string|max:100',
            'preferred_date' => 'required|string|max:30',
            'preferred_time' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $bookingRef = 'NFA-' . date('Y') . '-' . strtoupper(Str::random(4));

        $bookingRecord = [
            'id' => round(microtime(true) * 1000),
            'booking_number' => $bookingRef,
            'booking_reference' => $bookingRef,
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'] ?? '',
            'customer_phone' => $validated['phone'],
            'service_name' => $validated['service'],
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'vehicle_year' => $validated['vehicle_year'],
            'vehicle_make' => $validated['vehicle_make'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_info' => "{$validated['vehicle_year']} {$validated['vehicle_make']} {$validated['vehicle_model']}",
            'notes' => $validated['notes'] ?? '',
            'internal_notes' => 'Received via AI Chatbot Assistant',
            'source' => 'ai_chatbot',
            'status' => 'pending',
            'created_at' => now()->toISOString()
        ];

        // Save to storage/app/database.json for compatibility
        $dbFile = storage_path('app/database.json');
        if (File::exists($dbFile)) {
            $db = json_decode(File::get($dbFile), true) ?: [];
            if (!isset($db['bookings'])) $db['bookings'] = [];
            array_unshift($db['bookings'], $bookingRecord);
            File::put($dbFile, json_encode($db, JSON_PRETTY_PRINT));
        }

        return response()->json([
            'success' => true,
            'booking_reference' => $bookingRef,
            'message' => "Your appointment request ({$bookingRef}) has been received! Our service advisor at North Fork Auto will contact you to confirm final availability.",
            'booking' => $bookingRecord
        ]);
    }

    /**
     * Clear session history.
     */
    public function clearHistory(Request $request)
    {
        $sessionId = $request->input('session_id');
        if ($sessionId) {
            $conversations = $this->loadConversations();
            unset($conversations[$sessionId]);
            $this->saveConversations($conversations);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversation history cleared.'
        ]);
    }

    protected function loadConversations(): array
    {
        if (File::exists($this->storageFile)) {
            return json_decode(File::get($this->storageFile), true) ?: [];
        }
        return [];
    }

    protected function saveConversations(array $conversations): void
    {
        $dir = dirname($this->storageFile);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::put($this->storageFile, json_encode($conversations, JSON_PRETTY_PRINT));
    }
}
