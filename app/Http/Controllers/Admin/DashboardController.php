<?php

namespace App\Http\ControllersAdmin;
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'Pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'Confirmed')->count(),
            'completed_bookings' => Booking::where('status', 'Completed')->count(),
            'cancelled_bookings' => Booking::where('status', 'Cancelled')->count(),
            'total_services' => Service::count(),
        ];

        $recentBookings = Booking::latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
