@extends('layouts.admin')

@section('header_title', 'Operations Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#BDEBFA] gap-4">
        <div>
            <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— TALKEETNA OPERATIONS CONSOLE —</span>
            <h1 class="text-2xl sm:text-3xl font-heading font-extrabold text-[#071F35] tracking-tight mt-0.5">Operations Dashboard</h1>
            <p class="text-xs text-[#334E68] mt-1 font-mono">Real-time shop metrics, bookings &amp; workflow</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/admin/bookings" class="btn-navy-pill text-xs px-5 py-2.5 shadow-md">
                Manage Bookings
            </a>
            <a href="/admin/services" class="btn-white-pill text-xs px-5 py-2.5 shadow-sm">
                Services Catalog
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Volume -->
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-mono font-bold text-[#334E68] uppercase tracking-wider">Total Volume</span>
                <div class="w-9 h-9 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-[#071F35]">{{ $stats['total_bookings'] ?? 0 }}</div>
            <div class="text-xs text-[#087DB5] font-medium mt-2">All-time volume</div>
        </div>

        <!-- Pending Review -->
        <div class="frosted-card p-6 flex flex-col justify-between border-amber-300/80 bg-amber-50/40">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-mono font-bold text-amber-800 uppercase tracking-wider">Pending Review</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-amber-600">{{ $stats['pending_bookings'] ?? 0 }}</div>
            <div class="text-xs text-amber-800 font-medium mt-2">Requires confirmation</div>
        </div>

        <!-- Confirmed -->
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-mono font-bold text-[#087DB5] uppercase tracking-wider">Confirmed</span>
                <div class="w-9 h-9 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-[#087DB5]">{{ $stats['confirmed_bookings'] ?? 0 }}</div>
            <div class="text-xs text-[#334E68] font-medium mt-2">Scheduled on service calendar</div>
        </div>

        <!-- Completed -->
        <div class="frosted-card p-6 flex flex-col justify-between border-emerald-300/80 bg-emerald-50/40">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-800 uppercase tracking-wider">Completed</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div class="text-3xl font-heading font-extrabold text-emerald-600">{{ $stats['completed_bookings'] ?? 0 }}</div>
            <div class="text-xs text-emerald-800 font-medium mt-2">Finished vehicle repairs</div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="frosted-card overflow-hidden">
        <div class="p-6 border-b border-[#BDEBFA] flex items-center justify-between bg-white/60">
            <div>
                <h2 class="text-lg font-heading font-bold text-[#071F35]">Recent Booking Requests</h2>
                <p class="text-xs text-[#334E68] mt-0.5">Latest service appointments submitted online</p>
            </div>
            <a href="/admin/bookings" class="btn-white-pill text-xs px-5 py-2">View Full Ledger</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA] font-mono text-[#071F35] bg-[#D8F1FA]/60">
                    <tr>
                        <th class="p-4 font-bold">Ref #</th>
                        <th class="p-4 font-bold">Customer</th>
                        <th class="p-4 font-bold">Service</th>
                        <th class="p-4 font-bold">Date / Time</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 text-right font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/40 bg-white/70">
                    @forelse($recentBookings ?? [] as $b)
                        <tr class="hover:bg-[#EBF7FD]/80 transition-colors">
                            <td class="p-4 font-mono font-bold text-[#087DB5]">{{ $b['booking_number'] ?? $b['booking_reference'] ?? 'NFA' }}</td>
                            <td class="p-4">
                                <div class="font-bold text-[#071F35] text-sm">{{ $b['name'] ?? $b['customer_name'] }}</div>
                                <div class="text-xs text-[#334E68] font-mono">{{ $b['phone'] ?? $b['customer_phone'] }}</div>
                            </td>
                            <td class="p-4 font-semibold text-[#071F35]">{{ $b['service'] ?? $b['service_name'] }}</td>
                            <td class="p-4 font-mono text-[#334E68]">{{ $b['preferred_date'] }} <span class="text-[#334E68]/60">{{ $b['preferred_time'] }}</span></td>
                            <td class="p-4">
                                @php
                                    $st = strtolower($b['status'] ?? 'pending');
                                @endphp
                                <span class="status-badge status-{{ $st }}">{{ $b['status'] ?? 'Pending' }}</span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="/admin/bookings/{{ $b['id'] ?? '' }}" class="btn-navy-pill text-[11px] px-3.5 py-1.5 shadow-sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-[#334E68] font-mono">No recent bookings recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection