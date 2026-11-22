@extends('layouts.app')

@section('title', 'North Fork Automotive | Reliable Auto Repair in Talkeetna, Alaska')
@section('description', 'Honest answers, quality workmanship, and a team that cares in Talkeetna, AK.')

@section('content')
<!-- SECTION 1: HOMEPAGE HERO (EXACT MATCH TO REFERENCE SCREENSHOT) -->
<section class="section-pad pt-8 pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
        <!-- Left Column: Brand Lockup & CTAs -->
        <div class="lg:col-span-5 flex flex-col justify-center">
            <h1 class="text-[#071F35] font-heading font-extrabold uppercase tracking-tight leading-[0.96] text-4xl sm:text-5xl lg:text-[3.6rem]">
                <span class="block">North Fork</span>
                <span class="block mt-1">Automotive</span>
            </h1>
            
            <!-- Car with Mechanic Hood Graphic -->
            <div class="my-3">
                <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive Mechanic & Car" class="h-14 sm:h-16 w-auto object-contain">
                <div class="flex items-center gap-2.5 mt-1.5 max-w-[240px]">
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                    <span class="font-heading font-bold text-xs uppercase tracking-widest text-[#071F35]">Talkeetna, AK</span>
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                </div>
            </div>
            
            <h2 class="text-xl sm:text-2xl font-heading font-extrabold italic text-[#071F35] mt-2 mb-3 leading-snug">
                Reliable Auto Repair<br>in Talkeetna - Alaska
            </h2>
            
            <p class="text-sm sm:text-base text-[#1E3A5F] font-medium leading-relaxed mb-8 max-w-md">
                Honest answers, quality workmanship,<br class="hidden sm:inline"> and a team that cares.
            </p>
            
            <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                <a href="/book" class="inline-flex items-center justify-center gap-2.5 bg-[#071F35] hover:bg-[#0B2B47] text-white px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book Your Appointment</span>
                </a>
                <a href="tel:9077333030" class="inline-flex items-center justify-center gap-2.5 bg-white/95 hover:bg-white text-[#071F35] border border-[#BDEBFA] hover:border-[#087DB5] px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-[#071F35] shrink-0 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    <span>Call (907) 733-3030</span>
                </a>
            </div>
        </div>
        
        <!-- Right Column: Truck & Mountain Photograph -->
        <div class="lg:col-span-7">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-[#BDEBFA] group">
                <img src="/images/hero-truck-mountains.jpg" alt="Dark-blue pickup truck parked on wet reflective ice in front of Alaskan snow-covered mountain range" class="w-full h-auto max-h-[460px] object-cover object-center group-hover:scale-102 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-[#071F35]/20 via-transparent to-transparent pointer-events-none"></div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: TRUST BAR (4 HORIZONTAL INDICATORS) -->
<section class="section-pad my-4">
    <div class="bg-[#D8F1FA]/85 backdrop-blur-md rounded-2xl border border-[#BDEBFA] py-5 px-6 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:divide-x md:divide-[#BDEBFA]">
            <div class="flex items-center gap-3.5 md:justify-center">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">ASE-Certified</h3>
                    <p class="text-xs text-[#334E68]">Technicians</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Transparent</h3>
                    <p class="text-xs text-[#334E68]">Estimates</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">12-Month / 12K-Mile</h3>
                    <p class="text-xs text-[#334E68]">Warranty</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Locally Owned</h3>
                    <p class="text-xs text-[#334E68]">&amp; Operated</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: OUR SERVICES (4 CARDS LEFT, MECHANIC PHOTO RIGHT) -->
<section class="section-pad py-16">
    <div class="text-center mb-12">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— OUR SERVICES —</span>
        <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-[#071F35] tracking-tight mt-1 mb-2">
            Expert Auto Services
        </h2>
        <p class="text-sm sm:text-base text-[#334E68] max-w-xl mx-auto">
            From diagnostics to major repairs, we've got you covered.
        </p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- 4 Frosted Service Cards (Left) -->
        <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-lg text-[#071F35] mb-1">Diagnostics</h3>
                <p class="text-xs text-[#334E68] mb-4">Find the problem. Fast.</p>
                <a href="/book?service=Engine%20Diagnostics" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Learn More →
                </a>
            </div>
            
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
                </div>
                <h3 class="font-heading font-bold text-lg text-[#071F35] mb-1">Brake Repair</h3>
                <p class="text-xs text-[#334E68] mb-4">Stop with confidence.</p>
                <a href="/book?service=Brake%20System%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Learn More →
                </a>
            </div>
            
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-lg text-[#071F35] mb-1">Oil &amp; Filter Service</h3>
                <p class="text-xs text-[#334E68] mb-4">Keep your engine healthy.</p>
                <a href="/book?service=Oil%20%26%20Filter%20Change" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Learn More →
                </a>
            </div>
            
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L7.757 7.757m0 0l2.829-2.829a3 3 0 114.242 4.242L12 12"/></svg>
                </div>
                <h3 class="font-heading font-bold text-lg text-[#071F35] mb-1">General Auto Repair</h3>
                <p class="text-xs text-[#334E68] mb-4">Get back on the road.</p>
                <a href="/book?service=Preventive%20Maintenance" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Learn More →
                </a>
            </div>
        </div>
        
        <!-- Photographic Mechanic Visual (Right) -->
        <div class="lg:col-span-5">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-[#BDEBFA]">
                <img src="/images/service-mechanic-engine.jpg" alt="Automotive mechanic inspecting engine components with diagnostic tools" class="w-full h-[360px] object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-t from-[#071F35]/25 via-transparent to-transparent pointer-events-none"></div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: ABOUT NORTH FORK AUTOMOTIVE (WORKSHOP PHOTO, STORY, 4 BADGES) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Workshop Exterior Photo (Left - 4 cols) -->
        <div class="lg:col-span-4">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-[#BDEBFA]">
                <img src="/images/workshop-exterior.jpg" alt="North Fork Automotive Workshop Exterior in Talkeetna, Alaska" class="w-full h-[320px] object-cover object-center">
            </div>
        </div>
        
        <!-- Story & Description (Center - 5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">ABOUT NORTH FORK AUTOMOTIVE —</span>
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35] tracking-tight leading-snug">
                Your Trusted Auto Repair Shop in Talkeetna
            </h2>
            <p class="text-sm text-[#334E68] leading-relaxed">
                We're committed to keeping you on the road. From diagnostics to major repairs, our experienced technicians deliver dependable service with transparent pricing and a 12-month / 12K-mile warranty.
            </p>
            <div class="pt-2">
                <a href="/about" class="btn-navy-pill text-xs px-6 py-2.5">
                    Learn More →
                </a>
            </div>
        </div>
        
        <!-- 4 Badges Panel (Right - 3 cols) -->
        <div class="lg:col-span-3">
            <div class="bg-[#D8F1FA] border border-[#BDEBFA] rounded-2xl p-6 shadow-sm flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Experienced Team</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Quality Service</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Fair Pricing</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Customer Focus</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
