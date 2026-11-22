@extends('layouts.app')

@section('title', 'About Us | North Fork Automotive')
@section('description', 'Driven by quality. Trusted by Talkeetna, Alaska.')

@section('content')
<!-- MOUNTAIN HERO BANNER (MATCHES MIDDLE-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden py-14 sm:py-16">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-30">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35] via-[#071F35]/85 to-[#071F35]/70"></div>
    <div class="relative section-pad max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
                About Us
            </h1>
            <p class="text-sm sm:text-base text-[#BDEBFA] font-medium">
                Driven by Quality. Trusted by Talkeetna.
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
        <!-- Left Column: Our Story -->
        <div class="lg:col-span-7 space-y-5">
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35]">
                Our Story
            </h2>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                North Fork Automotive was built with a simple mission: to provide honest, reliable, and high-quality auto repair services to the Talkeetna community and beyond.
            </p>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                We take pride in our work and treat every customer like family. Whether you're a year-round resident preparing for sub-zero weather or passing through on the Parks Highway, our certified technicians are dedicated to keeping you safe on the road.
            </p>
            <div class="pt-3">
                <a href="/book" class="btn-navy-pill text-xs px-6 py-2.5">
                    Learn More →
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
                    <span class="font-heading font-bold text-sm text-[#071F35]">Experienced &amp; Certified Technicians</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Transparent Pricing</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">12-Month / 12K-Mile Warranty</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Locally Owned &amp; Operated</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
