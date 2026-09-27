@extends('layouts.admin')

@section('header_title', 'Dashboard Overview')

@section('content')
<div class="space-y-8">
    <!-- Top Stats Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="/admin/bookings" class="border border-border bg-card p-6 hover:border-primary transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono-data text-[11px] text-muted-foreground group-hover:text-primary">Total Volume</span>
                <svg class="w-4 h-4 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="font-heading font-800 text-3xl text-foreground">{{ $stats['total_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">All-time customer appointments</div>
        </a>

        <a href="/admin/bookings?status=Pending" class="border border-primary/40 bg-primary/5 p-6 hover:border-primary transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono-data text-[11px] text-primary">Pending Review</span>
                <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
            </div>
            <div class="font-heading font-800 text-3xl text-primary">{{ $stats['pending_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Awaiting staff confirmation</div>
        </a>

        <a href="/admin/bookings?status=Confirmed" class="border border-emerald-500/40 bg-emerald-500/5 p-6 hover:border-emerald-500 transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono-data text-[11px] text-emerald-400">Confirmed Slots</span>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="font-heading font-800 text-3xl text-emerald-400">{{ $stats['confirmed_bookings'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Scheduled on service calendar</div>
        </a>

        <a href="/admin/services" class="border border-border bg-card p-6 hover:border-primary transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono-data text-[11px] text-muted-foreground group-hover:text-primary">Catalog Services</span>
                <svg class="w-4 h-4 text-muted-foreground group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
            </div>
            <div class="font-heading font-800 text-3xl text-foreground">{{ $stats['total_services'] ?? 0 }}</div>
            <div class="text-xs text-muted-foreground mt-2">Active repair offerings</div>
        </a>
    </div>

    <!-- Recent Bookings Table -->
    <div class="border border-border bg-card p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-heading font-700 text-lg">Recent Booking Requests</h3>
                <p class="text-xs text-muted-foreground mt-1">Latest customer appointments scheduled via website.</p>
            </div>
            <a href="/admin/bookings" class="font-heading font-600 text-xs text-primary hover:underline flex items-center gap-1">
                View All Bookings →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-xs uppercase font-mono-data text-muted-foreground bg-secondary/30">
                        <th class="py-3 px-4">Ref #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Vehicle</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4">Slot</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($recentBookings ?? [] as $b)
                        <tr class="hover:bg-secondary/40 transition-colors">
                            <td class="py-3 px-4 font-mono-data text-xs text-primary font-bold">
                                {{ $b->booking_number }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-heading font-600 text-foreground">{{ $b->customer_name }}</div>
                                <div class="text-xs text-muted-foreground"><a href="tel:{{ $b->customer_phone }}" class="hover:underline">{{ $b->customer_phone }}</a></div>
                            </td>
                            <td class="py-3 px-4 text-xs text-muted-foreground">
                                {{ $b->vehicle_year }} {{ $b->vehicle_make }} {{ $b->vehicle_model }}
                            </td>
                            <td class="py-3 px-4 text-xs font-semibold text-foreground">
                                {{ $b->service_name }}
                            </td>
                            <td class="py-3 px-4 font-mono-data text-xs text-muted-foreground">
                                {{ $b->preferred_date ? $b->preferred_date->format('M d, Y') : '-' }} · {{ $b->preferred_time }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold border rounded-sm
                                    {{ $b->status === 'Confirmed' ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400' : '' }}
                                    {{ $b->status === 'Pending' ? 'border-primary/40 bg-primary/10 text-primary' : '' }}
                                    {{ $b->status === 'Completed' ? 'border-sky-500/40 bg-sky-500/10 text-sky-400' : '' }}
                                    {{ $b->status === 'Cancelled' ? 'border-border bg-muted text-muted-foreground' : '' }}">
                                    {{ $b->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="/admin/bookings/{{ $b->id }}" class="px-3 py-1 text-xs font-bold border border-border hover:border-primary text-foreground hover:text-primary transition-colors">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-muted-foreground">No recent bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Messages / Inquiries -->
    @if(isset($recentMessages) && count($recentMessages) > 0)
        <div class="border border-border bg-card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-heading font-700 text-lg">Recent Customer Inquiries</h3>
                    <p class="text-xs text-muted-foreground mt-1">Direct questions submitted through the contact page.</p>
                </div>
                <a href="/admin/messages" class="font-heading font-600 text-xs text-primary hover:underline">
                    View All Inquiries →
                </a>
            </div>

            <div class="space-y-3">
                @foreach($recentMessages as $msg)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-border bg-secondary/20 hover:bg-secondary/40 transition-colors gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-heading font-700 text-sm text-foreground">{{ $msg->name }}</span>
                                @if(!$msg->is_read)
                                    <span class="px-2 py-0.2 text-[9px] font-bold uppercase bg-primary text-primary-foreground rounded-full">New</span>
                                @endif
                                <span class="text-xs text-muted-foreground">· {{ $msg->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">{{ $msg->email }} · {{ $msg->phone ?? 'No phone' }}</div>
                            <p class="text-xs text-foreground/80 line-clamp-1 mt-1">{{ Str::limit($msg->message, 120) }}</p>
                        </div>
                        <a href="/admin/messages/{{ $msg->id }}" class="px-3 py-1.5 text-xs font-bold border border-border hover:border-primary text-foreground text-center shrink-0">
                            Read Inquiry
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
