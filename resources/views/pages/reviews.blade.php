@extends('layouts.app')

@section('title', 'Customer Reviews | North Fork Auto')
@section('description', 'What Talkeetna drivers say about North Fork Automotive.')

@section('content')
<!-- REVIEWS HERO BANNER (MISTY ALASKAN MOUNTAINS WITH FROST OVERLAY) -->
<section class="relative w-full overflow-hidden pt-32 pb-14 sm:pt-36 sm:pb-16">
    <!-- Layer 1: Full-Width Background: Misty Alaskan Mountain Range & Dense Pine Valley -->
    <div class="absolute inset-0 z-0 bg-[#EBF7FD]">
        <img src="/images/misty-mountains-hero.jpg" alt="Misty Alaskan Mountain Valley in Talkeetna, Alaska" class="w-full h-full object-cover object-center opacity-75 sm:opacity-80">
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
    <div class="absolute top-0 inset-x-0 h-14 bg-gradient-to-b from-white/35 to-transparent z-1 pointer-events-none"></div>
    <div class="absolute bottom-0 inset-x-0 h-14 bg-gradient-to-t from-[#EBF7FD]/80 to-transparent z-1 pointer-events-none"></div>

    <!-- Layer 3: Foreground Content (Padded to clear fixed navbar seamlessly, Left-Aligned) -->
    <div class="relative z-10 section-pad w-full">
        <div class="max-w-2xl text-left">
            <div class="text-xs sm:text-sm font-bold uppercase tracking-widest text-[#087DB5] mb-2.5">
                Real Customer Feedback
            </div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-[#071F35] mb-3 leading-tight">
                Customer Reviews
            </h1>
            <p class="text-sm sm:text-base text-[#334E68] font-medium leading-relaxed max-w-xl">
                What Talkeetna Drivers Say About North Fork Automotive
            </p>
        </div>
    </div>
</section>

<!-- REVIEWS GRID -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "Broke down on the Spur Road during sub-zero temps. North Fork Auto quickly diagnosed the alternator and got us back on the road the same day. Honest, fast, and fair pricing."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">Marcus T.</span>
                <span class="text-[#087DB5] font-semibold">Talkeetna Resident</span>
            </div>
        </div>
        
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "The best brake and suspension shop in the Mat-Su area. They showed me exactly which parts were worn and gave me options. My F-250 handles the gravel roads like new again."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">David K.</span>
                <span class="text-[#087DB5] font-semibold">Mat-Su Valley</span>
            </div>
        </div>
        
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "Having a trustworthy mechanic in Talkeetna is a lifesaver. Routine maintenance, fluid checks, and pre-winter inspections are always on time and professional."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">Sarah B.</span>
                <span class="text-[#087DB5] font-semibold">Trapper Creek</span>
            </div>
        </div>
    </div>
    
    <div class="mt-12 text-center">
        <a href="/book" class="btn-navy-pill text-sm px-8 py-3.5">
            Book Your Appointment Today →
        </a>
    </div>
</section>
@endsection
