<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class AppointmentVerificationService
{
    const MAX_FAILED_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    /**
     * Check if a text contains an email address or phone number.
     */
    public function extractVerificationDetail(string $text): ?array
    {
        $text = trim($text);

        // 1. Detect Email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            return [
                'type' => 'email',
                'value' => strtolower($matches[0])
            ];
        }

        // 2. Detect US / International Phone Number
        // Matches (907) 555-7766, 907-555-7766, 9075557766, +1 907 555 7766, etc.
        if (preg_match('/(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})/', $text, $matches)) {
            $digits = preg_replace('/[^0-9]/', '', $matches[0]);
            return [
                'type' => 'phone',
                'value' => $digits
            ];
        }

        // 3. Fallback for raw digits (7 to 15 digits)
        if (preg_match('/\b\d{7,15}\b/', $text, $matches)) {
            return [
                'type' => 'phone',
                'value' => $matches[0]
            ];
        }

        return null;
    }

    /**
     * Verify provided phone or email against an appointment record.
     */
    public function verifyCustomer(array $appointment, string $providedDetail, string $sessionId = '', string $ip = ''): array
    {
        $rateLimitKey = 'appt_verify_lockout:' . ($sessionId ?: $ip ?: 'unknown');

        // Check Rate Limiting / Lockout
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_FAILED_ATTEMPTS)) {
            $secondsRemaining = RateLimiter::availableIn($rateLimitKey);
            return [
                'verified' => false,
                'locked_out' => true,
                'message' => "Too many verification attempts. For your security, this feature is temporarily locked. Please wait {$secondsRemaining} seconds or call North Fork Auto at (907) 733-3030."
            ];
        }

        $provided = trim($providedDetail);
        $recordEmail = strtolower(trim($appointment['customer_email'] ?? ''));
        $recordPhone = preg_replace('/[^0-9]/', '', $appointment['customer_phone'] ?? '');

        $isMatch = false;

        // Check Email Match
        if (filter_var($provided, FILTER_VALIDATE_EMAIL)) {
            if (!empty($recordEmail) && strtolower($provided) === $recordEmail) {
                $isMatch = true;
            }
        }

        // Check Phone Match
        $cleanProvidedDigits = preg_replace('/[^0-9]/', '', $provided);
        if (!$isMatch && strlen($cleanProvidedDigits) >= 7) {
            // Compare last 7, 10, or exact digits
            if (!empty($recordPhone)) {
                if ($cleanProvidedDigits === $recordPhone) {
                    $isMatch = true;
                } elseif (strlen($cleanProvidedDigits) >= 10 && strlen($recordPhone) >= 10 && substr($cleanProvidedDigits, -10) === substr($recordPhone, -10)) {
                    $isMatch = true;
                } elseif (strlen($cleanProvidedDigits) >= 7 && strlen($recordPhone) >= 7 && substr($cleanProvidedDigits, -7) === substr($recordPhone, -7)) {
                    $isMatch = true;
                }
            }
        }

        if ($isMatch) {
            // Reset attempts on success
            RateLimiter::clear($rateLimitKey);
            return [
                'verified' => true,
                'locked_out' => false,
                'message' => 'Verification successful.'
            ];
        }

        // Increment failed attempt counter (15-minute decay)
        RateLimiter::hit($rateLimitKey, self::LOCKOUT_MINUTES * 60);
        $attemptsLeft = self::MAX_FAILED_ATTEMPTS - RateLimiter::attempts($rateLimitKey);
        $attemptsLeft = max(0, $attemptsLeft);

        return [
            'verified' => false,
            'locked_out' => false,
            'attempts_left' => $attemptsLeft,
            'message' => 'The phone number or email address provided does not match our records for this confirmation number.'
        ];
    }
}
