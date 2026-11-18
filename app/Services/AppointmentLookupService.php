<?php

namespace App\Services;

use App\Models\Booking;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AppointmentLookupService
{
    protected string $databaseFile;
    protected string $timezone;

    public function __construct()
    {
        $this->databaseFile = storage_path('app/database.json');
        $this->timezone = env('APP_TIMEZONE', 'America/Anchorage');
    }

    /**
     * Extract a confirmation number (e.g., NFA-2026-8659) from natural language text.
     */
    public function extractConfirmationId(string $text): ?string
    {
        // Matches patterns like NFA-2026-8659, NFA-2026-ABCD, NFA20268659, #NFA-2026-8659
        if (preg_match('/\b(?:#?\s*)?(NFA[-_\s]?(?:20\d{2})[-_\s]?[A-Z0-9]{4,6})\b/i', $text, $matches)) {
            return $this->normalizeId($matches[1]);
        }

        // Generic fallback for any NFA- prefixed reference
        if (preg_match('/\b(NFA[-_\s]?[A-Z0-9]{4,12})\b/i', $text, $matches)) {
            return $this->normalizeId($matches[1]);
        }

        return null;
    }

    /**
     * Normalize confirmation ID to standard uppercase NFA-YYYY-XXXX format.
     */
    public function normalizeId(string $id): string
    {
        $clean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $id)));
        if (preg_match('/^NFA(\d{4})([A-Z0-9]+)$/', $clean, $parts)) {
            return "NFA-{$parts[1]}-{$parts[2]}";
        }
        return strtoupper(trim($id));
    }

    /**
     * Find appointment record in the database source of truth.
     */
    public function findAppointment(string $confirmationId): ?array
    {
        $normalizedId = $this->normalizeId($confirmationId);

        // 1. Check primary persistent file: storage/app/database.json
        if (File::exists($this->databaseFile)) {
            try {
                $content = File::get($this->databaseFile);
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Strip BOM
                $db = json_decode($content, true);
                if (isset($db['bookings']) && is_array($db['bookings'])) {
                    foreach ($db['bookings'] as $booking) {
                        $bNumber = $this->normalizeId($booking['booking_number'] ?? $booking['booking_reference'] ?? '');
                        if ($bNumber === $normalizedId || (isset($booking['id']) && (string)$booking['id'] === $normalizedId)) {
                            return $this->standardizeBooking($booking);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error reading appointments from database.json: ' . $e->getMessage());
            }
        }

        // 2. Check Eloquent Booking model if database is active
        try {
            $booking = Booking::where('booking_number', $normalizedId)
                ->orWhere('id', $normalizedId)
                ->first();
            if ($booking) {
                return $this->standardizeBooking($booking->toArray());
            }
        } catch (\Exception $e) {
            // Database might not be running or migrated
        }

        return null;
    }

    /**
     * Standardize appointment record fields.
     */
    protected function standardizeBooking(array $raw): array
    {
        $dateStr = $raw['preferred_date'] ?? date('Y-m-d');
        $timeStr = $raw['preferred_time'] ?? '10:00 AM';

        // Format dates into Alaska Timezone
        $formattedDate = $dateStr;
        try {
            $dt = new DateTime($dateStr, new DateTimeZone($this->timezone));
            $formattedDate = $dt->format('l, F j, Y');
        } catch (\Exception $e) {
            // Keep original string if parsing fails
        }

        $vehicle = trim(($raw['vehicle_year'] ?? '') . ' ' . ($raw['vehicle_make'] ?? '') . ' ' . ($raw['vehicle_model'] ?? ''));
        if (empty($vehicle)) {
            $vehicle = $raw['vehicle_info'] ?? 'Vehicle details on file';
        }

        return [
            'id' => $raw['id'] ?? null,
            'confirmation_id' => $raw['booking_number'] ?? $raw['booking_reference'] ?? 'NFA-UNKNOWN',
            'customer_name' => $raw['customer_name'] ?? 'Valued Customer',
            'customer_email' => $raw['customer_email'] ?? '',
            'customer_phone' => $raw['customer_phone'] ?? '',
            'service_name' => $raw['service_name'] ?? 'Automotive Service',
            'raw_date' => $dateStr,
            'formatted_date' => $formattedDate,
            'preferred_time' => $timeStr,
            'vehicle' => $vehicle,
            'status' => ucfirst(strtolower($raw['status'] ?? 'pending')),
            'notes' => $raw['notes'] ?? '',
            'location' => 'North Fork Auto, 25460 Talkeetna Spur Rd, Talkeetna, AK 99676',
            'timezone' => $this->timezone . ' (Alaska Time)',
            'created_at' => $raw['created_at'] ?? null,
        ];
    }

    /**
     * Return verified, sanitized appointment summary.
     */
    public function formatSummary(array $appointment, bool $isVerified = false): array
    {
        $base = [
            'confirmation_id' => $appointment['confirmation_id'],
            'status' => $appointment['status'],
            'location' => $appointment['location'],
            'timezone' => $appointment['timezone'],
        ];

        if (!$isVerified) {
            return array_merge($base, [
                'verified' => false,
                'message' => 'Appointment located. Customer verification required to view schedule and service details.'
            ]);
        }

        // Mask phone & email for privacy
        $maskedPhone = $this->maskPhone($appointment['customer_phone']);
        $maskedEmail = $this->maskEmail($appointment['customer_email']);

        return array_merge($base, [
            'verified' => true,
            'customer_name' => $appointment['customer_name'],
            'masked_phone' => $maskedPhone,
            'masked_email' => $maskedEmail,
            'service_name' => $appointment['service_name'],
            'date' => $appointment['formatted_date'],
            'raw_date' => $appointment['raw_date'],
            'time' => $appointment['preferred_time'],
            'vehicle' => $appointment['vehicle'],
            'notes' => $appointment['notes'] ?? '',
        ]);
    }

    /**
     * Mask phone number (e.g. (907) 555-7766 -> (907) ***-7766).
     */
    public function maskPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 7) {
            $last4 = substr($clean, -4);
            return '***-***-' . $last4;
        }
        return '***-****';
    }

    /**
     * Mask email (e.g. dave@test.com -> d***@test.com).
     */
    public function maskEmail(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            [$name, $domain] = explode('@', $email, 2);
            $maskedName = substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 1, 3));
            return $maskedName . '@' . $domain;
        }
        return '***@***.***';
    }

    /**
     * Update appointment status (Pending, Confirmed, Completed, Cancelled, Rescheduled).
     */
    public function updateAppointmentStatus(string $confirmationId, string $newStatus, string $note = ''): bool
    {
        $normalizedId = $this->normalizeId($confirmationId);

        if (File::exists($this->databaseFile)) {
            try {
                $content = File::get($this->databaseFile);
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
                $db = json_decode($content, true);
                if (isset($db['bookings']) && is_array($db['bookings'])) {
                    foreach ($db['bookings'] as &$booking) {
                        $bNumber = $this->normalizeId($booking['booking_number'] ?? $booking['booking_reference'] ?? '');
                        if ($bNumber === $normalizedId) {
                            $booking['status'] = strtolower($newStatus);
                            if (!empty($note)) {
                                $booking['internal_notes'] = trim(($booking['internal_notes'] ?? '') . "\n" . date('Y-m-d H:i') . ": " . $note);
                            }
                            $booking['updated_at'] = date('c');
                            File::put($this->databaseFile, json_encode($db, JSON_PRETTY_PRINT));
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to update appointment {$confirmationId}: " . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * Reschedule appointment with explicit confirmation.
     */
    public function rescheduleAppointment(string $confirmationId, string $newDate, string $newTime, string $note = ''): bool
    {
        $normalizedId = $this->normalizeId($confirmationId);

        if (File::exists($this->databaseFile)) {
            try {
                $content = File::get($this->databaseFile);
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
                $db = json_decode($content, true);
                if (isset($db['bookings']) && is_array($db['bookings'])) {
                    foreach ($db['bookings'] as &$booking) {
                        $bNumber = $this->normalizeId($booking['booking_number'] ?? $booking['booking_reference'] ?? '');
                        if ($bNumber === $normalizedId) {
                            $oldDate = $booking['preferred_date'] ?? '';
                            $oldTime = $booking['preferred_time'] ?? '';
                            $booking['preferred_date'] = $newDate;
                            $booking['preferred_time'] = $newTime;
                            $booking['status'] = 'rescheduled';
                            $auditNote = "Rescheduled from {$oldDate} {$oldTime} to {$newDate} {$newTime}. " . $note;
                            $booking['internal_notes'] = trim(($booking['internal_notes'] ?? '') . "\n" . date('Y-m-d H:i') . ": " . $auditNote);
                            $booking['updated_at'] = date('c');
                            File::put($this->databaseFile, json_encode($db, JSON_PRETTY_PRINT));
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to reschedule appointment {$confirmationId}: " . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * Cancel appointment with explicit customer confirmation.
     */
    public function cancelAppointment(string $confirmationId, string $reason = ''): bool
    {
        return $this->updateAppointmentStatus($confirmationId, 'cancelled', "Cancellation reason: {$reason}");
    }
}
