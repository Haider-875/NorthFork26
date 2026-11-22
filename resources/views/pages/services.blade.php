@extends('layouts.app')

@section('title', 'Our Automotive Services | North Fork Automotive')
@section('description', 'From routine fluid changes to complete engine diagnostics, our certified technicians keep your vehicle running smoothly in harsh Alaskan conditions.')

@section('content')
<!-- MOUNTAIN HERO BANNER (MATCHES TOP-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-16 sm:pt-36 sm:pb-20">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35]/90 via-[#071F35]/75 to-[#071F35]/90"></div>
    <div class="relative section-pad text-center max-w-3xl mx-auto">
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
            Our Automotive Services
        </h1>
        <p class="text-sm sm:text-base text-[#BDEBFA] font-medium leading-relaxed max-w-2xl mx-auto">
            From routine fluid changes to complete engine diagnostics, our certified technicians keep your vehicle running smoothly in harsh Alaskan conditions.
        </p>
    </div>
</section>

<!-- 8 CANONICAL SERVICES GRID (FULL LIVE WEBSITE DATA) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Diagnostic Inspection -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Diagnostics</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$120.00 · ~60m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Diagnostic Inspection</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.</p>
            <a href="/book?service=Diagnostic%20Inspection" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 2. Brake Repair & Replacement -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Brakes</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Brake Repair &amp; Replacement</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.</p>
            <a href="/book?service=Brake%20Repair%20%26%20Replacement" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 3. Oil & Filter Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Maintenance</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$89.99 · ~45m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Oil &amp; Filter Service</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.</p>
            <a href="/book?service=Oil%20%26%20Filter%20Service" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 4. Engine Repair & Overhaul -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Engine</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$650.00 · ~240m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Engine Repair &amp; Overhaul</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.</p>
            <a href="/book?service=Engine%20Repair%20%26%20Overhaul" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 5. Transmission Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Drivetrain</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Transmission Service</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Fluid exchange, filter replacements, clutch service, and transmission diagnostics.</p>
            <a href="/book?service=Transmission%20Service" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 6. Steering & Suspension -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M2 12h20"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Suspension</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$220.00 · ~120m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Steering &amp; Suspension</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.</p>
            <a href="/book?service=Steering%20%26%20Suspension" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 7. Electrical & Battery -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Electrical</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$135.00 · ~60m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Electrical &amp; Battery</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.</p>
            <a href="/book?service=Electrical%20%26%20Battery" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 8. Cooling & AC Repair -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0-18l3 3m-3-3l-3 3m0 12l3 3m-3-3l-3 3M3 12h18m-18 0l3-3m-3 3l3 3m12-3l3-3m-3 3l3 3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Climate</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$160.00 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Cooling &amp; AC Repair</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.</p>
            <a href="/book?service=Cooling%20%26%20AC%20Repair" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
    </div>
    
    <!-- CTA BUTTON BANNER (MATCHES SCREENSHOT) -->
    <div class="mt-12 text-center">
        <a href="/book" class="inline-flex items-center gap-3 bg-[#071F35] text-white hover:bg-[#0B2B47] px-8 py-4 rounded-full font-bold text-sm shadow-xl transition">
            <svg class="w-5 h-5 text-[#BDEBFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
            <span>Book Your Appointment Today →</span>
        </a>
    </div>
</section>
@endsection
