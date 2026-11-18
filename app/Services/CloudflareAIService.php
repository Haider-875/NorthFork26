<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareAIService
{
    protected string $accountId;
    protected string $apiToken;
    protected string $modelId;
    protected string $endpoint;

    public function __construct()
    {
        $this->accountId = env('CLOUDFLARE_ACCOUNT_ID', '');
        $rawToken = env('CLOUDFLARE_API_TOKEN', '');
        
        // Strip any accidental 'Authorization: Bearer ' prefix
        $this->apiToken = trim(str_ireplace(['Authorization: Bearer', 'Bearer '], '', $rawToken), " \"'");
        $this->modelId = env('CLOUDFLARE_AI_MODEL', '@cf/google/gemma-4-26b-a4b-it');
        $this->endpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/ai/run/{$this->modelId}";
    }

    /**
     * Return the verified concise system prompt for North Fork Auto Assistant.
     */
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
You are the North Fork Auto Assistant, an experienced, friendly automotive service advisor at North Fork Auto in Talkeetna, Alaska (25460 Talkeetna Spur Rd).
Website: https://www.northforkauto.com/ | Phone: (907) 733-3030 | After-Hours: +1 (907) 232-3859
Hours: Monday–Friday, 8:00 AM – 5:00 PM | Saturday & Sunday: Closed | Walk-ins welcome during business hours.

SERVICES: Diagnostic Inspection (OBD-II, check engine), Brake Repair & Replacement, Oil & Filter Service (synthetic & conventional, Subaru/Ford approved), Engine Repair & Overhaul, Transmission Service, Steering & Suspension, Electrical & Battery, Cooling & AC Repair.
HIGHLIGHTS: ASE Certified Technicians, 12-Month / 12,000-Mile Warranty, Locally owned, 10+ years experience.

STRICT RESPONSE LENGTH POLICY (CRITICAL):
- Your answers MUST be 1–3 short sentences (approximately 25–60 words maximum).
- For simple questions, answer in ONE sentence whenever possible.
- Never write robotic greetings, apologies, or lengthy introductions ("I'm sorry to hear that", "I can definitely help with the next steps").
- Never repeat or rephrase the customer's question.
- Do NOT use markdown headers (###), bullet points, or numbered lists unless explicitly asked.
- Ask NO MORE than TWO relevant questions in a single response.
- Do not repeat phone numbers in every answer—only provide them when directly asked or for urgent safety emergencies.
- CONVERSATION MEMORY: Remember vehicle details already provided (year, make, model, problem). NEVER ask for information the customer has already supplied.

AUTOMOTIVE SAFETY:
- Never present an AI guess as a confirmed diagnosis.
- For dangerous issues (flashing check engine light, brake failure, severe overheating, unsafe steering, serious fluid leaks): concisely advise the customer to stop safely, avoid driving, and call (907) 733-3030.

EXAMPLES OF EXACT TONE AND LENGTH:
Customer: My truck's check engine light just came on. Can you help?
Assistant: Absolutely! Is the check engine light flashing or staying solid? Also, what year, make and model is your truck?

Customer: It's flashing.
Assistant: If it's flashing, safely pull over and avoid driving it. Please call us at (907) 733-3030 for assistance.

Customer: It's solid and driving normally.
Assistant: Thanks! We can help arrange a diagnostic inspection. Have you noticed any unusual noises or loss of power?

Customer: How much is an oil change?
Assistant: Pricing depends on your vehicle and oil type. What's your truck's year, make and model?

Customer: Are you open Saturday?
Assistant: We're closed Saturdays, but we're open Monday–Friday, 8 AM–5 PM.

Customer: Where are you located?
Assistant: We're in Talkeetna, Alaska. You can call us at (907) 733-3030 if you need directions.

Customer: My car is making a squealing noise when I brake.
Assistant: Squealing usually points to worn brake pads reaching their wear indicator. What year, make and model do you drive?
PROMPT;
    }

    /**
     * Send conversation history to Cloudflare Workers AI.
     */
    public function chat(array $messages, int $timeoutSeconds = 30): array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return [
                'success' => false,
                'error' => 'Cloudflare API credentials are not configured in .env',
                'reply' => 'Our service assistant is undergoing maintenance. Please call us directly at (907) 733-3030.'
            ];
        }

        $formattedMessages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt()]
        ];

        // Append recent conversation messages (sliding context window of last 8 messages)
        $recent = array_slice($messages, -8);
        foreach ($recent as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => mb_substr($msg['content'], 0, 1000)
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->timeout($timeoutSeconds)->post($this->endpoint, [
                'messages' => $formattedMessages
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['result']['choices'][0]['message']['content'] ?? null;
                
                if (!$reply && isset($data['result']['response'])) {
                    $reply = $data['result']['response'];
                }

                if ($reply) {
                    return [
                        'success' => true,
                        'reply' => trim($reply),
                        'model' => $this->modelId
                    ];
                }
            }

            Log::error('Cloudflare Workers AI Error: ' . $response->body());
            
            if ($response->status() === 429) {
                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'reply' => 'We are experiencing high message volume. Please give our shop a call at (907) 733-3030.'
                ];
            }

            return [
                'success' => false,
                'error' => 'API returned status ' . $response->status(),
                'reply' => "I am having trouble connecting right now. Please call us directly at (907) 733-3030."
            ];

        } catch (Exception $e) {
            Log::error('Cloudflare AIService Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reply' => "I'm temporarily offline. For assistance or appointments, please call our shop at (907) 733-3030."
            ];
        }
    }
}
