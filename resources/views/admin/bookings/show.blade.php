@extends('layouts.admin')

@section('header_title', 'Booking #' . ($booking['booking_number'] ?? ''))

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="/admin/bookings" class="text-xs text-muted-foreground hover:text-primary">← Back to All Bookings</a>
        <div class="flex items-center gap-3">
            <span class="font-mono-data text-xs">Status:</span>
            <span class="px-3 py-1 text-xs font-bold uppercase border 
                {{ $booking['status'] === 'Confirmed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400' : '' }}
                {{ $booking['status'] === 'Pending' ? 'border-primary/40 bg-primary/10 text-primary' : '' }}
                {{ $booking['status'] === 'Completed' ? 'border-sky-500/40 bg-sky-500/10 text-sky-400' : '' }}
                {{ $booking['status'] === 'Cancelled' ? 'border-border bg-muted text-muted-foreground' : '' }}">
                {{ $booking['status'] }}
            </span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="border border-border bg-card p-6 space-y-4">
            <h3 class="font-heading font-700 text-base border-b border-border pb-3">Customer &amp; Contact</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-muted-foreground">Full Name:</span> <strong class="text-foreground">{{ $booking['name'] }}</strong></div>
                <div><span class="text-muted-foreground">Phone:</span> <a href="tel:{{ $booking['phone'] }}" class="text-primary hover:underline font-bold">{{ $booking['phone'] }}</a></div>
                <div><span class="text-muted-foreground">Email:</span> <a href="mailto:{{ $booking['email'] }}" class="text-primary hover:underline">{{ $booking['email'] }}</a></div>
            </div>

            <h3 class="font-heading font-700 text-base border-b border-border pb-3 pt-4">Vehicle Details</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-muted-foreground">Year / Make / Model:</span> <strong class="text-foreground">{{ $booking['vehicle_year'] ?? 'N/A' }} {{ $booking['vehicle_make'] ?? '' }} {{ $booking['vehicle_model'] ?? '' }}</strong></div>
                <div><span class="text-muted-foreground">Mileage:</span> {{ $booking['vehicle_mileage'] ?? 'Not specified' }}</div>
                <div><span class="text-muted-foreground">VIN:</span> <span class="font-mono-data text-xs">{{ $booking['vehicle_vin'] ?? 'Not provided' }}</span></div>
            </div>
        </div>

        <div class="border border-border bg-card p-6 space-y-4">
            <h3 class="font-heading font-700 text-base border-b border-border pb-3">Requested Service</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-muted-foreground">Service Name:</span> <strong class="text-primary text-base">{{ $booking['service'] }}</strong></div>
                <div><span class="text-muted-foreground">Preferred Date:</span> <strong class="font-mono-data text-foreground">{{ $booking['preferred_date'] }}</strong></div>
                <div><span class="text-muted-foreground">Preferred Time:</span> <strong class="font-mono-data text-foreground">{{ $booking['preferred_time'] }}</strong></div>
                <div><span class="text-muted-foreground">Customer Notes:</span> <p class="mt-1 p-3 bg-background border border-border text-xs text-muted-foreground">{{ $booking['notes'] ?? 'No extra notes provided.' }}</p></div>
            </div>

            <div class="pt-4 border-t border-border">
                <form action="/admin/bookings/{{ $booking['id'] }}/status" method="POST" class="space-y-3">
                    @csrf
                    <label class="block font-heading font-700 text-xs uppercase">Update Booking Status</label>
                    <div class="flex gap-2">
                        <select name="status" class="bg-background border border-border px-3 py-2 text-sm flex-1">
                            <option value="Pending" {{ $booking['status'] === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Confirmed" {{ $booking['status'] === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="Completed" {{ $booking['status'] === 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Cancelled" {{ $booking['status'] === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="bg-primary text-white font-bold px-4 py-2 text-xs uppercase">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
