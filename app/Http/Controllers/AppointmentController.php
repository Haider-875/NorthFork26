<?php

namespace App\Http\Controllers;

use App\Services\AppointmentLookupService;
use App\Services\AppointmentVerificationService;
use App\Services\CalendarProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppointmentController extends Controller
{
    protected AppointmentLookupService $lookupService;
    protected AppointmentVerificationService $verificationService;
    protected CalendarProviderService $calendarService;

    public function __construct(
        AppointmentLookupService $lookupService,
        AppointmentVerificationService $verificationService,
        CalendarProviderService $calendarService
    ) {
        $this->lookupService = $lookupService;
        $this->verificationService = $verificationService;
        $this->calendarService = $calendarService;
    }

    /**
     * Look up appointment by confirmation ID, with optional verification detail.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'confirmation_id' => 'required|string|max:50',
            'verification_detail' => 'nullable|string|max:100',
            'session_id' => 'nullable|string|max:64',
        ]);

        $ip = $request->ip() ?? '127.0.0.1';
        $sessionId = $validated['session_id'] ?? 'sess_' . md5($ip);

        // Rate limit lookups: 30 requests per minute per IP
        $lookupKey = 'appt_lookup:' . $ip;
        if (RateLimiter::tooManyAttempts($lookupKey, 30)) {
            return response()->json([
                'success' => false,
                'error' => 'rate_limit',
                'message' => 'Too many lookup requests. Please wait a moment and try again.'
            ], 429);
        }
        RateLimiter::hit($lookupKey, 60);

        $confirmationId = $this->lookupService->normalizeId($validated['confirmation_id']);
        $appointment = $this->lookupService->findAppointment($confirmationId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again."
            ], 404);
        }

        $verificationDetail = trim($validated['verification_detail'] ?? '');
        $isVerified = false;

        if (!empty($verificationDetail)) {
            $verifyResult = $this->verificationService->verifyCustomer($appointment, $verificationDetail, $sessionId, $ip);
            if ($verifyResult['locked_out']) {
                return response()->json([
                    'success' => false,
                    'error' => 'locked_out',
                    'message' => $verifyResult['message']
                ], 429);
            }

            if ($verifyResult['verified']) {
                $isVerified = true;
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'verification_failed',
                    'attempts_left' => $verifyResult['attempts_left'] ?? 0,
                    'message' => $verifyResult['message']
                ], 403);
            }
        }

        $summary = $this->lookupService->formatSummary($appointment, $isVerified);

        if ($isVerified) {
            $message = "Your appointment is confirmed for {$summary['date']} at {$summary['time']}. You're booked for {$summary['service_name']} (Status: {$summary['status']}). Your confirmation number is {$summary['confirmation_id']}.";
        } else {
            $message = "I found your appointment ({$summary['confirmation_id']}). For your privacy, please verify the email address or phone number associated with your booking before I share its details.";
        }

        return response()->json([
            'success' => true,
            'verified' => $isVerified,
            'appointment' => $summary,
            'reply' => $message
        ]);
    }

    /**
     * Verify customer identity for a located appointment.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'confirmation_id' => 'required|string|max:50',
            'verification_detail' => 'required|string|max:100',
            'session_id' => 'nullable|string|max:64',
        ]);

        $ip = $request->ip() ?? '127.0.0.1';
        $sessionId = $validated['session_id'] ?? 'sess_' . md5($ip);

        $confirmationId = $this->lookupService->normalizeId($validated['confirmation_id']);
        $appointment = $this->lookupService->findAppointment($confirmationId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => "I couldn't find an appointment with that confirmation number."
            ], 404);
        }

        $verifyResult = $this->verificationService->verifyCustomer($appointment, $validated['verification_detail'], $sessionId, $ip);

        if ($verifyResult['locked_out']) {
            return response()->json([
                'success' => false,
                'error' => 'locked_out',
                'message' => $verifyResult['message']
            ], 429);
        }

        if (!$verifyResult['verified']) {
            return response()->json([
                'success' => false,
                'error' => 'verification_failed',
                'attempts_left' => $verifyResult['attempts_left'] ?? 0,
                'message' => $verifyResult['message']
            ], 403);
        }

        $summary = $this->lookupService->formatSummary($appointment, true);
        $message = "Your appointment is confirmed for {$summary['date']} at {$summary['time']}. You're booked for {$summary['service_name']} (Status: {$summary['status']}). Your confirmation number is {$summary['confirmation_id']}.";

        return response()->json([
            'success' => true,
            'verified' => true,
            'appointment' => $summary,
            'reply' => $message
        ]);
    }

    /**
     * Return active booking provider / calendar status.
     */
    public function providerStatus()
    {
        return response()->json([
            'success' => true,
            'status' => $this->calendarService->getProviderStatus()
        ]);
    }

    /**
     * Reschedule appointment (requires customer verification and confirmation).
     */
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'confirmation_id' => 'required|string|max:50',
            'verification_detail' => 'required|string|max:100',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string|max:30',
            'confirmed' => 'required|boolean',
            'note' => 'nullable|string|max:500',
            'session_id' => 'nullable|string|max:64',
        ]);

        if (!$validated['confirmed']) {
            return response()->json([
                'success' => false,
                'error' => 'confirmation_required',
                'message' => 'Explicit confirmation is required before rescheduling.'
            ], 400);
        }

        $ip = $request->ip() ?? '127.0.0.1';
        $sessionId = $validated['session_id'] ?? 'sess_' . md5($ip);

        $confirmationId = $this->lookupService->normalizeId($validated['confirmation_id']);
        $appointment = $this->lookupService->findAppointment($confirmationId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => "I couldn't find an appointment with that confirmation number."
            ], 404);
        }

        $verifyResult = $this->verificationService->verifyCustomer($appointment, $validated['verification_detail'], $sessionId, $ip);
        if (!$verifyResult['verified']) {
            return response()->json([
                'success' => false,
                'error' => 'verification_failed',
                'message' => 'Verification failed. Cannot reschedule unverified appointment.'
            ], 403);
        }

        $rescheduled = $this->lookupService->rescheduleAppointment(
            $confirmationId,
            $validated['preferred_date'],
            $validated['preferred_time'],
            $validated['note'] ?? 'Requested by customer via Virtual Assistant'
        );

        if ($rescheduled) {
            return response()->json([
                'success' => true,
                'message' => "Your appointment ({$confirmationId}) has been rescheduled to {$validated['preferred_date']} at {$validated['preferred_time']}."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to reschedule appointment at this time. Please call (907) 733-3030.'
        ], 500);
    }

    /**
     * Cancel appointment (requires customer verification and confirmation).
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'confirmation_id' => 'required|string|max:50',
            'verification_detail' => 'required|string|max:100',
            'confirmed' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
            'session_id' => 'nullable|string|max:64',
        ]);

        if (!$validated['confirmed']) {
            return response()->json([
                'success' => false,
                'error' => 'confirmation_required',
                'message' => 'Explicit confirmation is required before cancelling.'
            ], 400);
        }

        $ip = $request->ip() ?? '127.0.0.1';
        $sessionId = $validated['session_id'] ?? 'sess_' . md5($ip);

        $confirmationId = $this->lookupService->normalizeId($validated['confirmation_id']);
        $appointment = $this->lookupService->findAppointment($confirmationId);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'error' => 'not_found',
                'message' => "I couldn't find an appointment with that confirmation number."
            ], 404);
        }

        $verifyResult = $this->verificationService->verifyCustomer($appointment, $validated['verification_detail'], $sessionId, $ip);
        if (!$verifyResult['verified']) {
            return response()->json([
                'success' => false,
                'error' => 'verification_failed',
                'message' => 'Verification failed. Cannot cancel unverified appointment.'
            ], 403);
        }

        $cancelled = $this->lookupService->cancelAppointment($confirmationId, $validated['reason'] ?? 'Cancelled by customer via Virtual Assistant');

        if ($cancelled) {
            return response()->json([
                'success' => true,
                'message' => "Your appointment ({$confirmationId}) has been cancelled."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to cancel appointment at this time. Please call (907) 733-3030.'
        ], 500);
    }
}
