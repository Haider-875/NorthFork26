<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;

class CalendarProviderService
{
    protected string $timezone;
    protected ?string $googleCalendarId;
    protected ?string $googleCredentialsPath;

    public function __construct()
    {
        $this->timezone = env('APP_TIMEZONE', 'America/Anchorage');
        $this->googleCalendarId = env('GOOGLE_CALENDAR_ID', null);
        $this->googleCredentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS', null);
    }

    /**
     * Check if a third-party calendar provider (e.g. Google Calendar) is actively connected.
     */
    public function hasExternalCalendar(): bool
    {
        return !empty($this->googleCalendarId) && !empty($this->googleCredentialsPath) && file_exists($this->googleCredentialsPath);
    }

    /**
     * Return status of the active calendar/appointment source of truth.
     */
    public function getProviderStatus(): array
    {
        if ($this->hasExternalCalendar()) {
            return [
                'provider' => 'google_calendar',
                'connected' => true,
                'calendar_id' => $this->googleCalendarId,
                'timezone' => $this->timezone,
                'message' => 'Connected to Google Calendar API.'
            ];
        }

        return [
            'provider' => 'local_database',
            'connected' => true,
            'source_of_truth' => storage_path('app/database.json'),
            'model' => 'App\\Models\\Booking',
            'timezone' => $this->timezone . ' (America/Anchorage, Alaska Time)',
            'missing_credentials' => [
                'GOOGLE_CALENDAR_ID' => 'Optional Google Calendar ID for 2-way sync',
                'GOOGLE_APPLICATION_CREDENTIALS' => 'Optional path to Google Cloud service account JSON file'
            ],
            'message' => 'Primary appointment source of truth is the local North Fork Auto database (storage/app/database.json). No third-party calendar credentials configured.'
        ];
    }

    /**
     * Format appointment start and end times in Alaska timezone.
     */
    public function formatCalendarSlot(string $date, string $time, int $durationMinutes = 60): array
    {
        try {
            $tz = new DateTimeZone($this->timezone);
            $start = new DateTime("{$date} {$time}", $tz);
            $end = clone $start;
            $end->modify("+{$durationMinutes} minutes");

            return [
                'start_iso' => $start->format('c'),
                'end_iso' => $end->format('c'),
                'start_formatted' => $start->format('l, F j, Y \a\t g:i A T'),
                'timezone' => $this->timezone,
            ];
        } catch (\Exception $e) {
            return [
                'start_iso' => null,
                'end_iso' => null,
                'start_formatted' => "{$date} at {$time}",
                'timezone' => $this->timezone,
            ];
        }
    }
}
