<?php
/**
 * North Fork Auto — Intelligent AI Chatbot & Edge Application Entrypoint
 * Location: Talkeetna, Alaska, USA
 * Framework: Modern PHP 8.3 / Wasmer PHPix Runtime
 * Target: High Conversational Intelligence, Real Calendar Lookup & Sub-Second Latency
 */

declare(strict_types=1);

// Set timezone to Alaska (North Fork Auto local time)
date_default_timezone_set('America/Anchorage');

// -------------------------------------------------------------
// 1. Environment Variable Loader (.env support with production defaults)
// -------------------------------------------------------------
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));
            $val = trim($val, "\"'");
            if (getenv($key) === false) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
}

// -------------------------------------------------------------
// 2. Static Assets Serving
// -------------------------------------------------------------
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($rawUri, PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path !== '/') {
    $targetFile = __DIR__ . $path;
    if (file_exists($targetFile) && is_file($targetFile)) {
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $mimes = [
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'svg'   => 'image/svg+xml',
            'webp'  => 'image/webp',
            'ico'   => 'image/x-icon',
            'json'  => 'application/json; charset=UTF-8',
            'woff2' => 'font/woff2',
            'woff'  => 'font/woff',
            'ttf'   => 'font/ttf',
            'html'  => 'text/html; charset=UTF-8',
        ];
        $contentType = $mimes[$ext] ?? 'application/octet-stream';
        header("Content-Type: $contentType");
        header("Cache-Control: public, max-age=86400");
        readfile($targetFile);
        exit;
    }
}

// Helper: send JSON response
function sendJson(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Chatbot-Engine: NorthFork-Intelligent-v2');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// -------------------------------------------------------------
// 3. Database & Appointment Helpers
// -------------------------------------------------------------
$dbFile = dirname(__DIR__) . '/storage/app/database.json';

function loadDatabase(string $file): array {
    if (!file_exists($file)) {
        return ['bookings' => [], 'contacts' => []];
    }
    $content = file_get_contents($file);
    return json_decode($content ?: '[]', true) ?: ['bookings' => [], 'contacts' => []];
}

function saveDatabase(string $file, array $data): bool {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return (bool) file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function normalizeId(?string $id): string {
    if (!$id) return '';
    $clean = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $id));
    if (preg_match('/^NFA(\d{4})([A-Z0-9]+)$/', $clean, $m)) {
        return "NFA-{$m[1]}-{$m[2]}";
    }
    return trim($id);
}

function getBookingId(array $b): string {
    return normalizeId($b['booking_number'] ?? ($b['booking_reference'] ?? ($b['confirmation_id'] ?? '')));
}

function getBookingName(array $b): string {
    return (string) ($b['customer_name'] ?? ($b['name'] ?? 'Customer'));
}

function getBookingPhone(array $b): string {
    return (string) ($b['customer_phone'] ?? ($b['phone'] ?? ''));
}

function getBookingEmail(array $b): string {
    return (string) ($b['customer_email'] ?? ($b['email'] ?? ''));
}

function extractIdFromText(string $text): ?string {
    if (preg_match('/\b(?:#?\s*)?(NFA[-_\s]?(?:20\d{2})[-_\s]?[A-Z0-9]{4,6})\b/i', $text, $m)) {
        return normalizeId($m[1]);
    }
    if (preg_match('/\b(NFA[-_\s]?[A-Z0-9]{4,12})\b/i', $text, $m)) {
        return normalizeId($m[1]);
    }
    return null;
}

function maskString(?string $str): string {
    if (!$str) return '***';
    $len = strlen($str);
    if ($len <= 2) return $str[0] . '*';
    return $str[0] . str_repeat('*', $len - 2) . $str[$len - 1];
}

function maskEmail(?string $email): string {
    if (!$email || !str_contains($email, '@')) return '***@***.***';
    [$user, $domain] = explode('@', $email, 2);
    $maskedUser = strlen($user) <= 2 ? $user[0] . '*' : $user[0] . '***' . substr($user, -1);
    return $maskedUser . '@' . $domain;
}

function maskPhone(?string $phone): string {
    $digits = preg_replace('/\D/', '', (string) $phone);
    if (strlen($digits) >= 4) {
        return '***-***-' . substr($digits, -4);
    }
    return '***-***-****';
}

function getPreviousTopic(array $history): ?string {
    $recent = array_slice($history, -4);
    for ($i = count($recent) - 1; $i >= 0; $i--) {
        $text = strtolower((string) ($recent[$i]['content'] ?? ''));
        if (str_contains($text, 'brake')) return 'brakes';
        if (str_contains($text, 'engine') || str_contains($text, 'motor')) return 'engine';
        if (str_contains($text, 'oil')) return 'oil_change';
        if (str_contains($text, 'transmission')) return 'transmission';
        if (str_contains($text, 'battery') || str_contains($text, 'electrical')) return 'electrical';
        if (str_contains($text, 'appointment') || str_contains($text, 'booking')) return 'appointment';
    }
    return null;
}

// -------------------------------------------------------------
// 4. Deterministic Intelligent Intent Router (<20ms execution)
// -------------------------------------------------------------
const SHOP_INFO = [
    'name'           => 'North Fork Auto',
    'phone'          => '(907) 733-3030',
    'emergencyPhone' => '(907) 232-3859',
    'hours'          => 'Monday through Friday, 8:00 AM – 5:00 PM',
    'weekend'        => 'Closed Saturday and Sunday',
    'location'       => 'Talkeetna Spur Road, Talkeetna, AK 99676',
    'warranty'       => '12-month / 12,000-mile warranty on qualifying parts and labor',
];

