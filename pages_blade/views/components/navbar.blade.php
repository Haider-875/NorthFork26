@php
    $currentRoute = request()->path();
    $navLinks = [
        ['label' => 'Home', 'path' => '/'],
        ['label' => 'Services', 'path' => '/services'],
        ['label' => 'About', 'path' => '/about'],
        ['label' => 'Visit Us', 'path' => '/contact']
    ];
@endphp

<header id="site-header" class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-gradient-to-b from-background/90 to-transparent">
    <div class="section-pad">
        <div id="header-inner" class="flex items-center justify-between transition-all duration-300 h-20">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <span class="flex items-center justify-center w-9 h-9 border-2 border-primary text-primary font-heading font-800 text-lg leading-none group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                    N
                </span>
                <span class="leading-none">
                    <span class="block font-heading font-800 text-lg tracking-tight text-foreground">North Fork Auto</span>
                    <span class="block font-mono-data text-[10px]">Talkeetna, AK</span>
                </span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-8">
                @foreach($navLinks as $link)
                    @php
                        $isActive = ($link['path'] === '/' && ($currentRoute === '/' || $currentRoute === '')) || ($link['path'] !== '/' && request()->is(ltrim($link['path'], '/').'*'));
                    @endphp
                    <a href="{{ $link['path'] }}" class="font-heading font-600 text-sm uppercase tracking-wide transition-colors hover:text-primary {{ $isActive ? 'text-primary' : 'text-foreground/90' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Desktop CTA & Phone -->
            <div class="hidden lg:flex items-center gap-4">
                <a href="tel:+19077333030" class="flex items-center gap-2 font-heading font-700 text-sm hover:text-primary transition-colors text-foreground">
                    <svg class="w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    (907) 733-3030
                </a>
                <a href="/book" class="btn-engine bg-primary text-primary-foreground px-5 py-2.5 text-xs">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    Book Appointment
                </a>
            </div>

            <!-- Mobile Menu Toggle Button -->
            <button id="mobile-menu-btn" type="button" class="lg:hidden p-2 -mr-2 text-foreground hover:text-primary transition-colors focus:outline-none" aria-label="Toggle menu">
                <svg id="menu-icon-bars" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                <svg id="menu-icon-close" class="w-6 h-6 hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-menu" class="hidden lg:hidden border-t border-border bg-background">
        <nav class="section-pad py-6 flex flex-col gap-1">
            @foreach($navLinks as $link)
                @php
                    $isActive = ($link['path'] === '/' && ($currentRoute === '/' || $currentRoute === '')) || ($link['path'] !== '/' && request()->is(ltrim($link['path'], '/').'*'));
                @endphp
                <a href="{{ $link['path'] }}" class="py-3 font-heading font-600 text-base uppercase tracking-wide border-b border-border/60 transition-colors {{ $isActive ? 'text-primary' : 'text-foreground' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="tel:+19077333030" class="mt-4 flex items-center justify-center gap-2 btn-engine border border-border py-3 text-sm text-foreground hover:border-primary">
                <svg class="w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                (907) 733-3030
            </a>
        </nav>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const header = document.getElementById('site-header');
        const headerInner = document.getElementById('header-inner');
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconBars = document.getElementById('menu-icon-bars');
        const iconClose = document.getElementById('menu-icon-close');

        function onScroll() {
            if (window.scrollY > 24) {
                header.classList.remove('bg-gradient-to-b', 'from-background/90', 'to-transparent');
                header.classList.add('bg-background/95', 'backdrop-blur', 'border-b', 'border-border');
                headerInner.classList.remove('h-20');
                headerInner.classList.add('h-16');
            } else {
                header.classList.add('bg-gradient-to-b', 'from-background/90', 'to-transparent');
                header.classList.remove('bg-background/95', 'backdrop-blur', 'border-b', 'border-border');
                headerInner.classList.remove('h-16');
                headerInner.classList.add('h-20');
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        if (mobileBtn) {
            mobileBtn.addEventListener('click', () => {
                const isHidden = mobileMenu.classList.contains('hidden');
                if (isHidden) {
                    mobileMenu.classList.remove('hidden');
                    iconBars.classList.add('hidden');
                    iconClose.classList.remove('hidden');
                } else {
                    mobileMenu.classList.add('hidden');
                    iconBars.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                }
            });
        }
    });
</script>
