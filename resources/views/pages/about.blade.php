@extends('layouts.app')

@section('title', 'About Us | North Fork Auto')
@section('description', 'Locally owned. Alaskan tested. Professional automotive care in Talkeetna, Alaska.')

@section('content')
<!-- ABOUT US HERO BANNER (WORKSHOP EXTERIOR WITH HOMEPAGE HERO STYLE FROST OVERLAY) -->
<section class="relative w-full overflow-hidden min-h-[440px] sm:min-h-[480px] lg:min-h-[520px] flex items-center">
    <!-- Layer 1: Full-Width Background: Snowy Workshop Exterior in Alaskan Mountains -->
    <div class="absolute inset-0 z-0 bg-[#EBF7FD]">
        <img src="/images/about-hero-workshop.jpg" alt="North Fork Automotive Workshop in Talkeetna, Alaska" class="w-full h-full object-cover object-[70%_center] lg:object-right opacity-75 sm:opacity-80">
    </div>
    
    <!-- Layer 1.5 & Layer 2: Organic Frost Texture & Watercolor Patch behind Left-Side Text (Naturally Feathered Edges, Same as Homepage Hero Banner) -->
    <div class="absolute inset-y-0 left-0 w-full lg:w-[62%] pointer-events-none z-1" 
         style="-webkit-mask-image: radial-gradient(ellipse 75% 70% at 30% 50%, black 25%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 90%);
                mask-image: radial-gradient(ellipse 75% 70% at 30% 50%, black 25%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 90%);">
        
        <!-- Organic Watercolor Ice Backing (Provides gentle icy watercolor foundation and ensures text readability) -->
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 42%, rgba(189, 235, 250, 0.45) 72%, transparent 95%);"></div>
        
        <!-- Exact Frost Texture Image (Blended softly at 40% opacity) -->
        <img src="/images/hero-frost-texture.jpg" 
             alt="" 
             aria-hidden="true" 
             class="w-full h-full object-cover object-left opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
    </div>
    
    <!-- Top & Bottom Soft Atmospheric Blends -->
    <div class="absolute top-0 inset-x-0 h-16 bg-gradient-to-b from-white/35 to-transparent z-1 pointer-events-none"></div>
    <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-[#EBF7FD]/80 to-transparent z-1 pointer-events-none"></div>

    <!-- Layer 3: Foreground Content (Padded to clear fixed navbar seamlessly, Left-Aligned) -->
    <div class="relative z-10 section-pad w-full pt-32 pb-14 sm:pt-36 sm:pb-16 lg:pt-40 lg:pb-20">
        <div class="max-w-2xl text-left">
            <div class="text-xs sm:text-sm font-bold uppercase tracking-widest text-[#087DB5] mb-2.5">
                About North Fork Auto
            </div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-[#071F35] mb-3 leading-tight">
                Locally Owned. Alaskan Tested.
            </h1>
            <p class="text-sm sm:text-base text-[#334E68] font-medium leading-relaxed max-w-xl">
                North Fork Auto is Talkeetna's premier independent auto care facility. We understand what it takes to keep vehicles running safely through Alaskan winters and rugged summer terrain.
            </p>
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
