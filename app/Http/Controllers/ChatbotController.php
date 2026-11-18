<?php

namespace App\Http\Controllers;

use App\Services\AppointmentLookupService;
use App\Services\AppointmentVerificationService;
use App\Services\CloudflareAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    protected CloudflareAIService $aiService;
    protected AppointmentLookupService $lookupService;
    protected AppointmentVerificationService $verificationService;
    protected string $storageFile;

    public function __construct(
        CloudflareAIService $aiService,
        AppointmentLookupService $lookupService,
        AppointmentVerificationService $verificationService
    ) {
        $this->aiService = $aiService;
        $this->lookupService = $lookupService;
        $this->verificationService = $verificationService;
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

        // 3. Load Conversation Session
        $conversations = $this->loadConversations();
        $sessionData = $conversations[$sessionId] ?? [
            'session_id' => $sessionId,
            'messages' => [],
            'pending_appointment_id' => null,
            'verified_appointment_id' => null,
        ];
        $history = $sessionData['messages'] ?? [];

        // Add user message to history
        $history[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->toISOString()
        ];

        // 4. INTELLIGENT APPOINTMENT LOOKUP & PRIVACY ENGINE
        $botReply = null;
        $appointmentData = null;

        // 4A. Check if user provided an appointment confirmation ID (e.g. NFA-2026-8659)
        $extractedId = $this->lookupService->extractConfirmationId($userMessage);
        if ($extractedId) {
            $appointment = $this->lookupService->findAppointment($extractedId);

            if (!$appointment) {
                $botReply = "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again.";
            } else {
                // Check if already verified in this session
                $alreadyVerified = ($sessionData['verified_appointment_id'] ?? '') === $extractedId;

                // Also check if user provided verification detail in the same message
                if (!$alreadyVerified) {
                    $detail = $this->verificationService->extractVerificationDetail($userMessage);
                    if ($detail) {
                        $verifyResult = $this->verificationService->verifyCustomer($appointment, $detail['value'], $sessionId, $ip);
                        if ($verifyResult['verified']) {
                            $alreadyVerified = true;
                            $sessionData['verified_appointment_id'] = $extractedId;
                            $sessionData['pending_appointment_id'] = null;
                        }
                    }
                }

                if ($alreadyVerified) {
                    $summary = $this->lookupService->formatSummary($appointment, true);
                    $appointmentData = $summary;
                    $botReply = "Your appointment is confirmed for {$summary['date']} at {$summary['time']}. You're booked for {$summary['service_name']} (Status: {$summary['status']}). Your confirmation number is {$summary['confirmation_id']}.";
                } else {
                    $sessionData['pending_appointment_id'] = $extractedId;
                    $botReply = "I found your appointment ({$extractedId}). For privacy, please verify the email address or phone number associated with your booking before I share its details.";
                }
            }
        }

        // 4B. If session has a pending appointment awaiting verification, check for phone/email
        elseif (!empty($sessionData['pending_appointment_id'])) {
            $pendingId = $sessionData['pending_appointment_id'];
            $detail = $this->verificationService->extractVerificationDetail($userMessage);

            if ($detail) {
                $appointment = $this->lookupService->findAppointment($pendingId);
                if ($appointment) {
                    $verifyResult = $this->verificationService->verifyCustomer($appointment, $detail['value'], $sessionId, $ip);

                    if ($verifyResult['locked_out']) {
                        $botReply = $verifyResult['message'];
                    } elseif ($verifyResult['verified']) {
                        $sessionData['verified_appointment_id'] = $pendingId;
                        $sessionData['pending_appointment_id'] = null;
                        $summary = $this->lookupService->formatSummary($appointment, true);
                        $appointmentData = $summary;
                        $botReply = "Your appointment is confirmed for {$summary['date']} at {$summary['time']}. You're booked for {$summary['service_name']} (Status: {$summary['status']}). Your confirmation number is {$summary['confirmation_id']}.";
                    } else {
                        $botReply = "The verification details provided do not match our records for that appointment. For security, please check your information and try again, or call us directly at (907) 733-3030.";
                    }
                } else {
                    $sessionData['pending_appointment_id'] = null;
                    $botReply = "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again.";
                }
            }
        }

        // 4C. Check if customer is asking to check appointment without an ID
        if ($botReply === null) {
            $isAskingAppointment = preg_match('/\b(check|find|look up|view|status of|when is|what time is|my)\b.*\b(appointment|booking|scheduled|schedule|meeting)\b/i', $userMessage)
                || preg_match('/\b(when is my appointment|check my appointment|my appointment|check confirmation)\b/i', $userMessage);

            if ($isAskingAppointment) {
                // If this session has an active verified appointment, answer directly
                if (!empty($sessionData['verified_appointment_id'])) {
                    $appointment = $this->lookupService->findAppointment($sessionData['verified_appointment_id']);
                    if ($appointment) {
                        $summary = $this->lookupService->formatSummary($appointment, true);
                        $appointmentData = $summary;
                        $botReply = "Your appointment is confirmed for {$summary['date']} at {$summary['time']} for {$summary['service_name']} (Confirmation: {$summary['confirmation_id']}).";
                    }
                }

                if ($botReply === null) {
                    $botReply = "I can check your appointment! What is your confirmation number (for example, NFA-2026-xxxx)?";
                }
            }
        }

        // 4D. Call Cloudflare Workers AI for general automotive advisor queries
        if ($botReply === null) {
            // If an appointment is verified in session, prepend verified context into recent history
            $aiHistory = $history;
            if (!empty($sessionData['verified_appointment_id'])) {
                $appt = $this->lookupService->findAppointment($sessionData['verified_appointment_id']);
                if ($appt) {
                    $summary = $this->lookupService->formatSummary($appt, true);
                    $verifiedContext = "[VERIFIED GROUND TRUTH: Appointment {$summary['confirmation_id']} confirmed for {$summary['date']} at {$summary['time']}. Service: {$summary['service_name']}. Vehicle: {$summary['vehicle']}. Status: {$summary['status']}.]";
                    $aiHistory[] = ['role' => 'system', 'content' => $verifiedContext];
                }
            }

            $result = $this->aiService->chat($aiHistory);
            $botReply = $result['reply'] ?? "I apologize, but I am momentarily unavailable. Please call our shop directly at (907) 733-3030.";
        }

        // Append assistant reply to history
        $history[] = [
            'role' => 'assistant',
            'content' => $botReply,
            'timestamp' => now()->toISOString()
        ];

        // Save conversation state
        $sessionData['ip'] = $ip;
        $sessionData['user_agent'] = $request->userAgent();
        $sessionData['updated_at'] = now()->toISOString();
        $sessionData['messages'] = array_slice($history, -20); // Keep last 20 messages
        $conversations[$sessionId] = $sessionData;
        $this->saveConversations($conversations);

        $responsePayload = [
            'success' => true,
            'reply' => $botReply,
            'session_id' => $sessionId,
            'timestamp' => now()->format('h:i A')
        ];

        if ($appointmentData) {
            $responsePayload['appointment'] = $appointmentData;
        }

        return response()->json($responsePayload);
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

        // Save to storage/app/database.json
        $dbFile = storage_path('app/database.json');
        if (File::exists($dbFile)) {
            $content = File::get($dbFile);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            $db = json_decode($content, true) ?: [];
            if (!isset($db['bookings'])) $db['bookings'] = [];
            array_unshift($db['bookings'], $bookingRecord);
            File::put($dbFile, json_encode($db, JSON_PRETTY_PRINT));
        }

        // Auto-verify this appointment for current user session
        $sessionId = $validated['session_id'] ?? null;
        if ($sessionId) {
            $conversations = $this->loadConversations();
            if (isset($conversations[$sessionId])) {
                $conversations[$sessionId]['verified_appointment_id'] = $bookingRef;
                $this->saveConversations($conversations);
            }
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
            $content = File::get($this->storageFile);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            return json_decode($content, true) ?: [];
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
