@extends('layouts.app')

@section('title', 'North Fork Automotive | Reliable Auto Repair in Talkeetna, Alaska')
@section('description', 'Honest answers, quality workmanship, and a team that cares in Talkeetna, AK.')

@section('content')
<!-- SECTION 1: FULL-BLEED HOMEPAGE HERO (FROST TEXTURE OVERLAY BEHIND LEFT-SIDE TEXT ONLY) -->
<section class="relative w-full overflow-hidden min-h-[640px] sm:min-h-[680px] lg:min-h-[720px] flex items-center">
    <!-- Layer 1: Full-Width Background: Dark-blue Ford F-150 in Alaskan Mountains (Sharp & Clear on Right, extends behind navbar) -->
    <div class="absolute inset-0 z-0">
        <img src="/images/hero-truck-mountains.jpg" alt="Dark-blue Ford F-150 in front of Alaskan snow-covered mountain range" class="w-full h-full object-cover object-[80%_center] lg:object-right">
    </div>
    
    <!-- Layer 1.5 & Layer 2: Organic Frost Texture & Watercolor Patch behind Left-Side Text (Naturally Feathered Edges, Zero Sharp Borders) -->
    <div class="absolute inset-y-0 left-0 w-full lg:w-[62%] pointer-events-none z-1" 
         style="-webkit-mask-image: radial-gradient(ellipse 72% 66% at 28% 46%, black 20%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 88%);
                mask-image: radial-gradient(ellipse 72% 66% at 28% 46%, black 20%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 88%);">
        
        <!-- Organic Watercolor Ice Backing (Provides gentle icy watercolor foundation and ensures text readability) -->
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 46%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 42%, rgba(189, 235, 250, 0.45) 72%, transparent 95%);"></div>
        
        <!-- Exact Uploaded Frost Texture Image (Blended softly at 40% opacity) -->
        <img src="/images/hero-frost-texture.jpg" 
             alt="" 
             aria-hidden="true" 
             class="w-full h-full object-cover object-left opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
    </div>
    
    <!-- Top & Bottom Soft Atmospheric Blends -->
    <div class="absolute top-0 inset-x-0 h-14 bg-gradient-to-b from-white/40 to-transparent z-1 pointer-events-none"></div>
    <div class="absolute bottom-0 inset-x-0 h-14 bg-gradient-to-t from-[#D8F1FA]/80 to-transparent z-1 pointer-events-none"></div>

    <!-- Layer 3: Foreground Content (Padded to clear fixed navbar seamlessly, Strictly Left-Aligned) -->
    <div class="relative z-10 section-pad w-full pt-28 pb-14 sm:pt-32 sm:pb-16 lg:pt-36 lg:pb-20">
        <div class="max-w-xl lg:max-w-xl text-left">
            <!-- Status Badge: Currently Open -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/85 border border-[#BDEBFA] text-[#087DB5] text-xs font-bold uppercase tracking-wider mb-2.5 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Currently Open · Mon – Fri 8:00 AM – 5:00 PM</span>
            </div>

            <!-- Eyebrow: Talkeetna, Alaska · Auto Repair Experts -->
            <div class="text-xs sm:text-sm font-bold uppercase tracking-widest text-[#087DB5] mb-1">
                Talkeetna, Alaska · Auto Repair Experts
            </div>

            <h1 class="text-[#071F35] font-heading font-extrabold tracking-tight leading-[0.96] text-3xl sm:text-4xl lg:text-[3.2rem]">
                <span class="block text-[#071F35]">North Fork</span>
                <span class="block mt-1 text-[#071F35]">Automotive</span>
            </h1>
            
            <!-- Car with Mechanic Hood Graphic (Clean Silhouette matching Original Reference) -->
            <div class="my-2.5 max-w-[280px]">
                <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive Mechanic & Car" class="h-11 sm:h-13 w-auto object-contain">
                <div class="flex items-center gap-2.5 mt-1">
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                    <span class="font-heading font-bold text-xs uppercase tracking-widest text-[#071F35]">Talkeetna, AK</span>
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                </div>
            </div>
            
            <!-- Exact Live Headline -->
            <h2 class="text-xl sm:text-2xl font-heading font-extrabold text-[#071F35] mt-1 mb-2 leading-snug">
                Trusted Auto Repair.<br><span class="text-[#087DB5]">Quality Service.</span> Every Time.
            </h2>
            
            <!-- Exact Live Description -->
            <p class="text-xs sm:text-sm text-[#1E3A5F] font-medium leading-relaxed mb-6 max-w-md">
                From routine maintenance to complex diagnostics, North Fork Auto provides professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.
            </p>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                <a href="/book" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#071F35] hover:bg-[#0B2B47] text-white px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book an Appointment</span>
                </a>
                <a href="tel:9077333030" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/95 hover:bg-white text-[#071F35] border border-[#BDEBFA] hover:border-[#087DB5] px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-[#071F35] shrink-0 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    <span>Call Now · (907) 733-3030</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: TRUST BAR (4 HORIZONTAL INDICATORS FROM LIVE SITE) -->
<section class="section-pad my-4">
    <div class="bg-[#D8F1FA]/85 backdrop-blur-md rounded-2xl border border-[#BDEBFA] py-5 px-6 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:divide-x md:divide-[#BDEBFA]">
            <!-- 1. ASE Certified -->
            <div class="flex items-center gap-3.5 md:justify-center">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">ASE Certified</h3>
                    <p class="text-xs text-[#334E68]">Skilled Master Techs</p>
                </div>
            </div>
            
            <!-- 2. OEM Diagnostic -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">OEM Diagnostic</h3>
                    <p class="text-xs text-[#334E68]">Dealer-Level Equipment</p>
                </div>
            </div>
            
            <!-- 3. Warranty Backed -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 00-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Warranty Backed</h3>
                    <p class="text-xs text-[#334E68]">Parts &amp; Labor Protected</p>
                </div>
            </div>
            
            <!-- 4. Local Alaskan -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Local Alaskan</h3>
                    <p class="text-xs text-[#334E68]">Community Focused</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: OUR CAPABILITIES (ALL 8 CANONICAL SERVICES WITH TITLES, DESCRIPTIONS & PRICES) -->
<section class="relative section-pad py-16 overflow-hidden">
    <!-- Section 3 Background: Ice Texture with Soft Top & Bottom Blends -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-20 mix-blend-multiply">
        <div class="absolute inset-0 bg-gradient-to-b from-[#EBF7FD] via-[#EBF7FD]/60 to-[#EBF7FD]"></div>
    </div>
    <div class="text-center mb-12 relative z-10">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— OUR CAPABILITIES —</span>
        <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-[#071F35] tracking-tight mt-1 mb-2">
            Expert Auto Care For Alaskan Roads
        </h2>
        <p class="text-sm sm:text-base text-[#334E68] max-w-xl mx-auto mb-3">
            From routine maintenance to complex diagnostics, we've got you covered.
        </p>
        <a href="/services" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#087DB5] hover:text-[#071F35] transition">
            <span>View All Services (8)</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    
    <!-- Top Row: 4 Cards Left with Frost Texture Overlay + Flipped Photographic Mechanic Visual Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-8">
        <!-- 4 Frosted Service Cards (Left) with Frost Texture Overlay (Same Styling & Reduced Opacity as Hero Banner) -->
        <div class="lg:col-span-7 relative">
            <!-- Left Side Ice Texture Overlay: Seamless Organic Feathering (Zero Sharp Box Corners) -->
            <div class="absolute -inset-6 sm:-inset-10 pointer-events-none z-0 overflow-hidden" 
                 style="-webkit-mask-image: radial-gradient(ellipse 82% 76% at 50% 50%, black 20%, rgba(0,0,0,0.8) 48%, rgba(0,0,0,0.2) 72%, transparent 95%);
                        mask-image: radial-gradient(ellipse 82% 76% at 50% 50%, black 20%, rgba(0,0,0,0.8) 48%, rgba(0,0,0,0.2) 72%, transparent 95%);">
                <!-- Organic Watercolor Ice Backing -->
                <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 45%, rgba(189, 235, 250, 0.40) 72%, transparent 96%);"></div>
                <!-- Exact Uploaded Frost Texture Image (Blended softly at 40% opacity with mix-blend-multiply) -->
                <img src="/images/hero-frost-texture.jpg" 
                     alt="" 
                     aria-hidden="true" 
                     class="w-full h-full object-cover opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 relative z-10">
            <!-- 1. Diagnostic Inspection -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Diagnostics</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$120.00</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Diagnostic Inspection</h3>
                <p class="text-xs text-[#334E68] mb-4">State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.</p>
                <a href="/book?service=Diagnostic%20Inspection" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 2. Brake Repair & Replacement -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Brakes</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$189.99</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Brake Repair &amp; Replacement</h3>
                <p class="text-xs text-[#334E68] mb-4">Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.</p>
                <a href="/book?service=Brake%20Repair%20%26%20Replacement" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 3. Oil & Filter Service -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Maintenance</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$89.99</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Oil &amp; Filter Service</h3>
                <p class="text-xs text-[#334E68] mb-4">Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.</p>
                <a href="/book?service=Oil%20%26%20Filter%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 4. Engine Repair & Overhaul -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Engine</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$650.00</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Engine Repair &amp; Overhaul</h3>
                <p class="text-xs text-[#334E68] mb-4">Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.</p>
                <a href="/book?service=Engine%20Repair%20%26%20Overhaul" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            </div>
        </div>
        
        <!-- Photographic Mechanic Visual (Right) - Mosaic Ice-Crystal Splash Frame (Non-standard organic corners) -->
        <div class="lg:col-span-5 relative z-10 flex items-center justify-center p-2 sm:p-4">
            <!-- Reverse Mosaic Crystalline Ice Aura Behind the Image (Faceted & Reversed) -->
            <div class="absolute -inset-5 sm:-inset-8 pointer-events-none -z-1 shadow-xl" 
                 style="clip-path: polygon(
                     0% 6%, 16% 0%, 38% 3%, 64% 0%, 86% 2%, 100% 0%, 
                     97% 20%, 100% 46%, 95% 68%, 100% 88%, 93% 100%, 
                     74% 96%, 46% 100%, 22% 95%, 0% 100%, 
                     5% 72%, 0% 48%, 4% 24%, 1% 10%
                 ); -webkit-clip-path: polygon(
                     0% 6%, 16% 0%, 38% 3%, 64% 0%, 86% 2%, 100% 0%, 
                     97% 20%, 100% 46%, 95% 68%, 100% 88%, 93% 100%, 
                     74% 96%, 46% 100%, 22% 95%, 0% 100%, 
                     5% 72%, 0% 48%, 4% 24%, 1% 10%
                 ); -webkit-mask-image: radial-gradient(ellipse 92% 90% at 50% 50%, black 65%, rgba(0,0,0,0.8) 85%, transparent 100%);
                    mask-image: radial-gradient(ellipse 92% 90% at 50% 50%, black 65%, rgba(0,0,0,0.8) 85%, transparent 100%);">
                <!-- Reverse Gradient Backing -->
                <div class="absolute inset-0 bg-gradient-to-bl from-[#BDEBFA] via-[#D8F1FA] to-[#087DB5]/25"></div>
                <!-- Reversed Frost Texture Image -->
                <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-60 mix-blend-multiply scale-x-[-1] rotate-180">
            </div>

            <!-- Main Image with Organic Crystalline Mosaic Facet Contour (Zero Standard Box Corners) -->
            <div class="relative w-full h-[420px] sm:h-[450px] shadow-2xl transition-all duration-300 hover:scale-[1.01] overflow-hidden" 
                 style="clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 ); -webkit-clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 );">
                
                <!-- The Uploaded Diagnostic Mechanic Photograph -->
                <img src="/images/service-mechanic-diagnostic.jpg" alt="Automotive diagnostic technician with computer tablet" class="w-full h-full object-cover object-[70%_center]">
                
                <!-- Frost Mosaic Crystallization Over Corners & Edges -->
                <div class="absolute inset-0 pointer-events-none" 
                     style="background: radial-gradient(ellipse 88% 85% at 50% 50%, transparent 58%, rgba(216, 241, 250, 0.45) 78%, rgba(189, 235, 250, 0.85) 100%);">
                    <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-40 mix-blend-overlay">
                </div>

                <!-- Soft Bottom Contrast Shadow -->
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#071F35]/40 via-transparent to-transparent pointer-events-none"></div>
            </div>

            <!-- Exterior Crystalline Accent Border -->
            <div class="absolute inset-1 sm:inset-3 pointer-events-none border-2 border-[#BDEBFA]/50 shadow-inner"
                 style="clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 ); -webkit-clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 );">
            </div>
        </div>
    </div>

    <!-- Bottom Row: Remaining 4 Canonical Services with Frost Texture Overlay -->
    <div class="relative mt-8 z-10">
        <!-- Seamless Organic Frost Texture Overlay Behind Bottom Cards (No Sharp Box Corners) -->
        <div class="absolute -inset-6 sm:-inset-10 pointer-events-none z-0 overflow-hidden" 
             style="-webkit-mask-image: radial-gradient(ellipse 90% 75% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);
                    mask-image: radial-gradient(ellipse 90% 75% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);">
            <!-- Organic Watercolor Ice Backing -->
            <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 45%, rgba(189, 235, 250, 0.40) 72%, transparent 96%);"></div>
            <!-- Frost Texture Image (Blended softly at 40% opacity) -->
            <img src="/images/hero-frost-texture.jpg" 
                 alt="" 
                 aria-hidden="true" 
                 class="w-full h-full object-cover opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 relative z-10">
        <!-- 5. Transmission Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Drivetrain</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Transmission Service</h3>
            <p class="text-xs text-[#334E68] mb-4">Fluid exchange, filter replacements, clutch service, and transmission diagnostics.</p>
            <a href="/book?service=Transmission%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 6. Steering & Suspension -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M2 12h20"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Suspension</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$220.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Steering &amp; Suspension</h3>
            <p class="text-xs text-[#334E68] mb-4">Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.</p>
            <a href="/book?service=Steering%20%26%20Suspension" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 7. Electrical & Battery -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Electrical</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$135.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Electrical &amp; Battery</h3>
            <p class="text-xs text-[#334E68] mb-4">Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.</p>
            <a href="/book?service=Electrical%20%26%20Battery" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 8. Cooling & AC Repair -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0-18l3 3m-3-3l-3 3m0 12l3 3m-3-3l-3 3M3 12h18m-18 0l3-3m-3 3l3 3m12-3l3-3m-3 3l3 3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Climate</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$160.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Cooling &amp; AC Repair</h3>
            <p class="text-xs text-[#334E68] mb-4">Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.</p>
            <a href="/book?service=Cooling%20%26%20AC%20Repair" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>
        </div>
    </div>
