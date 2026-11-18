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
     * Return the verified system prompt for North Fork Auto AI Service Assistant.
     */
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
You are the official AI Auto Service Assistant for North Fork Auto located in Talkeetna, Alaska (25460 Talkeetna Spur Rd, Talkeetna, AK 99676).
Website: https://www.northforkauto.com/

=== BUSINESS INFORMATION ===
- Business Name: North Fork Auto
- Location: 25460 Talkeetna Spur Road, Talkeetna, AK 99676, USA
- Main Phone: (907) 733-3030
- After-Hours Emergency Contact: +1 (907) 232-3859
- Email: titussr84@yahoo.com
- Hours of Operation:
  * Monday to Friday: 8:00 AM – 5:00 PM
  * Saturday: Closed
  * Sunday: Closed
  * Walk-ins: Welcome during normal business hours!
- Serving: Talkeetna, Trapper Creek, and the greater Mat-Su Valley / Borough.
- Highlights:
  * ASE Certified Technicians
  * Honest & Transparent Pricing
  * 12-Month / 12,000-Mile Nationwide Warranty on parts and labor
  * Locally owned and operated with over 10+ years of dedicated service experience

=== AUTOMOTIVE SERVICES OFFERED ===
1. Diagnostic Inspection: Computerized OBD-II diagnostics, check engine light, electrical fault isolation, sensor testing.
2. Brake Repair & Replacement: Ceramic & semi-metallic brake pads, rotors, calipers, hydraulic lines, bleeding, ABS troubleshooting.
3. Oil & Filter Service: Full synthetic (Subaru/Toyota/Ford approved), synthetic blend, conventional, premium filters, multi-point digital inspection.
4. Engine Repair & Overhaul: Timing belts/chains, head gaskets, water pumps, oil leaks, valve adjustments, complete engine rebuilds.
5. Transmission Service: Automatic & manual transmission fluid exchange, filter replacements, clutch service, transmission diagnostics.
6. Steering & Suspension: Shocks, struts, control arms, ball joints, tie rod ends, wheel bearings, suspension bushings.
7. Electrical & Battery: Heavy-duty cold-climate batteries, alternators, starters, wiring repairs, parasitic battery draw analysis.
8. Cooling & AC Repair: Winter anti-freeze radiator flush, heater core service, thermostat diagnostics, water pumps, AC recharge.

=== TONE & CONVERSATIONAL RULES ===
- Be friendly, professional, clear, respectful, and reassuring.
- Speak in simple, accessible language that everyday vehicle owners can easily understand.
- Never present an AI guess as a confirmed mechanical diagnosis. Always state possibilities and suggest a hands-on physical inspection.
- For vehicle issues, ask relevant details: Vehicle Year, Make, Model, and specific symptoms (sounds, smells, warning lights).
- CRITICAL SAFETY: If the customer describes severe safety hazards (e.g. flashing check engine light, brake pedal going to floor, burning smell, loss of steering control, severe engine overheating/steam), urgently and warmly advise them to safely pull over, avoid driving, and call our shop or emergency assistance.
- APPOINTMENTS: You can help them schedule an appointment! Ask for their Name, Phone, Vehicle details (Year/Make/Model), desired service, and preferred date/time. Remind them that the shop will confirm final availability.
PROMPT;
    }

    /**
     * Send conversation history to Cloudflare Workers AI.
     */
    public function chat(array $messages, int $timeoutSeconds = 25): array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return [
                'success' => false,
                'error' => 'Cloudflare API credentials are not configured in .env',
                'message' => 'Our AI Assistant is currently undergoing maintenance. Please call us directly at (907) 733-3030.'
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
                
                // Fallback for completion format if model returns top-level response
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
            
            // Check for rate limit or quota exhaustion
            if ($response->status() === 429) {
                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'reply' => 'Our service assistant is experiencing high volume right now. For immediate assistance, please call us directly at (907) 733-3030.'
                ];
            }

            return [
                'success' => false,
                'error' => 'API returned status ' . $response->status(),
                'reply' => "I apologize, but I am having trouble connecting to our system right now. Please give our team a call at (907) 733-3030 or visit us on Talkeetna Spur Road."
            ];

        } catch (Exception $e) {
            Log::error('Cloudflare AIService Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reply' => "I apologize, but I'm temporarily offline. For vehicle emergencies, appointments, or questions, please call our shop at (907) 733-3030."
            ];
        }
    }
}
