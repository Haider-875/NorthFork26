@extends('layouts.app')

@section('title', 'About Us | North Fork Auto')
@section('description', 'Locally owned. Alaskan tested. Professional automotive care in Talkeetna, Alaska.')

@section('content')
<!-- MOUNTAIN HERO BANNER (MATCHES MIDDLE-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-14 sm:pt-36 sm:pb-16">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-30">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35] via-[#071F35]/85 to-[#071F35]/70"></div>
    <div class="relative section-pad max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="text-xs font-bold uppercase tracking-widest text-[#BDEBFA] mb-2">About North Fork Auto</div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
                Locally Owned. Alaskan Tested.
            </h1>
            <p class="text-sm sm:text-base text-[#BDEBFA] font-medium leading-relaxed max-w-2xl">
                North Fork Auto is Talkeetna's premier independent auto care facility. We understand what it takes to keep vehicles running safely through Alaskan winters and rugged summer terrain.
            </p>
        </div>
        <div class="w-full md:w-64 rounded-xl overflow-hidden border border-[#BDEBFA]/30 shadow-lg shrink-0">
            <img src="/images/workshop-exterior.jpg" alt="North Fork Automotive Workshop" class="w-full h-32 sm:h-36 object-cover object-center">
        </div>
    </div>
</section>

<!-- TWO-COLUMN CONTENT SECTION (MATCHES SCREENSHOT) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        <!-- Left Column: Our Story & Values -->
        <div class="lg:col-span-7 space-y-5">
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35]">
                Our Story &amp; Values
            </h2>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                Founded with a mission to deliver straightforward, trustworthy auto repairs to the Talkeetna community, North Fork Auto brings modern diagnostic technology and old-school honesty to every job.
            </p>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                We believe in total transparency. When you bring your vehicle in, we thoroughly inspect it, explain what needs immediate attention versus what can wait, and provide clear upfront estimates before any work begins.
            </p>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                Whether you're a year-round resident preparing for sub-zero weather or passing through on the Parks Highway, our certified technicians are dedicated to keeping you safe on the road.
            </p>
            <div class="pt-3">
                <a href="/book" class="btn-navy-pill text-xs px-6 py-2.5">
                    Book an Appointment →
                </a>
            </div>
        </div>
        
        <!-- Right Column: 4 Checkmarks Card -->
        <div class="lg:col-span-5">
            <div class="frosted-card p-8 space-y-5 border-2 border-[#BDEBFA]">
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">100% Locally Owned &amp; Operated</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">ASE Certified Technicians</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">3-Year / 36,000-Mile Warranty</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Transparent Upfront Pricing</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