</section>

<!-- SECTION 4: ABOUT NORTH FORK AUTOMOTIVE (WORKSHOP BANNER, STORY, 4 POINTS WITH FROST OVERLAY) -->
<section class="relative section-pad py-16 overflow-hidden">
    <!-- Section Background: Ice Texture with Soft Top & Bottom Blends -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-20 mix-blend-multiply">
        <div class="absolute inset-0 bg-gradient-to-b from-[#EBF7FD] via-[#EBF7FD]/65 to-[#EBF7FD]"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center relative z-10">
        <!-- Workshop Exterior Visual (Left - 5 cols) - Chiseled Glacial Ice Silhouette (Zero Frame Look) -->
        <div class="lg:col-span-5 relative z-10 flex items-center justify-center p-2 sm:p-4">
            <!-- Organic Frost Mist Aura Behind Visual (Soft, Non-Geometric Glacial Vapor) -->
            <div class="absolute -inset-5 sm:-inset-7 pointer-events-none -z-1 overflow-hidden" 
                 style="-webkit-mask-image: radial-gradient(ellipse 85% 80% at 50% 50%, black 35%, rgba(0,0,0,0.7) 65%, transparent 95%);
                        mask-image: radial-gradient(ellipse 85% 80% at 50% 50%, black 35%, rgba(0,0,0,0.7) 65%, transparent 95%);">
                <!-- Glacial Cyan Ice Vapour -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#BDEBFA]/70 via-[#D8F1FA]/50 to-[#087DB5]/20"></div>
                <!-- Natural Frost Texture -->
                <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-50 mix-blend-multiply scale-105">
            </div>

            <!-- Dynamic Chiseled Ice Slab (Asymmetrical Glacial Faceted Angles, No Picture Frame) -->
            <div class="relative w-full h-[390px] sm:h-[430px] transition-all duration-500 hover:scale-[1.015] group" 
                 style="clip-path: polygon(
                     16% 0%, 93% 0%, 100% 12%, 
                     100% 80%, 85% 100%, 
                     7% 100%, 0% 88%, 
                     0% 20%
                 ); -webkit-clip-path: polygon(
                     16% 0%, 93% 0%, 100% 12%, 
                     100% 80%, 85% 100%, 
                     7% 100%, 0% 88%, 
                     0% 20%
                 ); filter: drop-shadow(0 20px 25px rgba(7, 31, 53, 0.18));">
                
                <!-- Workshop Exterior Image -->
                <img src="/images/workshop-exterior.jpg" alt="North Fork Automotive Workshop Exterior in Talkeetna, Alaska" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">
                
                <!-- Alaskan Frost Splinters Across the Cut Facets -->
                <div class="absolute inset-0 pointer-events-none" 
                     style="background: linear-gradient(135deg, rgba(216, 241, 250, 0.6) 0%, transparent 22%, transparent 78%, rgba(189, 235, 250, 0.5) 100%),
                                 radial-gradient(ellipse at 50% 50%, transparent 65%, rgba(189, 235, 250, 0.35) 100%);">
                    <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-30 mix-blend-overlay">
                </div>

                <!-- Upper Chiseled Ridge Ice Shimmer -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-[#BDEBFA]/90 to-transparent pointer-events-none"></div>

                <!-- Soft Atmospheric Depth Gradient at Bottom -->
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#071F35]/65 via-[#071F35]/20 to-transparent pointer-events-none"></div>

                <!-- Location Pill Positioned Naturally Along Lower Shelf -->
                <div class="absolute bottom-6 left-7 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#071F35]/85 backdrop-blur-md border border-[#BDEBFA]/50 text-white text-xs font-bold shadow-xl">
                    <svg class="w-3.5 h-3.5 text-[#BDEBFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Talkeetna Spur Road Workshop</span>
                </div>
            </div>
        </div>
        
        <!-- Story Text & Points Container with Frost Texture Overlay (Right - 7 cols) -->
        <div class="lg:col-span-7 relative">
            <!-- Organic Frost Texture & Watercolor Patch Behind Text & Points (Hero Banner Style, No Box Corners) -->
            <div class="absolute -inset-6 sm:-inset-10 pointer-events-none -z-1 overflow-hidden" 
                 style="-webkit-mask-image: radial-gradient(ellipse 85% 80% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);
                        mask-image: radial-gradient(ellipse 85% 80% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);">
                <!-- Watercolor Ice Backing -->
                <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 45%, rgba(189, 235, 250, 0.40) 72%, transparent 96%);"></div>
                <!-- Frost Texture Image (Blended softly at 40% opacity with mix-blend-multiply) -->
                <img src="/images/hero-frost-texture.jpg" 
                     alt="" 
                     aria-hidden="true" 
                     class="w-full h-full object-cover opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
            </div>

            <!-- Content Grid: Story Text (Center) + 4 Points (Right, Taken out of the Card Box) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 items-center relative z-10">
                <!-- Center: Story & Description (7 cols) -->
                <div class="md:col-span-7 space-y-4 text-left">
                    <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">TALKEETNA'S TRUSTED AUTO CARE —</span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35] tracking-tight leading-snug">
                        Locally Owned. Honest Service. Real Results.
                    </h2>
                    <p class="text-xs sm:text-sm text-[#334E68] leading-relaxed">
                        North Fork Auto was built with a simple goal: provide our Talkeetna neighbors with honest, high-quality automotive repair you can count on. We treat every vehicle like it's our own.
                    </p>
                    <div class="pt-2">
                        <a href="/about" class="btn-navy-pill text-xs px-6 py-2.5 shadow-md">
                            Learn More →
                        </a>
                    </div>
                </div>
                
                <!-- Right: 4 Points (5 cols) - COMPLETELY FREED FROM CARD BOX -->
                <div class="md:col-span-5 flex flex-col gap-4">
                    <!-- Point 1 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">ASE-Certified Master Technicians</span>
                    </div>
                    
                    <!-- Point 2 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">Advanced Computer Diagnostics</span>
                    </div>
                    
                    <!-- Point 3 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">3-Year / 36,000-Mile Warranty</span>
                    </div>
                    
                    <!-- Point 4 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">Locally Owned &amp; Community Minded</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: HOMEPAGE CALL TO ACTION (FROM LIVE SITE) -->
<section class="section-pad py-12">
    <div class="frosted-card p-8 sm:p-12 text-center border-2 border-[#BDEBFA] bg-gradient-to-r from-white/95 via-[#EBF7FD] to-white/95">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase mb-2 block">— GET BACK ON THE ROAD —</span>
        <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-[#071F35] tracking-tight mb-3">
            Ready For Reliable Automotive Service?
        </h2>
        <p class="text-sm text-[#334E68] max-w-xl mx-auto mb-8 leading-relaxed">
            Schedule your appointment online or call us directly. We provide transparent estimates and expert care.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/book" class="w-full sm:w-auto btn-navy-pill text-sm px-8 py-3.5 shadow-lg">
                Book Service Appointment
            </a>
            <a href="tel:9077333030" class="w-full sm:w-auto btn-white-pill text-sm px-8 py-3.5 shadow-sm">
                Call (907) 733-3030
            </a>
        </div>
    </div>
</section>
@endsection
