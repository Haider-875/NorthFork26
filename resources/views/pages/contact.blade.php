@extends('layouts.app')

@section('title', 'Contact & Location | North Fork Auto')
@section('description', 'Conveniently located off the Talkeetna Spur Road. Drop by during shop hours or send us a message below.')

@section('content')
<!-- CONTACT US HERO BANNER (MISTY ALASKAN MOUNTAINS WITH FROST OVERLAY) -->
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
                Visit Us In Talkeetna
            </div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-[#071F35] mb-3 leading-tight">
                Contact &amp; Location
            </h1>
            <p class="text-sm sm:text-base text-[#334E68] font-medium leading-relaxed max-w-xl">
                Conveniently located off the Talkeetna Spur Road. Drop by during shop hours or send us a message below.
            </p>
        </div>
    </div>
</section>

<!-- TWO-COLUMN CONTENT SECTION (MATCHES SCREENSHOT) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- Left Column: Shop Details -->
        <div class="lg:col-span-5 space-y-6">
            <div class="frosted-card p-8 space-y-6">
                <h3 class="font-heading font-bold text-xl text-[#071F35] border-b border-[#BDEBFA] pb-3">Shop Details</h3>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Shop Phone</span>
                        <a href="tel:9077333030" class="font-heading font-bold text-base text-[#071F35] hover:text-[#087DB5] transition">(907) 733-3030</a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">After-Hours Calls</span>
                        <a href="tel:9072323859" class="font-heading font-bold text-base text-[#071F35] hover:text-[#087DB5] transition">(907) 232-3859</a>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Shop Location</span>
                        <span class="font-heading font-bold text-base text-[#071F35]">Talkeetna Spur Road, Talkeetna, AK 99676</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Email Inquiries</span>
                        <a href="mailto:titussr88@gmail.com" class="font-heading font-bold text-base text-[#071F35] hover:text-[#087DB5] transition">titussr88@gmail.com</a>
                    </div>
                </div>
            </div>
            
            <div class="frosted-card p-6">
                <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block mb-2">Shop Hours</span>
                <p class="text-sm font-semibold text-[#071F35]">Monday – Friday: 8:00 AM – 5:00 PM</p>
                <p class="text-xs text-[#334E68] mt-1">Saturday: By Appointment · Sunday: Closed</p>
            </div>
        </div>
        
        <!-- Right Column: Send Us a Message Form -->
        <div class="lg:col-span-7">
            <div class="frosted-card p-8 border-2 border-[#BDEBFA]">
                <h3 class="font-heading font-bold text-2xl text-[#071F35] mb-2">Send a Message</h3>
                <p class="text-xs text-[#334E68] mb-6">Drop us a line and our team will get back to you promptly.</p>
                
                <div id="contact-feedback" class="hidden mb-4 p-4 rounded-xl text-xs font-bold border border-emerald-400 bg-emerald-500/10 text-emerald-700">
                    Thank you! Your message has been sent successfully. Our team will get back to you shortly.
                </div>
                
                <form id="contact-form" action="/contact" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Your Name *</label>
                        <input type="text" name="name" placeholder="Your Full Name" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Email Address *</label>
                        <input type="email" name="email" placeholder="you@example.com" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Phone Number</label>
                        <input type="tel" name="phone" placeholder="(907) 555-0199" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Subject</label>
                        <input type="text" name="subject" placeholder="e.g. Brake Inspection Inquiry" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Message *</label>
                        <textarea name="message" rows="4" placeholder="How can we help you?" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5] resize-none"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full btn-navy-pill py-3 text-sm font-bold justify-center rounded-lg">
                        Send Inquiry →
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
