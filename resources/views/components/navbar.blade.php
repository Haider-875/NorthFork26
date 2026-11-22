<!-- Navbar (Transparent over Hero Banner at top; Scrolled state reveals exact ice texture background with crisp dark text) -->
<header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 {{ !empty($isScrolled) ? 'navbar-scrolled' : 'navbar-top' }}">
    <div class="section-pad">
        <div class="flex items-center justify-between h-20">
            <!-- Logo (Clean transparent silhouette matching reference design) -->
            <a href="/" class="flex items-center gap-2.5 sm:gap-3 group">
                <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive" class="h-8 sm:h-10 w-auto object-contain">
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-[#071F35] text-base sm:text-lg leading-tight tracking-tight">North Fork Automotive</span>
                    <span class="text-[9px] sm:text-[10px] font-bold text-[#087DB5] tracking-widest uppercase">— Talkeetna, AK —</span>
                </div>
            </a>

            <!-- Desktop Navigation (Crisp Dark Text) -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="/" class="text-sm font-bold tracking-wide transition-colors {{ request()->is('/') ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">Home</a>
                <div class="relative group">
                    <a href="/services" class="text-sm font-bold tracking-wide transition-colors flex items-center gap-1 py-2 {{ request()->is('services*') ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5]' : 'text-[#071F35] hover:text-[#087DB5]' }}">
                        Services
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180 text-[#071F35]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </a>
                    <div class="absolute left-0 top-full pt-1.5 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="bg-white/95 backdrop-blur-md border border-[#BDEBFA] rounded-2xl shadow-xl p-3 space-y-1">
                            <a href="/book?service=Diagnostic%20Inspection" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Diagnostic Inspection</a>
                            <a href="/book?service=Brake%20Repair%20%26%20Replacement" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Brake Repair &amp; Replacement</a>
                            <a href="/book?service=Oil%20%26%20Filter%20Service" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Oil &amp; Filter Service</a>
                            <a href="/book?service=Engine%20Repair%20%26%20Overhaul" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Engine Repair &amp; Overhaul</a>
                            <a href="/book?service=Transmission%20Service" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Transmission Service</a>
                            <a href="/book?service=Steering%20%26%20Suspension" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Steering &amp; Suspension</a>
                            <a href="/book?service=Electrical%20%26%20Battery" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Electrical &amp; Battery</a>
                            <a href="/book?service=Cooling%20%26%20AC%20Repair" class="block px-3 py-1.5 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Cooling &amp; AC Repair</a>
                            <div class="border-t border-[#BDEBFA] my-1 pt-1"></div>
                            <a href="/services" class="block px-3 py-1.5 rounded-xl text-[11px] font-bold text-[#087DB5] hover:bg-[#EBF7FD] transition">View All Services (8) →</a>
                        </div>
                    </div>
                </div>
                <a href="/about" class="text-sm font-bold tracking-wide transition-colors {{ request()->is('about*') ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">About</a>
                <a href="/contact" class="text-sm font-bold tracking-wide transition-colors {{ request()->is('contact*') ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]' }}">Contact</a>
            </nav>

            <!-- Phone Pill Button -->
            <div class="hidden md:flex items-center gap-4">
                <a href="tel:9077333030" class="btn-navy-pill text-xs shadow-md">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    (907) 733-3030
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="flex md:hidden items-center gap-3">
                <a href="tel:9077333030" class="btn-navy-pill px-3 py-1.5 text-xs shadow-sm">
                    Call
                </a>
                <button id="mobile-toggle" aria-label="Toggle Navigation Menu" class="p-2 text-[#071F35] hover:text-[#087DB5]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="hidden md:hidden border-b border-[#BDEBFA] bg-white/98 backdrop-blur-md px-6 py-6 space-y-4 shadow-xl" style="background-image: linear-gradient(rgba(240, 249, 254, 0.94), rgba(216, 241, 250, 0.96)), url('/images/hero-frost-texture.jpg'); background-size: cover; background-position: center;">
        <a href="/" class="block text-sm font-bold {{ request()->is('/') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Home</a>
        <a href="/services" class="block text-sm font-bold {{ request()->is('services*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Services</a>
        <a href="/about" class="block text-sm font-bold {{ request()->is('about*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">About</a>
        <a href="/contact" class="block text-sm font-bold {{ request()->is('contact*') ? 'text-[#087DB5]' : 'text-[#071F35]' }} py-1">Contact</a>
        <div class="pt-4 border-t border-[#BDEBFA] flex flex-col gap-3">
            <a href="tel:9077333030" class="btn-navy-pill text-center py-2.5 text-xs font-bold">Call (907) 733-3030</a>
            <a href="/book" class="btn-white-pill text-center py-2.5 text-xs font-bold">Book an Appointment</a>
        </div>
    </div>
</header>
