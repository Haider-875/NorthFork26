<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim($request->query('q', ''));

        $query = Booking::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('booking_number', 'LIKE', "%{$q}%")
                    ->orWhere('customer_name', 'LIKE', "%{$q}%")
                    ->orWhere('customer_phone', 'LIKE', "%{$q}%")
                    ->orWhere('customer_email', 'LIKE', "%{$q}%")
                    ->orWhere('vehicle_make', 'LIKE', "%{$q}%")
                    ->orWhere('vehicle_model', 'LIKE', "%{$q}%")
                    ->orWhere('service_name', 'LIKE', "%{$q}%");
            });
        }

        $counts = [
            'all' => Booking::count(),
            'Pending' => Booking::where('status', 'Pending')->count(),
            'Confirmed' => Booking::where('status', 'Confirmed')->count(),
            'Completed' => Booking::where('status', 'Completed')->count(),
            'Cancelled' => Booking::where('status', 'Cancelled')->count(),
        ];

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'counts', 'status', 'q'));
    }

    public function show($id)
    {
        $booking = Booking::findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Confirmed,Completed,Cancelled',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = $request->status;
        $booking->save();

        if (class_exists(BookingStatusHistory::class)) {
            try {
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'status' => $request->status,
                    'notes' => 'Status updated from ' . $oldStatus . ' to ' . $request->status . ' by admin.',
                ]);
            } catch (\Exception $e) {
                // Ignore if history table schema differs
            }
        }

        return back()->with('success', "Booking status updated to {$request->status}.");
    }

    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:3000',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->admin_notes = $request->admin_notes;
        $booking->save();

        return back()->with('success', 'Admin internal notes saved successfully.');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $ref = $booking->booking_number;
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', "Booking {$ref} deleted successfully.");
    }
}
