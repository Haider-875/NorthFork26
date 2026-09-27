@extends('layouts.admin')

@section('header_title', 'Booking #' . $booking->booking_number)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="/admin/bookings" class="text-xs text-muted-foreground hover:text-primary flex items-center gap-1 font-semibold">
            ← Back to All Bookings
        </a>
        <div class="flex items-center gap-3">
            <span class="font-mono-data text-xs text-muted-foreground">Current Status:</span>
            <span class="px-3 py-1 text-xs font-bold uppercase border rounded-sm
                {{ $booking->status === 'Confirmed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400' : '' }}
                {{ $booking->status === 'Pending' ? 'border-primary/40 bg-primary/10 text-primary' : '' }}
                {{ $booking->status === 'Completed' ? 'border-sky-500/40 bg-sky-500/10 text-sky-400' : '' }}
                {{ $booking->status === 'Cancelled' ? 'border-border bg-muted text-muted-foreground' : '' }}">
                {{ $booking->status }}
            </span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Customer & Vehicle -->
        <div class="space-y-6">
            <div class="border border-border bg-card p-6 space-y-4">
                <h3 class="font-heading font-700 text-base border-b border-border pb-3 text-foreground flex items-center justify-between">
                    <span>Customer &amp; Contact</span>
                    <span class="font-mono-data text-xs text-primary font-bold">{{ $booking->booking_number }}</span>
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Full Name</span>
                        <strong class="text-foreground text-base">{{ $booking->customer_name }}</strong>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Phone Number</span>
                        <a href="tel:{{ $booking->customer_phone }}" class="text-primary hover:underline font-bold text-base">{{ $booking->customer_phone }}</a>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Email Address</span>
                        <a href="mailto:{{ $booking->customer_email }}" class="text-primary hover:underline">{{ $booking->customer_email }}</a>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Date Created</span>
                        <span class="font-mono-data text-xs text-muted-foreground">{{ $booking->created_at->format('M d, Y · h:i A') }}</span>
                    </div>
                </div>
            </div>

            <div class="border border-border bg-card p-6 space-y-4">
                <h3 class="font-heading font-700 text-base border-b border-border pb-3 text-foreground">
                    Vehicle Details
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Year / Make / Model</span>
                        <strong class="text-foreground text-base">{{ $booking->vehicle_year ?? 'N/A' }} {{ $booking->vehicle_make }} {{ $booking->vehicle_model }}</strong>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Mileage</span>
                        <span class="text-foreground">{{ $booking->vehicle_mileage ?: 'Not specified' }}</span>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">VIN</span>
                        <span class="font-mono-data text-xs text-foreground">{{ $booking->vehicle_vin ?: 'Not provided' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service & Status Operations -->
        <div class="space-y-6">
            <div class="border border-border bg-card p-6 space-y-4">
                <h3 class="font-heading font-700 text-base border-b border-border pb-3 text-foreground">
                    Appointment Details
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Requested Service</span>
                        <strong class="text-primary text-base">{{ $booking->service_name }}</strong>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Preferred Date</span>
                            <span class="font-mono-data text-foreground font-semibold">{{ $booking->preferred_date ? $booking->preferred_date->format('l, M d, Y') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-0.5">Preferred Slot</span>
                            <span class="font-mono-data text-foreground font-semibold">{{ $booking->preferred_time }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs uppercase font-mono-data text-muted-foreground block mb-1">Customer Special Requests / Symptoms</span>
                        <div class="p-3 bg-background border border-border text-xs text-foreground/90 leading-relaxed min-h-[60px]">
                            {{ $booking->notes ?: 'No customer notes provided.' }}
                        </div>
                    </div>
                </div>

                <!-- Update Status Form -->
                <div class="pt-4 border-t border-border">
                    <form action="/admin/bookings/{{ $booking->id }}/status" method="POST" class="space-y-3">
                        @csrf
                        <label class="block font-heading font-700 text-xs uppercase text-foreground">Update Booking Status</label>
                        <div class="flex gap-2">
                            <select name="status" class="bg-background border border-border px-3 py-2 text-sm flex-1 text-foreground focus:outline-none focus:border-primary">
                                <option value="Pending" {{ $booking->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Confirmed" {{ $booking->status === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="Completed" {{ $booking->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Cancelled" {{ $booking->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit" class="bg-primary text-white font-bold px-5 py-2 text-xs uppercase hover:bg-primary/90 transition-colors">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Internal Staff Notes Form -->
            <div class="border border-border bg-card p-6 space-y-4">
                <h3 class="font-heading font-700 text-base border-b border-border pb-3 text-foreground">
                    Staff Internal Notes
                </h3>
                <form action="/admin/bookings/{{ $booking->id }}/notes" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="admin_notes" rows="3" placeholder="Add shop notes (parts ordered, bay assigned, inspection result)..." class="w-full bg-background border border-border px-3 py-2 text-xs text-foreground focus:outline-none focus:border-primary">{{ $booking->admin_notes }}</textarea>
                    <button type="submit" class="bg-secondary text-foreground hover:bg-primary hover:text-white border border-border px-4 py-2 text-xs font-bold uppercase transition-colors">
                        Save Internal Notes
                    </button>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="pt-2 flex justify-end">
                <form action="/admin/bookings/{{ $booking->id }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this appointment record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-primary hover:underline flex items-center gap-1 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Booking Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
