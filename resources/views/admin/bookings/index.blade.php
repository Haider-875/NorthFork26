@extends('layouts.admin')

@section('header_title', 'Booking Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h2 class="font-heading font-800 text-2xl">All Customer Bookings</h2>
        <div class="flex gap-2">
            <a href="/admin/bookings" class="px-3 py-1.5 text-xs font-bold border border-border bg-card hover:border-primary">All</a>
            <a href="/admin/bookings?status=Pending" class="px-3 py-1.5 text-xs font-bold border border-primary/40 bg-primary/10 text-primary">Pending</a>
            <a href="/admin/bookings?status=Confirmed" class="px-3 py-1.5 text-xs font-bold border border-emerald-500/40 bg-emerald-500/10 text-emerald-400">Confirmed</a>
            <a href="/admin/bookings?status=Completed" class="px-3 py-1.5 text-xs font-bold border border-sky-500/40 bg-sky-500/10 text-sky-400">Completed</a>
        </div>
    </div>

    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Ref #</th>
                    <th class="py-3 px-4">Customer</th>
                    <th class="py-3 px-4">Vehicle</th>
                    <th class="py-3 px-4">Service</th>
                    <th class="py-3 px-4">Appt Slot</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($bookings ?? [] as $b)
                    <tr class="hover:bg-secondary/40">
                        <td class="py-3 px-4 font-mono-data text-xs text-primary font-bold">{{ $b['booking_number'] }}</td>
                        <td class="py-3 px-4">
                            <div class="font-heading font-700">{{ $b['name'] }}</div>
                            <div class="text-xs text-muted-foreground"><a href="tel:{{ $b['phone'] }}" class="hover:underline">{{ $b['phone'] }}</a> · <a href="mailto:{{ $b['email'] }}" class="hover:underline">{{ $b['email'] }}</a></div>
                        </td>
                        <td class="py-3 px-4 text-xs">{{ $b['vehicle_year'] ?? '' }} {{ $b['vehicle_make'] ?? '' }} {{ $b['vehicle_model'] ?? '' }}</td>
                        <td class="py-3 px-4 text-xs font-semibold">{{ $b['service'] }}</td>
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
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="/admin/bookings/{{ $b['id'] }}" class="px-2.5 py-1 text-xs border border-border hover:border-primary text-foreground">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-muted-foreground">No bookings found in this view.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
