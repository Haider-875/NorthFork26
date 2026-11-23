@extends('layouts.admin')

@section('header_title', 'Booking Management')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-[#BDEBFA] gap-4">
        <div>
            <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— APPOINTMENTS LEDGER —</span>
            <h2 class="text-2xl sm:text-3xl font-heading font-extrabold text-[#071F35] tracking-tight mt-0.5">All Customer Bookings</h2>
            <p class="text-xs text-[#334E68] mt-1 font-mono">Review and manage vehicle service bookings</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            @php $currentStatus = request('status', 'all'); @endphp
            <a href="/admin/bookings" class="px-3.5 py-1.5 rounded-full border transition-all {{ $currentStatus === 'all' ? 'bg-[#071F35] text-white border-[#071F35] font-bold shadow-sm' : 'bg-white/80 text-[#334E68] border-[#BDEBFA] hover:border-[#087DB5]' }}">All</a>
            <a href="/admin/bookings?status=Pending" class="px-3.5 py-1.5 rounded-full border transition-all {{ strtolower($currentStatus) === 'pending' ? 'bg-amber-500 text-white border-amber-600 font-bold shadow-sm' : 'bg-white/80 text-[#334E68] border-[#BDEBFA] hover:border-[#087DB5]' }}">Pending</a>
            <a href="/admin/bookings?status=Confirmed" class="px-3.5 py-1.5 rounded-full border transition-all {{ strtolower($currentStatus) === 'confirmed' ? 'bg-[#087DB5] text-white border-[#087DB5] font-bold shadow-sm' : 'bg-white/80 text-[#334E68] border-[#BDEBFA] hover:border-[#087DB5]' }}">Confirmed</a>
            <a href="/admin/bookings?status=Completed" class="px-3.5 py-1.5 rounded-full border transition-all {{ strtolower($currentStatus) === 'completed' ? 'bg-emerald-600 text-white border-emerald-700 font-bold shadow-sm' : 'bg-white/80 text-[#334E68] border-[#BDEBFA] hover:border-[#087DB5]' }}">Completed</a>
        </div>
    </div>

    <div class="frosted-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA] font-mono text-[#071F35] bg-[#D8F1FA]/60">
                    <tr>
                        <th class="p-4 font-bold">Ref #</th>
                        <th class="p-4 font-bold">Customer Details</th>
                        <th class="p-4 font-bold">Vehicle</th>
                        <th class="p-4 font-bold">Requested Service</th>
                        <th class="p-4 font-bold">Date &amp; Time</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 text-right font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/40 bg-white/70">
                    @forelse($bookings ?? [] as $b)
                        <tr class="hover:bg-[#EBF7FD]/80 transition-colors">
                            <td class="p-4 font-mono text-[#087DB5] font-bold">{{ $b['booking_number'] ?? $b['booking_reference'] }}</td>
                            <td class="p-4">
                                <div class="font-bold text-[#071F35] text-sm">{{ $b['name'] ?? $b['customer_name'] }}</div>
                                <div class="text-[#334E68] font-mono"><a href="tel:{{ $b['phone'] ?? $b['customer_phone'] }}" class="hover:underline">{{ $b['phone'] ?? $b['customer_phone'] }}</a></div>
                                <div class="text-[#334E68]/70 text-[11px] font-mono">{{ $b['email'] ?? $b['customer_email'] }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-[#071F35] font-medium">{{ $b['vehicle_info'] ?? ($b['vehicle_year'] ?? '') . ' ' . ($b['vehicle_make'] ?? '') . ' ' . ($b['vehicle_model'] ?? 'Vehicle Not Specified') }}</div>
                            </td>
                            <td class="p-4 text-[#071F35] font-semibold">{{ $b['service'] ?? $b['service_name'] }}</td>
                            <td class="p-4 font-mono">
                                <div class="text-[#071F35] font-medium">{{ $b['preferred_date'] }}</div>
                                <div class="text-[#334E68] text-[11px]">{{ $b['preferred_time'] }}</div>
                            </td>
                            <td class="p-4">
                                @php $st = strtolower($b['status'] ?? 'pending'); @endphp
                                <span class="status-badge status-{{ $st }}">{{ $b['status'] ?? 'Pending' }}</span>
                            </td>
                            <td class="p-4 text-right space-x-2 font-mono">
                                <a href="/admin/bookings/{{ $b['id'] }}" class="btn-navy-pill text-[11px] px-3.5 py-1.5 shadow-sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-[#334E68] font-mono">No bookings found in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection