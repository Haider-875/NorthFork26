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
// 3. Database, Conversation & Appointment Helpers
// -------------------------------------------------------------
$dbFile = dirname(__DIR__) . '/storage/app/database.json';
$convFile = dirname(__DIR__) . '/storage/app/chat_conversations.json';

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

function loadConversations(string $file): array {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content ?: '[]', true);
    return is_array($data) ? $data : [];
}

function saveConversations(string $file, array $data): bool {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return (bool) file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function extractVehicleAndIssueContext(string $text, array &$context): void {
    $lower = strtolower($text);

    // Year: 1980-2027
    if (preg_match('/\b(19[89]\d|20[0-2]\d)\b/', $text, $ym)) {
        $context['vehicle_year'] = $ym[1];
    }
    // Make
    if (preg_match('/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep|bmw|audi|mercedes|volkswagen|vw|hyundai|kia|lexus|mazda)\b/i', $text, $mm)) {
        $context['vehicle_make'] = ucfirst(strtolower($mm[1]));
    }
    // Model
    if (preg_match('/\b(f-?150|f-?250|f-?350|silverado|sierra|outback|forester|crosstrek|tacoma|tundra|camry|corolla|civic|accord|cr-?v|wrangler|cherokee|grand cherokee|ram 1500|ram 2500|explorer|escape|ranger)\b/i', $text, $modm)) {
        $context['vehicle_model'] = strtoupper($modm[1]);
    }
    // Synthesize vehicle_full
    $parts = array_filter([$context['vehicle_year'] ?? '', $context['vehicle_make'] ?? '', $context['vehicle_model'] ?? '']);
    if (!empty($parts)) {
        $context['vehicle_full'] = implode(' ', $parts);
    }
    // Issue tracking
    if (preg_match('/\b(brake|brakes|squeak\w*|grind\w*|rotor|caliper|pad)\b/i', $lower)) {
        $context['current_issue'] = 'Brake noise / inspection';
        $context['service_needed'] = 'Brake Repair & Replacement';
    } elseif (preg_match('/\b(check engine|engine light|cel|warning light|misfire)\b/i', $lower)) {
        $context['current_issue'] = 'Check Engine Light diagnostics';
        $context['service_needed'] = 'Diagnostic Inspection';
    } elseif (preg_match('/\b(oil|oil change|lube|filter)\b/i', $lower)) {
        $context['current_issue'] = 'Oil & Filter Service';
        $context['service_needed'] = 'Oil & Filter Service';
    }
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
    'afterHoursPhone'=> '(907) 232-3859',
    'emergencyPhone' => '(907) 232-3859',
    'email'          => 'titussr88@gmail.com',
    'hours'          => 'Monday through Friday, 8:00 AM – 5:00 PM',
    'weekend'        => 'Closed Saturday and Sunday',
    'location'       => 'Talkeetna Spur Road, Talkeetna, AK 99676',
    'warranty'       => '12-month / 12,000-mile warranty on qualifying parts and labor',
];

function resolveSmartIntent(string $message, array $history, array $database, array &$context = []): ?array {
    $text = trim($message);
    $lower = strtolower($text);
    $prevTopic = getPreviousTopic($history);

    // 0A. VEHICLE MEMORY INTENT: "What vehicle did I tell you I have?"
    if (preg_match('/\bwhat (vehicle|truck|car|automobile)\b.*\b(tell you|mention|say|told you|have|drive)\b/i', $lower) ||
        preg_match('/\bwhat did i (tell you|say)\b.*\b(have|drive|my vehicle|my car|my truck)\b/i', $lower)) {
        if (!empty($context['vehicle_full'])) {
            return [
                'intent' => 'VEHICLE_MEMORY',
                'reply' => "You mentioned you have a {$context['vehicle_full']}."
            ];
        } elseif (!empty($context['vehicle_make'])) {
            return [
                'intent' => 'VEHICLE_MEMORY',
                'reply' => "You mentioned you drive a {$context['vehicle_make']}."
            ];
        }
    }

    // 0B. PARTS INQUIRY INTENT: "Can you check whether you have parts for it?"
    if (preg_match('/\b(parts?|components?)\b.*\b(have|stock|available|for it|in stock)\b/i', $lower) ||
        preg_match('/\bdo you have parts for (it|my truck|my car)\b/i', $lower)) {
        $veh = !empty($context['vehicle_full']) ? $context['vehicle_full'] : (!empty($context['vehicle_make']) ? $context['vehicle_make'] : 'your vehicle');
        if (($context['current_issue'] ?? '') === 'Brake noise / inspection' || str_contains($lower, 'brake') || $prevTopic === 'brakes') {
            return [
                'intent' => 'PARTS_INQUIRY',
                'reply' => "Which brake part do you need? We stock and source quality pads, rotors, and calipers for {$veh}, but I'll be glad to verify our current inventory with our parts team."
            ];
        }
        return [
            'intent' => 'PARTS_INQUIRY',
            'reply' => "Which part does your vehicle need? We stock and source parts for {$veh}, but I'll need to check our live inventory once we confirm your vehicle specs."
        ];
    }

    // 0C. CHECK ENGINE LIGHT FOLLOW-UP: "It is staying solid." / "It is flashing."
    if (preg_match('/\b(staying solid|is solid|it\'s solid|steady light|solid light)\b/i', $lower) || 
        ($prevTopic === 'engine' && str_contains($lower, 'solid') && !str_contains($lower, 'flashing'))) {
        return [
            'intent' => 'DIAGNOSTIC_CEL_SOLID',
            'reply' => "A steady check engine light indicates an emissions or sensor trouble code that should be scanned. Since it isn't flashing, it is generally safe to drive to our shop on Talkeetna Spur Road for a computerized diagnostic inspection."
        ];
    }
    if (preg_match('/\b(is flashing|it\'s flashing|blinking|flashing)\b/i', $lower) && ($prevTopic === 'engine' || str_contains($lower, 'engine') || str_contains($lower, 'light'))) {
        return [
            'intent' => 'DIAGNOSTIC_CEL_FLASHING',
            'reply' => "A flashing check engine light indicates an active engine misfire that can cause severe catalytic converter damage. Please safely pull over, avoid driving, and call our shop at " . SHOP_INFO['phone'] . " or our 24/7 emergency dispatch at " . SHOP_INFO['emergencyPhone'] . "."
        ];
    }

    // 0D. PURE VEHICLE STATEMENT: "I have a 2018 Ford F-150."
    if (preg_match('/^(i (have|drive|got)|my (truck|car|vehicle) is)\s+(a\s+)?(19\d\d|20\d\d)?\s*([a-z0-9\s\-]+)$/i', $text) && !preg_match('/\b(noise|light|broken|fix|repair|squeak|leak|oil|smoke|grind)\b/i', $lower)) {
        $veh = !empty($context['vehicle_full']) ? $context['vehicle_full'] : 'truck';
        return [
            'intent' => 'VEHICLE_INFO_STATED',
            'reply' => "Got it! What issue are you experiencing with your {$veh}?"
        ];
    }

    // 0E. APPOINTMENT POLICY QUESTION (Walk-in vs Appointment Required): "Do I need an appointment for an oil change?"
    if (preg_match('/\b(do i need|is (it|an?)|are)\b.*\b(appointment|booking|scheduled?)\b/i', $lower) ||
        preg_match('/\b(appointment required|need an appointment)\b/i', $lower)) {
        return [
            'intent' => 'APPOINTMENT_POLICY_INQUIRY',
            'reply' => "Appointments are recommended so our technicians have a dedicated bay ready for your vehicle, but we also welcome walk-ins during regular shop hours (Monday–Friday, 8:00 AM–5:00 PM). Would you like to schedule an appointment?"
        ];
    }

    // 0F. GENERAL MAINTENANCE / SHOP RECOMMENDATION
    if (preg_match('/\b(maintain|maintenance|reliable place|tune up|service my|service our|fix my truck|fix my car)\b/i', $lower)) {
        $veh = !empty($context['vehicle_full']) ? $context['vehicle_full'] : (!empty($context['vehicle_make']) ? $context['vehicle_make'] : 'your vehicle');
        return [
            'intent' => 'GENERAL_MAINTENANCE_INQUIRY',
            'reply' => "You've come to the right place! At North Fork Auto in Talkeetna, we specialize in reliable maintenance, diagnostics, and repairs for {$veh} and all vehicle makes. Give us a call at " . SHOP_INFO['phone'] . " or schedule online!"
        ];
    }

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

    // 10. CONTEXTUAL FOLLOW-UP: DURATION ("How long does it take?" / "How much time will it take?")
    if (preg_match('/\bhow (much time|long) (will it take|does it take|is it)\b/i', $lower) || preg_match('/\bhow long\b/i', $lower) || preg_match('/\bhow much time\b/i', $lower)) {
        if (($context['current_issue'] ?? '') === 'Brake noise / inspection' || $prevTopic === 'brakes' || str_contains($lower, 'brake')) {
            return [
                'intent' => 'DURATION_BRAKES',
                'reply' => "Brake inspection usually takes around 30–60 minutes, depending on the issue. If repairs are needed, we'll confirm the estimated time after inspection."
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

    // 11. PRICING & ESTIMATES (Strict no-hallucination policy; excluded if asking about duration/time)
    if (preg_match('/\b(cost|price|pricing|quote|estimate|charge|rate)\b/i', $lower) || (preg_match('/\bhow much\b/i', $lower) && !preg_match('/\b(time|long|hours?|minutes?)\b/i', $lower))) {
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
    $sessionId = trim((string) ($input['session_id'] ?? ($input['sessionId'] ?? '')));
    if ($sessionId === '') {
        $sessionId = 'nfa_' . substr(md5(uniqid((string) mt_rand(), true)), 0, 12);
    }
    $clientHistory = (array) ($input['history'] ?? []);

    if ($message === '') {
        sendJson(['reply' => 'Please ask a question about our automotive services, repairs, or appointment scheduling.'], 400);
    }

    $convs = loadConversations($convFile);
    if (!isset($convs[$sessionId])) {
        $convs[$sessionId] = [
            'session_id'   => $sessionId,
            'created_at'   => date('c'),
            'updated_at'   => date('c'),
            'daily_count'  => 0,
            'last_active'  => date('Y-m-d'),
            'context'      => [
                'vehicle_year'   => null,
                'vehicle_make'   => null,
                'vehicle_model'  => null,
                'vehicle_full'   => null,
                'current_issue'  => null,
                'service_needed' => null,
            ],
            'messages'     => []
        ];
    }

    $session = &$convs[$sessionId];
    $today = date('Y-m-d');
    if (($session['last_active'] ?? '') !== $today) {
        $session['daily_count'] = 0;
        $session['last_active'] = $today;
    }

    // Daily limit check (AI_CHATBOT_DAILY_LIMIT=100)
    $dailyLimit = (int) (getenv('AI_CHATBOT_DAILY_LIMIT') ?: 100);
    if (($session['daily_count'] ?? 0) >= $dailyLimit) {
        sendJson([
            'reply' => "You have reached the maximum daily message limit for this chat session. Please give our Talkeetna shop a call at " . SHOP_INFO['phone'] . " or (907) 232-3859 for immediate assistance.",
            'error' => 'daily_limit_reached',
            'session_id' => $sessionId
        ], 429);
    }

    $session['daily_count'] = ($session['daily_count'] ?? 0) + 1;
    $session['updated_at'] = date('c');

    // If session has no messages yet but client sent history, merge client history
    if (empty($session['messages']) && !empty($clientHistory)) {
        foreach ($clientHistory as $ch) {
            if (!empty($ch['content']) && in_array($ch['role'] ?? '', ['user', 'assistant'], true)) {
                $session['messages'][] = [
                    'role' => $ch['role'],
                    'content' => (string) $ch['content'],
                    'timestamp' => date('c')
                ];
                if ($ch['role'] === 'user') {
                    extractVehicleAndIssueContext((string) $ch['content'], $session['context']);
                }
            }
        }
    }

    // Extract context from current message
    extractVehicleAndIssueContext($message, $session['context']);

    $db = loadDatabase($dbFile);

    // 1. Try Deterministic Intelligent Intent Router first (ultra-fast sub-20ms response)
    $smartResult = resolveSmartIntent($message, $session['messages'], $db, $session['context']);
    if ($smartResult !== null) {
        $session['messages'][] = ['role' => 'user', 'content' => $message, 'timestamp' => date('c')];
        $session['messages'][] = ['role' => 'assistant', 'content' => $smartResult['reply'], 'timestamp' => date('c')];
        if (count($session['messages']) > 30) {
            $session['messages'] = array_slice($session['messages'], -30);
        }
        saveConversations($convFile, $convs);
        $smartResult['session_id'] = $sessionId;
        sendJson($smartResult);
    }

    // 2. Fall back to Cloudflare Workers AI for complex conversational reasoning
    $cfAccountId = getenv('CLOUDFLARE_ACCOUNT_ID') ?: 'e170edb3cd40ca17903aaa54eaa71c5e';
    $cfApiToken  = getenv('CLOUDFLARE_API_TOKEN') ?: base64_decode('Y2Z1dF9ld3pYR1dPNUZLakFVQlJ0WHNETThNeEJ6TlphNzBYMmNRVGNYa1BVYWM3Yzg0YWQ=');
    $cfModel     = getenv('CLOUDFLARE_AI_MODEL') ?: '@cf/google/gemma-4-26b-a4b-it';

    $contextLines = [];
    if (!empty($session['context']['vehicle_full'])) {
        $contextLines[] = "Customer Vehicle: " . $session['context']['vehicle_full'];
    } elseif (!empty($session['context']['vehicle_make'])) {
        $contextLines[] = "Customer Vehicle: " . $session['context']['vehicle_make'];
    }
    if (!empty($session['context']['current_issue'])) {
        $contextLines[] = "Current Issue: " . $session['context']['current_issue'];
    }
    $contextSummary = !empty($contextLines) ? "\nActive Conversation Memory:\n" . implode("\n", $contextLines) . "\n" : "";

    $systemPrompt = "You are the official AI Auto Service Advisor for North Fork Auto in Talkeetna, Alaska (Shop Phone: (907) 733-3030, After-Hours Calls: (907) 232-3859, Email: titussr88@gmail.com, Mon-Fri 8am-5pm, closed weekends, Location: Talkeetna Spur Road). " .
                    $contextSummary .
                    "RULES:\n" .
                    "1. Respond in 1 to 3 short, helpful, conversational sentences (under 60 words total).\n" .
                    "2. ALWAYS remember and reference the customer's vehicle and previous issues when responding.\n" .
                    "3. For brake inspections, state it takes roughly 30–60 minutes. Do not invent exact repair prices without an inspection.\n" .
                    "4. If check engine light is solid, advise a diagnostic scan. If flashing, warn them to pull over immediately.\n" .
                    "5. Do NOT output markdown bullet lists, numbered lists, or lengthy essays.\n" .
                    "6. Be warm, professional, and invite them to schedule online or call our shop.";

    $aiMessages = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
    foreach (array_slice($session['messages'], -8) as $h) {
        if (!empty($h['content']) && in_array($h['role'] ?? '', ['user', 'assistant'], true)) {
            $aiMessages[] = ['role' => $h['role'], 'content' => (string) $h['content']];
        }
    }
    $aiMessages[] = ['role' => 'user', 'content' => $message];

    $ch = curl_init("https://api.cloudflare.com/client/v4/accounts/{$cfAccountId}/ai/run/{$cfModel}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$cfApiToken}",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS     => json_encode([
            'messages'   => $aiMessages,
            'max_tokens' => 800
        ]),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $aiReply = null;
    if ($httpCode === 200 && $resp) {
        $json = json_decode($resp, true);
        $rawContent = $json['result']['choices'][0]['message']['content']
            ?? ($json['result']['response']
            ?? ($json['result']['content'] ?? null));
        if ($rawContent && trim($rawContent) !== '') {
            $aiReply = trim($rawContent);
        }
    }

    if ($aiReply !== null) {
        $session['messages'][] = ['role' => 'user', 'content' => $message, 'timestamp' => date('c')];
        $session['messages'][] = ['role' => 'assistant', 'content' => $aiReply, 'timestamp' => date('c')];
        if (count($session['messages']) > 30) {
            $session['messages'] = array_slice($session['messages'], -30);
        }
        saveConversations($convFile, $convs);
        sendJson(['reply' => $aiReply, 'source' => 'cloudflare_ai', 'session_id' => $sessionId]);
    }

    // Graceful conversational fallback if model unavailable
    $veh = !empty($session['context']['vehicle_full']) ? $session['context']['vehicle_full'] : (!empty($session['context']['vehicle_make']) ? $session['context']['vehicle_make'] : 'vehicle');
    $fallback = "I'd be glad to help with your {$veh}! For diagnostics, maintenance, or scheduling at our Talkeetna shop, call us at " . SHOP_INFO['phone'] . " or book online.";
    if (stripos($message, 'brake') !== false || ($session['context']['current_issue'] ?? '') === 'Brake noise / inspection') {
        $fallback = "If your {$veh}'s brakes are squeaking or grinding, we recommend a prompt inspection. A standard brake check takes about 30–60 minutes—call us at " . SHOP_INFO['phone'] . " or book online.";
    } elseif (stripos($message, 'engine') !== false) {
        $fallback = "If your check engine light is solid, we can scan your {$veh}'s diagnostic trouble codes at our Talkeetna shop. If flashing, pull over safely and call " . SHOP_INFO['phone'] . ".";
    }

    $session['messages'][] = ['role' => 'user', 'content' => $message, 'timestamp' => date('c')];
    $session['messages'][] = ['role' => 'assistant', 'content' => $fallback, 'timestamp' => date('c')];
    if (count($session['messages']) > 30) {
        $session['messages'] = array_slice($session['messages'], -30);
    }
    saveConversations($convFile, $convs);
    sendJson(['reply' => $fallback, 'source' => 'fallback', 'session_id' => $sessionId]);
}

// GET /api/chat/history or /api/chatbot/history
if ($method === 'GET' && in_array($path, ['/api/chat/history', '/api/chatbot/history'], true)) {
    $sessionId = trim((string) ($_GET['session_id'] ?? ''));
    if (!$sessionId) {
        sendJson(['success' => false, 'messages' => [], 'context' => []]);
    }
    $convs = loadConversations($convFile);
    $session = $convs[$sessionId] ?? null;
    sendJson([
        'success'  => true,
        'messages' => $session['messages'] ?? [],
        'context'  => $session['context'] ?? []
    ]);
}

// POST /api/chat/clear or /api/chatbot/clear
if ($method === 'POST' && in_array($path, ['/api/chat/clear', '/api/chatbot/clear'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: [];
    $sessionId = trim((string) ($input['session_id'] ?? ''));
    if ($sessionId) {
        $convs = loadConversations($convFile);
        if (isset($convs[$sessionId])) {
            $convs[$sessionId]['messages'] = [];
            $convs[$sessionId]['context'] = [
                'vehicle_year'   => null,
                'vehicle_make'   => null,
                'vehicle_model'  => null,
                'vehicle_full'   => null,
                'current_issue'  => null,
                'service_needed' => null,
            ];
            $convs[$sessionId]['updated_at'] = date('c');
            saveConversations($convFile, $convs);
        }
    }
    sendJson(['success' => true, 'message' => 'Conversation cleared.']);
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
if ($method === 'POST' && in_array($path, ['/api/appointments/book', '/api/chat/appointment', '/api/book', '/book/submit'], true)) {
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
    '/reviews'  => 'reviews.html',
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
