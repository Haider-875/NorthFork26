@extends('layouts.app')

@section('title', 'Our Services | North Fork Automotive')
@section('description', 'Comprehensive automotive repair and maintenance in Talkeetna, Alaska.')

@section('content')
<!-- MOUNTAIN HERO BANNER (MATCHES TOP-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden py-16 sm:py-20">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35]/90 via-[#071F35]/75 to-[#071F35]/90"></div>
    <div class="relative section-pad text-center max-w-3xl mx-auto">
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
            Our Services
        </h1>
        <p class="text-sm sm:text-base text-[#BDEBFA] font-medium">
            Professional Auto Care You Can Count On
        </p>
    </div>
</section>

<!-- 6 SERVICE CARDS GRID -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- 1. Diagnostics -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">Diagnostics</h3>
            <p class="text-sm text-[#334E68] mb-6">Find the issue with precision.</p>
            <a href="/book?service=Engine%20Diagnostics" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
            </a>
        </div>
        
        <!-- 2. Brake Repair -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">Brake Repair</h3>
            <p class="text-sm text-[#334E68] mb-6">Drive safe, stop confident.</p>
            <a href="/book?service=Brake%20System%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
            </a>
        </div>
        
        <!-- 3. Oil & Filter Service -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">Oil &amp; Filter Service</h3>
            <p class="text-sm text-[#334E68] mb-6">Keep your engine running smoothly.</p>
            <a href="/book?service=Oil%20%26%20Filter%20Change" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
            </a>
        </div>
        
        <!-- 4. General Auto Repair -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L7.757 7.757m0 0l2.829-2.829a3 3 0 114.242 4.242L12 12"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">General Auto Repair</h3>
            <p class="text-sm text-[#334E68] mb-6">From minor fixes to major repairs.</p>
            <a href="/book?service=Preventive%20Maintenance" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
            </a>
        </div>
        
        <!-- 5. Engine Service -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">Engine Service</h3>
            <p class="text-sm text-[#334E68] mb-6">Boost performance and reliability.</p>
            <a href="/book?service=Engine%20Diagnostics" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
            </a>
        </div>
        
        <!-- 6. Tire Services -->
        <div class="frosted-card p-8 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-5 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v7m0 6v7m-10-8h7m6 0h7"/></svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-[#071F35] mb-2">Tire Services</h3>
            <p class="text-sm text-[#334E68] mb-6">Stay safe on any road.</p>
            <a href="/book?service=Tire%20%26%20Wheel%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Learn More →
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
