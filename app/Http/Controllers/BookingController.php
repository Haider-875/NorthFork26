<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        return view('pages.book', compact('services'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'service' => 'required|string|max:255',
            'vehicle_year' => 'required|integer|min:1950|max:' . (date('Y') + 1),
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_mileage' => 'nullable|string|max:50',
            'vehicle_vin' => 'nullable|string|max:50',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string|max:20',
            'notes' => 'nullable|string|max:2000',
        ]);

        $bookingNumber = 'NFA-' . date('Y') . '-' . strtoupper(Str::random(5));

        $booking = Booking::create([
            'booking_number' => $bookingNumber,
            'customer_name' => $validated['name'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
            'service_name' => $validated['service'],
            'vehicle_year' => $validated['vehicle_year'],
            'vehicle_make' => $validated['vehicle_make'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_mileage' => $validated['vehicle_mileage'] ?? null,
            'vehicle_vin' => $validated['vehicle_vin'] ?? null,
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'Pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully.',
                'booking' => $booking
            ]);
        }

        return back()->with('success', 'Appointment booked successfully. Reference: ' . $bookingNumber);
    }
}
