@php
    $currentRoute = request()->path();
@endphp

<header class="sticky top-0 left-0 right-0 z-50 transition-all duration-300 bg-white/85 backdrop-blur-md border-b border-[#BDEBFA]/80 shadow-sm">
    <div class="section-pad">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <img src="/images/logo.png" alt="North Fork Automotive - Talkeetna, AK" class="h-10 sm:h-12 w-auto object-contain">
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="/" class="text-sm font-semibold tracking-wide transition-colors {{ $currentRoute === '/' || $currentRoute === '' ? 'text-[#087DB5] font-bold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">Home</a>
                <div class="relative group">
                    <a href="/services" class="text-sm font-semibold tracking-wide transition-colors flex items-center gap-1 py-2 {{ request()->is('services*') ? 'text-[#087DB5] font-bold border-b-2 border-[#087DB5]' : 'text-[#071F35] hover:text-[#087DB5]' }}">
                        Services
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                    <div class="absolute left-0 top-full pt-1.5 w-60 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="bg-white/95 backdrop-blur-md border border-[#BDEBFA] rounded-2xl shadow-xl p-3 space-y-1">
                            <a href="/book?service=Engine%20Diagnostics" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Diagnostics</a>
                            <a href="/book?service=Brake%20System%20Service" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Brake Repair</a>
                            <a href="/book?service=Oil%20%26%20Filter%20Change" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Oil &amp; Filter Service</a>
                            <a href="/book?service=Preventive%20Maintenance" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">General Auto Repair</a>
                            <a href="/book?service=Engine%20Diagnostics" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Engine Service</a>
                            <a href="/book?service=Tire%20%26%20Wheel%20Service" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Tire Services</a>
                            <div class="border-t border-[#BDEBFA] my-1 pt-1"></div>
                            <a href="/services" class="block px-3 py-1.5 rounded-xl text-[11px] font-bold text-[#087DB5] hover:bg-[#EBF7FD] transition">View All Services →</a>
                        </div>
                    </div>
                </div>
                <a href="/about" class="text-sm font-semibold tracking-wide transition-colors {{ request()->is('about*') ? 'text-[#087DB5] font-bold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">About</a>
                <a href="/reviews" class="text-sm font-semibold tracking-wide transition-colors {{ request()->is('reviews*') ? 'text-[#087DB5] font-bold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">Reviews</a>
                <a href="/contact" class="text-sm font-semibold tracking-wide transition-colors {{ request()->is('contact*') ? 'text-[#087DB5] font-bold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">Contact</a>
            </nav>

            <!-- Phone Pill Button -->
            <div class="hidden md:flex items-center gap-4">
                <a href="tel:9077333030" class="btn-navy-pill text-xs">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    (907) 733-3030
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="flex md:hidden items-center gap-3">
                <a href="tel:9077333030" class="btn-navy-pill px-3 py-1.5 text-xs">
                    Call
                </a>
                <button id="mobile-toggle" aria-label="Toggle Navigation Menu" class="p-2 text-[#071F35] hover:text-[#087DB5]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="hidden md:hidden border-b border-[#BDEBFA] bg-white/98 backdrop-blur-md px-6 py-6 space-y-4">
        <a href="/" class="block text-sm font-semibold {{ $currentRoute === '/' || $currentRoute === '' ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Home</a>
        <a href="/services" class="block text-sm font-semibold {{ request()->is('services*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Services</a>
        <a href="/about" class="block text-sm font-semibold {{ request()->is('about*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">About</a>
        <a href="/reviews" class="block text-sm font-semibold {{ request()->is('reviews*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Reviews</a>
        <a href="/contact" class="block text-sm font-semibold {{ request()->is('contact*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Contact</a>
        <a href="/book" class="block btn-navy-pill text-center py-3 text-xs font-bold mt-4">Book Your Appointment</a>
    </div>
</header>
