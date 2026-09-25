@extends('layouts.admin')

@section('header_title', 'Dashboard Overview')

@section('content')
<div class="space-y-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="border border-border bg-card p-6">
            <div class="font-mono-data text-[11px] mb-1">Total Volume</div>
            <div class="font-heading font-800 text-3xl text-foreground">{{ $stats['total_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">All-time customer appointments</div>
        </div>

        <div class="border border-border bg-card p-6">
            <div class="font-mono-data text-[11px] mb-1 text-primary">Pending Review</div>
            <div class="font-heading font-800 text-3xl text-primary">{{ $stats['pending_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Awaiting staff confirmation</div>
        </div>

        <div class="border border-border bg-card p-6">
            <div class="font-mono-data text-[11px] mb-1 text-emerald-400">Confirmed</div>
            <div class="font-heading font-800 text-3xl text-emerald-400">{{ $stats['confirmed_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Scheduled on service calendar</div>
        </div>

        <div class="border border-border bg-card p-6">
            <div class="font-mono-data text-[11px] mb-1 text-sky-400">Completed</div>
            <div class="font-heading font-800 text-3xl text-sky-400">{{ $stats['completed_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Finished vehicle repairs</div>
        </div>
    </div>

    <div class="border border-border bg-card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-heading font-700 text-lg">Recent Booking Requests</h3>
            <a href="/admin/bookings" class="font-heading font-600 text-xs text-primary hover:underline">View All Bookings →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground">
                        <th class="py-3 px-4">Ref #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4">Date / Time</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($recentBookings ?? [] as $b)
                        <tr class="hover:bg-secondary/40">
                            <td class="py-3 px-4 font-mono-data text-xs text-primary">{{ $b['booking_number'] }}</td>
                            <td class="py-3 px-4">
                                <div class="font-heading font-600">{{ $b['name'] }}</div>
                                <div class="text-xs text-muted-foreground">{{ $b['phone'] }}</div>
                            </td>
                            <td class="py-3 px-4">{{ $b['service'] }}</td>
                            <td class="py-3 px-4 font-mono-data text-xs">{{ $b['preferred_date'] }} {{ $b['preferred_time'] }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold border 
                                    {{ $b['status'] === 'Confirmed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400' : '' }}
                                    {{ $b['status'] === 'Pending' ? 'border-primary/40 bg-primary/10 text-primary' : '' }}
                                    {{ $b['status'] === 'Completed' ? 'border-sky-500/40 bg-sky-500/10 text-sky-400' : '' }}
                                    {{ $b['status'] === 'Cancelled' ? 'border-border bg-muted text-muted-foreground' : '' }}">
                                    {{ $b['status'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="/admin/bookings/{{ $b['id'] }}" class="text-xs font-bold text-primary hover:underline">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-muted-foreground">No recent bookings recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