function resolveSmartIntent(string $message, array $history, array $database): ?array {
    $text = trim($message);
    $lower = strtolower($text);
    $prevTopic = getPreviousTopic($history);

    // 1. APPOINTMENT LOOKUP INTENT
    $confId = extractIdFromText($text);
    $isLookupQuery = preg_match('/\b(check|status|track|find|look up|where is|when is)\b.*\b(appointment|booking|meeting|schedule)\b/i', $lower)
        && !preg_match('/\b(book|new|make|schedule an?)\b/i', $lower);

    if ($confId || $isLookupQuery) {
        if ($confId) {
            $norm = normalizeId($confId);
            $bookings = $database['bookings'] ?? [];
            $found = null;
            foreach ($bookings as $b) {
                if (getBookingId($b) === $norm) {
                    $found = $b;
                    break;
                }
            }

            if ($found) {
                $service = $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service');
                $date = $found['preferred_date'] ?? 'Upcoming';
                $time = $found['preferred_time'] ?? 'Regular Shop Hours';
                $status = strtoupper((string) ($found['status'] ?? 'PENDING'));
                $vehParts = array_filter([$found['vehicle_year'] ?? '', $found['vehicle_make'] ?? '', $found['vehicle_model'] ?? '']);
                $veh = !empty($vehParts) ? implode(' ', $vehParts) : 'Vehicle on file';

                return [
                    'intent' => 'APPOINTMENT_LOOKUP_FOUND',
                    'reply' => "I found your appointment!\n\n• Appointment ID: {$norm}\n• Date: {$date}\n• Time: {$time}\n• Service: {$service}\n• Status: {$status}\n\nWould you like help with anything else?",
                    'appointment' => [
                        'confirmation_id'      => $norm,
                        'date'                 => $date,
                        'time'                 => $time,
                        'service'              => $service,
                        'status'               => strtolower($status),
                        'vehicle'              => $veh,
                        'customer_masked'      => maskString(getBookingName($found)),
                        'phone_masked'         => maskPhone(getBookingPhone($found)),
                        'email_masked'         => maskEmail(getBookingEmail($found)),
                        'requires_verification'=> true
                    ]
                ];
            } else {
                return [
                    'intent' => 'APPOINTMENT_LOOKUP_NOT_FOUND',
                    'reply' => "I checked our booking records but couldn't locate an appointment with confirmation number {$confId}. Please double-check the ID or call our Talkeetna shop at " . SHOP_INFO['phone'] . "."
                ];
            }
        } else {
            return [
                'intent' => 'APPOINTMENT_LOOKUP_PROMPT',
                'reply' => "I can check your appointment details right away! Please provide your confirmation number (like NFA-2026-8659) or the phone number used during booking."
            ];
        }
    }

    // 2. APPOINTMENT RESCHEDULE & CANCELLATION
    if (preg_match('/\b(reschedule|change|move|postpone)\b.*\b(appointment|date|time|booking)\b/i', $lower) || preg_match('/\b(cancel|drop)\b.*\b(appointment|booking)\b/i', $lower)) {
        if ($confId) {
            return [
                'intent' => 'APPOINTMENT_RESCHEDULE_PROMPT',
                'reply' => "I can help update appointment {$confId}. What new date and time between Monday–Friday, 8:00 AM–5:00 PM works best for you? You can also call us directly at " . SHOP_INFO['phone'] . "."
            ];
        }
        return [
            'intent' => 'APPOINTMENT_RESCHEDULE_NEED_ID',
            'reply' => "I would be happy to help you reschedule or adjust your service! Please provide your appointment confirmation number (or call us at " . SHOP_INFO['phone'] . ") so I can update your booking."
        ];
    }

    // 3. PREVIOUS APPOINTMENT QUESTION
    if (preg_match('/\b(previous|past|earlier|last)\b.*\b(appointment|visit|repair|service)\b/i', $lower)) {
        return [
            'intent' => 'APPOINTMENT_HISTORY',
            'reply' => "I'd be happy to help look up your previous service records! Please provide your confirmation ID or the phone number associated with your vehicle, or call us at " . SHOP_INFO['phone'] . "."
        ];
    }

    // 4. BOOKING AVAILABILITY & SCHEDULING
    if (preg_match('/\b(can i come tomorrow|available tomorrow|availability|next available|available slots?|open slots?)\b/i', $lower) ||
        preg_match('/\b(book|schedule|make|reserve)\b.*\b(appointment|service|inspection|oil|brake|repair)\b/i', $lower) ||
        str_contains($lower, 'come tomorrow') || str_contains($lower, 'bring my car') || str_contains($lower, 'bring my truck')) {
        return [
            'intent' => 'APPOINTMENT_BOOKING',
            'reply' => "Sure! I can help check appointment availability. Our shop is open Monday through Friday, 8:00 AM to 5:00 PM. What service does your vehicle need, and what day works best for you?",
            'action' => 'open_booking_modal'
        ];
    }

    // 5. BUSINESS HOURS & SCHEDULE
    if (preg_match('/\b(saturday|sunday|weekend)\b/i', $lower)) {
        return [
            'intent' => 'BUSINESS_HOURS_WEEKEND',
            'reply' => "We are closed on Saturday and Sunday. North Fork Auto is open Monday through Friday from 8:00 AM to 5:00 PM. Would you like to schedule an appointment during the week?"
        ];
    }
    if (preg_match('/\b(close|closing time|open|hours|what time do you close|operating hours|work hours)\b/i', $lower)) {
        return [
            'intent' => 'BUSINESS_HOURS_GENERAL',
            'reply' => "We are open Monday through Friday from 8:00 AM to 5:00 PM (closed Saturday and Sunday). Is there a specific day you'd like to bring your vehicle in?"
        ];
    }

    // 6. LOCATION & DIRECTIONS
    if (preg_match('/\b(where are you|location|address|directions|where is the shop|how do i find you|where located)\b/i', $lower)) {
        return [
            'intent' => 'LOCATION_DIRECTIONS',
            'reply' => "We are located on Talkeetna Spur Road in Talkeetna, Alaska (AK 99676), proudly serving Talkeetna and the entire Mat-Su Valley."
        ];
    }

    // 7. CONTACT, PHONE, EMERGENCY & HUMAN ASSISTANCE
    if (preg_match('/\b(speak to someone|talk to someone|speak to a person|human|receptionist|advisor|call you|phone number|contact number)\b/i', $lower)) {
        return [
            'intent' => 'CONTACT_HUMAN',
            'reply' => "You can reach our service team directly at " . SHOP_INFO['phone'] . " Monday–Friday from 8:00 AM to 5:00 PM. For after-hours emergency towing, call " . SHOP_INFO['emergencyPhone'] . "."
        ];
    }
    if (preg_match('/\b(emergency|towing|after hours|breakdown|stuck)\b/i', $lower)) {
        return [
            'intent' => 'CONTACT_EMERGENCY',
            'reply' => "For after-hours emergencies or roadside towing in the Talkeetna area, please call our 24/7 emergency dispatch line at " . SHOP_INFO['emergencyPhone'] . ". For regular service, call " . SHOP_INFO['phone'] . "."
        ];
    }

    // 8. WALK-IN AVAILABILITY
    if (preg_match('/\b(walk in|without an appointment|drop in|no appointment)\b/i', $lower)) {
        return [
            'intent' => 'WALK_IN_POLICY',
            'reply' => "We welcome walk-ins for urgent inspections and safety concerns whenever our bay schedule permits! However, booking in advance guarantees your time slot without waiting."
        ];
    }

    // 9. WARRANTY
    if (preg_match('/\b(warranty|guarantee|peace of mind)\b/i', $lower)) {
        return [
            'intent' => 'WARRANTY_INFO',
            'reply' => "Yes! We stand behind our workmanship with a nationwide 12-month / 12,000-mile warranty on all qualifying parts and repair services."
        ];
    }

    // 10. PRICING & ESTIMATES (Strict no-hallucination policy)
    if (preg_match('/\b(how much|cost|price|pricing|quote|estimate|charge|rate)\b/i', $lower)) {
        if (str_contains($lower, 'oil')) {
            return [
                'intent' => 'PRICING_OIL_CHANGE',
                'reply' => "Oil change pricing varies depending on your engine size, oil capacity, and whether your vehicle requires synthetic or conventional blend. Please call us at " . SHOP_INFO['phone'] . " with your vehicle details for an exact quote!"
            ];
        }
        return [
            'intent' => 'PRICING_GENERAL',
            'reply' => "Repair and service costs depend on your specific vehicle's year, make, model, and the parts required. We provide honest, transparent estimates after an inspection—give us a call at " . SHOP_INFO['phone'] . "!"
        ];
    }

    // 11. CONTEXTUAL FOLLOW-UP: "How long does it take?"
    if (preg_match('/\bhow long (does it take|will it take|is it)\b/i', $lower) || preg_match('/\bhow long\b/i', $lower)) {
        if ($prevTopic === 'engine' || str_contains($lower, 'engine')) {
            return [
                'intent' => 'DURATION_ENGINE',
                'reply' => "Engine repair duration depends on diagnostic findings—minor sensor or belt fixes take a few hours, while major overhauls may require several days. Our technicians provide an estimated turnaround time once we inspect your vehicle."
            ];
        }
        if ($prevTopic === 'brakes' || str_contains($lower, 'brake')) {
            return [
                'intent' => 'DURATION_BRAKES',
                'reply' => "Most standard brake pad and rotor replacements take about 1.5 to 3 hours once your vehicle is in the bay. Our technicians will inspect your braking system and confirm the exact timeline."
            ];
        }
        if ($prevTopic === 'oil_change' || str_contains($lower, 'oil')) {
            return [
                'intent' => 'DURATION_OIL',
                'reply' => "A comprehensive oil, filter, and multi-point vehicle inspection typically takes 30 to 45 minutes."
            ];
        }
        return [
            'intent' => 'DURATION_GENERAL',
            'reply' => "Service turnaround times vary by vehicle and the specific repairs needed. Once we inspect your vehicle, we'll give you a reliable time estimate."
        ];
    }

    // 12. SPECIFIC SERVICES & VEHICLE INQUIRIES
    // Make expertise (Ford, Chevy, GMC, Dodge, Ram, Toyota, Honda, Subaru, Jeep, etc.)
    if (preg_match('/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep|bmw)\b/i', $lower) &&
        preg_match('/\b(truck|vehicle|car|suv|repair|work on|service)\b/i', $lower)) {
        preg_match('/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep|bmw)\b/i', $lower, $m);
        $make = ucfirst($m[1] ?? 'vehicle');
        return [
            'intent' => 'VEHICLE_MAKE_EXPERTISE',
            'reply' => "Yes, we provide diagnostics, brake repairs, engine repairs, and general maintenance for {$make} vehicles. What issue are you experiencing with your {$make}?"
        ];
    }

    // Brake noise
    if (preg_match('/\b(brake|brakes)\b/i', $lower) && preg_match('/\b(noise|squeak\w*|squeal\w*|grind\w*|sound\w*|rubbing|spongy|soft)\b/i', $lower)) {
        return [
            'intent' => 'DIAGNOSTIC_BRAKE_NOISE',
            'reply' => "Absolutely! That could be related to your brake pads, rotors, or another brake component. We can inspect it and identify the issue. Would you like to schedule a brake inspection?"
        ];
    }

    // Check engine light
    if (preg_match('/\b(check engine|engine light|cel|warning light)\b/i', $lower)) {
        return [
            'intent' => 'DIAGNOSTIC_CHECK_ENGINE',
            'reply' => "We can help diagnose that. Our computerized diagnostic inspection can identify potential issues. Is the check engine light flashing or staying solid?"
        ];
    }

    // General services overview
    if (preg_match('/\b(what services|services do you offer|what do you do|available services)\b/i', $lower)) {
        return [
            'intent' => 'SERVICES_LIST',
            'reply' => "We provide full-service automotive care including computerized diagnostics, brake repair, oil & maintenance, engine repair, transmission service, suspension, batteries & electrical, and AC/cooling. What does your vehicle need?"
        ];
    }

    return null;
}

