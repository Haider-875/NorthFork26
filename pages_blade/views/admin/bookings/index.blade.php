@extends('layouts.admin')

@section('header_title', 'Booking Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-heading font-800 text-2xl text-foreground">Customer Appointments</h2>
            <p class="text-xs text-muted-foreground mt-1">Review, confirm, and update workshop bookings.</p>
        </div>
        
        <!-- Search bar -->
        <form action="/admin/bookings" method="GET" class="flex gap-2 w-full sm:w-auto">
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search ref, customer, car..." class="bg-background border border-border px-3 py-2 text-xs text-foreground focus:outline-none focus:border-primary w-full sm:w-64">
            <button type="submit" class="bg-secondary text-foreground hover:bg-primary hover:text-white px-3 py-2 text-xs font-bold border border-border transition-colors">
                Search
            </button>
            @if(!empty($q))
                <a href="/admin/bookings{{ !empty($status) ? '?status=' . $status : '' }}" class="px-2 py-2 text-xs text-muted-foreground hover:text-foreground">Clear</a>
            @endif
        </form>
    </div>

    <!-- Filter Status Tabs -->
    <div class="flex flex-wrap gap-2 pt-2 border-b border-border pb-4">
        <a href="/admin/bookings{{ !empty($q) ? '?q=' . urlencode($q) : '' }}" class="px-3 py-1.5 text-xs font-bold border transition-colors {{ empty($status) ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-muted-foreground hover:border-primary hover:text-foreground' }}">
            All ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="/admin/bookings?status=Pending{{ !empty($q) ? '&q=' . urlencode($q) : '' }}" class="px-3 py-1.5 text-xs font-bold border transition-colors {{ ($status ?? '') === 'Pending' ? 'border-primary bg-primary text-primary-foreground' : 'border-primary/40 bg-primary/10 text-primary hover:bg-primary/20' }}">
            Pending ({{ $counts['Pending'] ?? 0 }})
        </a>
        <a href="/admin/bookings?status=Confirmed{{ !empty($q) ? '&q=' . urlencode($q) : '' }}" class="px-3 py-1.5 text-xs font-bold border transition-colors {{ ($status ?? '') === 'Confirmed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }}">
            Confirmed ({{ $counts['Confirmed'] ?? 0 }})
        </a>
        <a href="/admin/bookings?status=Completed{{ !empty($q) ? '&q=' . urlencode($q) : '' }}" class="px-3 py-1.5 text-xs font-bold border transition-colors {{ ($status ?? '') === 'Completed' ? 'border-sky-500 bg-sky-500 text-white' : 'border-sky-500/40 bg-sky-500/10 text-sky-400 hover:bg-sky-500/20' }}">
            Completed ({{ $counts['Completed'] ?? 0 }})
        </a>
        <a href="/admin/bookings?status=Cancelled{{ !empty($q) ? '&q=' . urlencode($q) : '' }}" class="px-3 py-1.5 text-xs font-bold border transition-colors {{ ($status ?? '') === 'Cancelled' ? 'border-zinc-500 bg-zinc-600 text-white' : 'border-border bg-muted/30 text-muted-foreground hover:bg-secondary' }}">
            Cancelled ({{ $counts['Cancelled'] ?? 0 }})
        </a>
    </div>

    <!-- Bookings Table -->
    <div class="border border-border bg-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                    <th class="py-3 px-4">Ref #</th>
                    <th class="py-3 px-4">Customer Details</th>
                    <th class="py-3 px-4">Vehicle</th>
                    <th class="py-3 px-4">Requested Service</th>
                    <th class="py-3 px-4">Appointment Slot</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($bookings as $b)
                    <tr class="hover:bg-secondary/40 transition-colors">
                        <td class="py-3.5 px-4 font-mono-data text-xs text-primary font-bold whitespace-nowrap">
                            <a href="/admin/bookings/{{ $b->id }}" class="hover:underline">{{ $b->booking_number }}</a>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-heading font-700 text-foreground">{{ $b->customer_name }}</div>
                            <div class="text-xs text-muted-foreground flex items-center gap-2 mt-0.5">
                                <a href="tel:{{ $b->customer_phone }}" class="hover:underline text-foreground/80">{{ $b->customer_phone }}</a>
                                <span>·</span>
                                <a href="mailto:{{ $b->customer_email }}" class="hover:underline">{{ $b->customer_email }}</a>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-muted-foreground whitespace-nowrap">
                            <span class="font-semibold text-foreground">{{ $b->vehicle_year }} {{ $b->vehicle_make }}</span><br>
                            <span>{{ $b->vehicle_model }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-foreground">
                            {{ $b->service_name }}
                        </td>
                        <td class="py-3.5 px-4 font-mono-data text-xs whitespace-nowrap text-muted-foreground">
                            <span class="text-foreground">{{ $b->preferred_date ? $b->preferred_date->format('M d, Y') : '-' }}</span><br>
                            <span>{{ $b->preferred_time }}</span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-[10px] uppercase font-bold border rounded-sm
                                {{ $b->status === 'Confirmed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ $b->status === 'Pending' ? 'border-primary/40 bg-primary/10 text-primary' : '' }}
                                {{ $b->status === 'Completed' ? 'border-sky-500/40 bg-sky-500/10 text-sky-400' : '' }}
                                {{ $b->status === 'Cancelled' ? 'border-border bg-muted text-muted-foreground' : '' }}">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/bookings/{{ $b->id }}" class="px-3 py-1.5 text-xs font-bold border border-border hover:border-primary text-foreground hover:text-primary transition-colors inline-block">
                                View &amp; Manage
                            </a>
                            <form action="/admin/bookings/{{ $b->id }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this booking?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 text-xs text-primary hover:text-white hover:bg-primary/20 transition-colors" title="Delete Booking">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-muted-foreground">
                            No appointments found matching the current filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($bookings, 'hasPages') && $bookings->hasPages())
        <div class="pt-4 flex justify-between items-center text-xs text-muted-foreground">
            <div>
                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} bookings
            </div>
            <div>
                {{ $bookings->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
