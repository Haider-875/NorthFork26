<?php
/**
 * North Fork Auto — Wasmer Edge Application Entrypoint
 * Location: Talkeetna, Alaska, USA
 * Framework: Modern PHP 8.3 / Wasmer PHPix Runtime
 */

declare(strict_types=1);

// Set default timezone to Alaska (North Fork Auto local time)
date_default_timezone_set('America/Anchorage');

// -------------------------------------------------------------
// 1. Environment Variable Loader (.env support)
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
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

// Helper: get database
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

// Helper: normalize confirmation ID
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

// -------------------------------------------------------------
// 3. API Endpoints
// -------------------------------------------------------------

// POST /api/chat or /api/chatbot/chat
if ($method === 'POST' && in_array($path, ['/api/chat', '/api/chatbot/chat', '/chatbot/chat'], true)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true) ?: [];
    $message = trim((string) ($input['message'] ?? ''));
    $history = (array) ($input['history'] ?? []);

    if ($message === '') {
        sendJson(['reply' => 'Please ask a question about our services or vehicle repair.'], 400);
    }

    // Check Appointment Lookup Intent
    $confId = extractIdFromText($message);
    if ($confId || preg_match('/\b(check|status|track|find|where is|when is)\b.*\b(appointment|booking|schedule)\b/i', $message)) {
        $db = loadDatabase($dbFile);
        $bookings = $db['bookings'] ?? [];

        if ($confId) {
            $norm = normalizeId($confId);
            $found = null;
            foreach ($bookings as $b) {
                if (getBookingId($b) === $norm) {
                    $found = $b;
                    break;
                }
            }

            if ($found) {
                $status = $found['status'] ?? 'pending';
                $service = $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service');
                $date = $found['preferred_date'] ?? 'Upcoming';
                $time = $found['preferred_time'] ?? 'Regular Shop Hours';
                $veh = trim(($found['vehicle_year'] ?? '') . ' ' . ($found['vehicle_make'] ?? '') . ' ' . ($found['vehicle_model'] ?? ''));

                sendJson([
                    'reply' => "I found appointment {$norm} for {$service} on {$date} at {$time} (Status: " . ucfirst($status) . "). For security and full details, you can verify your phone number or email anytime.",
                    'appointment' => [
                        'confirmation_id' => $norm,
                        'status' => $status,
                        'service' => $service,
                        'date' => $date,
                        'time' => $time,
                        'vehicle' => $veh ?: 'Vehicle on file',
                        'customer_masked' => maskString(getBookingName($found)),
                        'verified' => false,
                        'requires_verification' => true
                    ]
                ]);
            } else {
                sendJson([
                    'reply' => "I couldn't locate an appointment with confirmation number {$confId}. Please double-check your confirmation ID or call North Fork Auto at (907) 733-3030.",
                    'appointment' => null
                ]);
            }
        } else {
            sendJson([
                'reply' => "I can check your appointment status. Please provide your confirmation number (like NFA-2026-8659) or the phone number used when booking.",
                'action' => 'prompt_confirmation_id'
            ]);
        }
    }

    // Call Cloudflare Workers AI
    $cfAccountId = getenv('CLOUDFLARE_ACCOUNT_ID') ?: 'bbf949d03429fa2feeb8c50e2ae2c68a';
    $cfApiToken = getenv('CLOUDFLARE_API_TOKEN') ?: '40O1o5x3zL8w9c1M3n0x6k2_TEST_TOKEN';
    $cfModel = getenv('CLOUDFLARE_AI_MODEL') ?: '@cf/google/gemma-4-26b-a4b-it';

    $systemPrompt = "You are the official AI Auto Service Assistant for North Fork Auto in Talkeetna, Alaska (Phone: (907) 733-3030, Emergency/Towing: (907) 232-3859, Mon-Fri 8am-6pm). " .
                    "Give helpful, friendly, and concise automotive service guidance in 1-3 short sentences (25-60 words max). Never write lengthy introductions or lists. " .
                    "For appointments, customers can book online or track their confirmation ID (NFA-YYYY-XXXX).";

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
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$cfApiToken}",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode(['messages' => $messages]),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $resp) {
        $json = json_decode($resp, true);
        $reply = $json['result']['response'] ?? ($json['result']['content'] ?? null);
        if ($reply) {
            sendJson(['reply' => trim($reply)]);
        }
    }

    // Fallback response if Cloudflare is unreachable or quota limited
    $fallback = "Thanks for reaching out to North Fork Auto in Talkeetna! For assistance with repairs, diagnostics, or scheduling, feel free to book online or call our shop directly at (907) 733-3030.";
    if (stripos($message, 'brake') !== false) {
        $fallback = "If your brakes are squeaking, grinding, or feel soft, we recommend a prompt inspection. You can book an appointment online or call us at (907) 733-3030.";
    } elseif (stripos($message, 'engine') !== false) {
        $fallback = "If your check engine light is flashing, pull over safely and turn off the engine. If steady, we can scan the diagnostic trouble codes at our Talkeetna shop. Call (907) 733-3030.";
    }

    sendJson(['reply' => $fallback]);
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
        'success' => true,
        'appointment' => [
            'confirmation_id' => $confId,
            'status' => $found['status'] ?? 'pending',
            'service' => $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service'),
            'date' => $found['preferred_date'] ?? 'Upcoming',
            'time' => $found['preferred_time'] ?? 'Regular Hours',
            'vehicle' => $veh ?: 'Vehicle on file',
            'customer_masked' => maskString(getBookingName($found)),
            'email_masked' => maskEmail(getBookingEmail($found)),
            'phone_masked' => maskPhone(getBookingPhone($found)),
            'verified' => false,
            'requires_verification' => true
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

    // Verify phone (digits match) or email (lowercase match)
    $cleanInputDigits = preg_replace('/\D/', '', $detail);
    $cleanRecordDigits = preg_replace('/\D/', '', getBookingPhone($found));
    $phoneMatch = (strlen($cleanInputDigits) >= 7 && (str_contains($cleanRecordDigits, $cleanInputDigits) || str_contains($cleanInputDigits, $cleanRecordDigits)));
    $emailMatch = (str_contains($detail, '@') && strtolower(trim($detail)) === strtolower(trim(getBookingEmail($found))));

    if ($phoneMatch || $emailMatch) {
        $veh = trim(($found['vehicle_year'] ?? '') . ' ' . ($found['vehicle_make'] ?? '') . ' ' . ($found['vehicle_model'] ?? ''));
        sendJson([
            'success' => true,
            'message' => 'Customer identity verified successfully.',
            'appointment' => [
                'confirmation_id' => $confId,
                'status' => $found['status'] ?? 'pending',
                'service' => $found['service_name'] ?? ($found['service_type'] ?? 'Automotive Service'),
                'date' => $found['preferred_date'] ?? 'Upcoming',
                'time' => $found['preferred_time'] ?? 'Regular Hours',
                'vehicle' => $veh ?: 'Vehicle on file',
                'customer_name' => getBookingName($found),
                'customer_phone' => getBookingPhone($found),
                'customer_email' => getBookingEmail($found),
                'notes' => $found['notes'] ?? ($found['issue_description'] ?? 'No special notes.'),
                'verified' => true
            ]
        ]);
    } else {
        sendJson(['success' => false, 'message' => 'Verification failed. The phone or email provided does not match this booking.'], 403);
    }
}

// GET /api/appointments/provider-status
if ($method === 'GET' && in_array($path, ['/api/appointments/provider-status', '/appointments/provider-status'], true)) {
    $db = loadDatabase($dbFile);
    $bookings = $db['bookings'] ?? [];
    sendJson([
        'status' => 'operational',
        'provider' => 'local_storage',
        'active_appointments' => count($bookings),
        'timezone' => 'America/Anchorage',
        'shop_location' => 'Talkeetna, Alaska'
    ]);
}

// -------------------------------------------------------------
// 4. Web Page Routing (Pre-rendered Pages Cache)
// -------------------------------------------------------------
$pageMap = [
    '/' => 'home.html',
    '/services' => 'services.html',
    '/about' => 'about.html',
    '/contact' => 'contact.html',
    '/book' => 'book.html',
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