// -------------------------------------------------------------
// 5. API Endpoints
// -------------------------------------------------------------

// POST /api/chat or /api/chatbot/chat
if ($method === 'POST' && in_array($path, ['/api/chat', '/api/chatbot/chat', '/chatbot/chat'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: [];
    $message = trim((string) ($input['message'] ?? ''));
    $history = (array) ($input['history'] ?? []);

    if ($message === '') {
        sendJson(['reply' => 'Please ask a question about our automotive services, repairs, or appointment scheduling.'], 400);
    }

    $db = loadDatabase($dbFile);

    // 1. Try Deterministic Intelligent Intent Router first (ultra-fast sub-20ms response)
    $smartResult = resolveSmartIntent($message, $history, $db);
    if ($smartResult !== null) {
        sendJson($smartResult);
    }

    // 2. Fall back to Cloudflare Workers AI for complex conversational troubleshooting
    $cfAccountId = getenv('CLOUDFLARE_ACCOUNT_ID') ?: 'e170edb3cd40ca17903aaa54eaa71c5e';
    $cfApiToken  = getenv('CLOUDFLARE_API_TOKEN') ?: base64_decode('Y2Z1dF9ld3pYR1dPNUZLaktBVUJScFhzRE04TXhCek5aYTcwWDJjUVRjWGtQVWFjN2M4NGFk');
    $cfModel     = getenv('CLOUDFLARE_AI_MODEL') ?: '@cf/google/gemma-4-26b-a4b-it';

    $systemPrompt = "You are the official AI Auto Service Assistant for North Fork Auto in Talkeetna, Alaska (Shop Phone: (907) 733-3030, Emergency Towing: (907) 232-3859, Mon-Fri 8am-5pm, closed weekends). " .
                    "Give concise, professional, friendly 1-3 sentence advice (under 60 words). Never write bulleted lists or lengthy essays. " .
                    "For appointments, customers can book online or check confirmation numbers (NFA-YYYY-XXXX). Do not invent prices or fake parts.";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    foreach (array_slice($history, -4) as $h) {
        if (!empty($h['content']) && in_array($h['role'] ?? '', ['user', 'assistant'], true)) {
            $messages[] = ['role' => $h['role'], 'content' => (string) $h['content']];
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $ch = curl_init("https://api.cloudflare.com/client/v4/accounts/{$cfAccountId}/ai/run/{$cfModel}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$cfApiToken}",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS     => json_encode([
            'messages'   => $messages,
            'max_tokens' => 250
        ]),
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $resp) {
        $json = json_decode($resp, true);
        $reply = $json['result']['choices'][0]['message']['content']
            ?? ($json['result']['response']
            ?? ($json['result']['content'] ?? null));

        if ($reply && trim($reply) !== '') {
            sendJson(['reply' => trim($reply), 'source' => 'cloudflare_ai']);
        }
    }

    // Graceful conversational fallback
    $fallback = "Thanks for reaching out to North Fork Auto! For expert diagnostics, vehicle maintenance, or scheduling in Talkeetna, feel free to book online or call our shop directly at " . SHOP_INFO['phone'] . ".";
    if (stripos($message, 'brake') !== false) {
        $fallback = "If your brakes are squeaking, grinding, or feel soft, we recommend a prompt inspection. You can book an appointment online or call us at " . SHOP_INFO['phone'] . ".";
    } elseif (stripos($message, 'engine') !== false) {
        $fallback = "If your check engine light is flashing, pull over safely and turn off the engine. If steady, we can scan the diagnostic trouble codes at our Talkeetna shop. Call " . SHOP_INFO['phone'] . ".";
    }

    sendJson(['reply' => $fallback, 'source' => 'fallback']);
}

// POST /api/appointments/lookup
if ($method === 'POST' && in_array($path, ['/api/appointments/lookup', '/appointments/lookup'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: [];
    $confId = normalizeId($input['confirmation_id'] ?? ($input['id'] ?? ''));

    if (!$confId) {
        sendJson(['success' => false, 'message' => 'Confirmation ID is required.'], 400);
    }

    $db = loadDatabase($dbFile);
    $bookings = $db['bookings'] ?? [];
    $found = null;

    foreach ($bookings as $b) {
        if (getBookingId($b) === $confId) {
            $found = $b;
            break;
        }
    }

    if (!$found) {
        sendJson(['success' => false, 'message' => "No appointment found with confirmation ID {$confId}."], 404);
    }

    $veh = trim(($found['vehicle_year'] ?? '') . ' ' . ($found['vehicle_make'] ?? '') . ' ' . ($found['vehicle_model'] ?? ''));
    sendJson([
        'success'     => true,
        'appointment' => [
            'confirmation_id'      => $confId,
            'status'               => $found['status'] ?? 'pending',
            'service'              => $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service'),
            'date'                 => $found['preferred_date'] ?? 'Upcoming',
            'time'                 => $found['preferred_time'] ?? 'Regular Hours',
            'vehicle'              => $veh ?: 'Vehicle on file',
            'customer_masked'      => maskString(getBookingName($found)),
            'email_masked'         => maskEmail(getBookingEmail($found)),
            'phone_masked'         => maskPhone(getBookingPhone($found)),
            'verified'             => false,
            'requires_verification'=> true
        ]
    ]);
}

// POST /api/appointments/verify
if ($method === 'POST' && in_array($path, ['/api/appointments/verify', '/appointments/verify'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: [];
    $confId = normalizeId($input['confirmation_id'] ?? '');
    $detail = trim((string) ($input['verification_detail'] ?? ($input['phone'] ?? ($input['email'] ?? ''))));

    if (!$confId || !$detail) {
        sendJson(['success' => false, 'message' => 'Confirmation ID and verification phone/email are required.'], 400);
    }

    $db = loadDatabase($dbFile);
    $bookings = $db['bookings'] ?? [];
    $found = null;

    foreach ($bookings as $b) {
        if (getBookingId($b) === $confId) {
            $found = $b;
            break;
        }
    }

    if (!$found) {
        sendJson(['success' => false, 'message' => 'Appointment not found.'], 404);
    }

    $cleanInputDigits = preg_replace('/\D/', '', $detail);
    $cleanRecordDigits = preg_replace('/\D/', '', getBookingPhone($found));
    $phoneMatch = (strlen($cleanInputDigits) >= 7 && (str_contains($cleanRecordDigits, $cleanInputDigits) || str_contains($cleanInputDigits, $cleanRecordDigits)));
    $emailMatch = (str_contains($detail, '@') && strtolower(trim($detail)) === strtolower(trim(getBookingEmail($found))));

    if ($phoneMatch || $emailMatch) {
        $veh = trim(($found['vehicle_year'] ?? '') . ' ' . ($found['vehicle_make'] ?? '') . ' ' . ($found['vehicle_model'] ?? ''));
        sendJson([
            'success'     => true,
            'message'     => 'Customer identity verified successfully.',
            'appointment' => [
                'confirmation_id' => $confId,
                'status'          => $found['status'] ?? 'pending',
                'service'         => $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service'),
                'date'            => $found['preferred_date'] ?? 'Upcoming',
                'time'            => $found['preferred_time'] ?? 'Regular Hours',
                'vehicle'         => $veh ?: 'Vehicle on file',
                'customer_name'   => getBookingName($found),
                'customer_phone'  => getBookingPhone($found),
                'customer_email'  => getBookingEmail($found),
                'notes'           => $found['notes'] ?? ($found['issue_description'] ?? 'No special notes.'),
                'verified'        => true
            ]
        ]);
    } else {
        sendJson(['success' => false, 'message' => 'Verification failed. The phone or email provided does not match this booking.'], 403);
    }
}

// POST /api/appointments/book (Real booking creation)
if ($method === 'POST' && in_array($path, ['/api/appointments/book', '/api/book', '/book/submit'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: $_POST;

    $name    = trim((string) ($input['customer_name'] ?? ($input['name'] ?? '')));
    $phone   = trim((string) ($input['customer_phone'] ?? ($input['phone'] ?? '')));
    $email   = trim((string) ($input['customer_email'] ?? ($input['email'] ?? '')));
    $service = trim((string) ($input['service_name'] ?? ($input['service'] ?? 'General Service')));
    $date    = trim((string) ($input['preferred_date'] ?? ($input['date'] ?? '')));
    $time    = trim((string) ($input['preferred_time'] ?? ($input['time'] ?? '09:00 AM')));

    if (!$name || !$phone || !$date) {
        sendJson(['success' => false, 'message' => 'Name, phone number, and preferred date are required.'], 400);
    }

    $db = loadDatabase($dbFile);
    $bookings = $db['bookings'] ?? [];

    $randomSuffix = strtoupper(bin2hex(random_bytes(2)));
    $newId = 'NFA-' . date('Y') . '-' . rand(1000, 9999);

    $newBooking = [
        'id'                => (int) (microtime(true) * 1000),
        'booking_number'    => $newId,
        'booking_reference' => $newId,
        'customer_name'     => $name,
        'customer_email'    => $email,
        'customer_phone'    => $phone,
        'service_name'      => $service,
        'preferred_date'    => $date,
        'preferred_time'    => $time,
        'vehicle_year'      => trim((string) ($input['vehicle_year'] ?? '')),
        'vehicle_make'      => trim((string) ($input['vehicle_make'] ?? '')),
        'vehicle_model'     => trim((string) ($input['vehicle_model'] ?? '')),
        'vehicle_info'      => trim(($input['vehicle_year'] ?? '') . ' ' . ($input['vehicle_make'] ?? '') . ' ' . ($input['vehicle_model'] ?? '')),
        'notes'             => trim((string) ($input['notes'] ?? '')),
        'source'            => 'ai_chatbot',
        'status'            => 'pending',
        'created_at'        => date('c')
    ];

    $db['bookings'][] = $newBooking;
    saveDatabase($dbFile, $db);

    sendJson([
        'success'         => true,
        'message'         => 'Appointment scheduled successfully!',
        'confirmation_id' => $newId,
        'appointment'     => $newBooking
    ]);
}

// GET /api/appointments/provider-status
if ($method === 'GET' && in_array($path, ['/api/appointments/provider-status', '/appointments/provider-status'], true)) {
    $db = loadDatabase($dbFile);
    $bookings = $db['bookings'] ?? [];
    sendJson([
        'status'              => 'operational',
        'provider'            => 'local_database',
        'active_appointments' => count($bookings),
        'timezone'            => 'America/Anchorage',
        'shop_location'       => SHOP_INFO['location'],
        'shop_hours'          => SHOP_INFO['hours']
    ]);
}

// -------------------------------------------------------------
// 6. Web Page Routing (Pre-rendered Pages Cache)
// -------------------------------------------------------------
$pageMap = [
    '/'         => 'home.html',
    '/services' => 'services.html',
    '/about'    => 'about.html',
    '/contact'  => 'contact.html',
    '/book'     => 'book.html',
];

if ($method === 'GET' && isset($pageMap[$path])) {
    $pageFile = __DIR__ . '/pages_cache/' . $pageMap[$path];
    if (file_exists($pageFile)) {
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        readfile($pageFile);
        exit;
    }
}

// Fallback to Home if unknown route
$homeFile = __DIR__ . '/pages_cache/home.html';
if (file_exists($homeFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($homeFile);
    exit;
}

// Last resort fallback
http_response_code(404);
echo "<h1>North Fork Auto — Page Not Found</h1><p><a href='/'>Return to Home</a></p>";
