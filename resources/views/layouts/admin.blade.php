<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal | North Fork Auto')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-navy: #071F35;
            --color-navy-dark: #041321;
            --color-ocean: #087DB5;
            --color-ice: #BDEBFA;
            --color-sky: #D8F1FA;
            --color-bg-light: #EBF7FD;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--color-bg-light); color: var(--color-navy); }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        .frosted-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid #BDEBFA;
            border-radius: 1rem;
            box-shadow: 0 4px 20px -2px rgba(8, 125, 181, 0.08);
        }

        .btn-navy-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #071F35;
            color: #FFFFFF !important;
            font-weight: 700;
            border-radius: 9999px;
            box-shadow: 0 4px 14px 0 rgba(7, 31, 53, 0.20);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }
        .btn-navy-pill:hover {
            background-color: #0B2B47;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(7, 31, 53, 0.30);
        }

        .btn-white-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: rgba(255, 255, 255, 0.95);
            color: #071F35 !important;
            border: 1.5px solid #071F35;
            font-weight: 700;
            border-radius: 9999px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }
        .btn-white-pill:hover {
            background-color: #FFFFFF;
            border-color: #087DB5;
            color: #087DB5 !important;
            transform: translateY(-1px);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            border-radius: 9999px;
        }
        .status-pending { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; }
        .status-confirmed { background: #D8F1FA; color: #087DB5; border: 1px solid #BDEBFA; }
        .status-in_progress { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }
        .status-completed { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .status-cancelled { background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-[#EBF7FD] text-[#071F35] antialiased flex flex-col md:flex-row">
    <!-- Admin Sidebar -->
    <aside id="admin-sidebar" class="w-full md:w-64 bg-[#071F35] border-b md:border-b-0 md:border-r border-[#BDEBFA]/20 p-6 flex flex-col justify-between shrink-0 shadow-2xl z-20">
        <div>
            <!-- Brand -->
            <div class="flex items-center justify-between md:justify-start gap-3.5 mb-8 pb-6 border-b border-[#BDEBFA]/15">
                <a href="/admin" class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="North Fork Auto" class="h-10 w-auto object-contain rounded-xl bg-white/10 border border-[#BDEBFA]/30 p-1">
                    <div class="min-w-0">
                        <div class="font-heading font-extrabold text-sm tracking-wide text-white leading-tight">North Fork Auto</div>
                        <div class="font-mono text-[10px] text-[#BDEBFA] uppercase tracking-wider font-semibold">Staff Console</div>
                    </div>
                </a>
                <button id="admin-mobile-nav-toggle" class="md:hidden p-2 text-[#D8F1FA] hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav id="admin-nav-links" class="hidden md:flex flex-col space-y-1.5 font-sans text-xs font-semibold">
                <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->is('admin') ? 'bg-[#087DB5] text-white font-bold shadow-md shadow-[#087DB5]/30' : 'text-[#D8F1FA]/80 hover:text-white hover:bg-[#0E3557]' }}">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="/admin/bookings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->is('admin/bookings*') ? 'bg-[#087DB5] text-white font-bold shadow-md shadow-[#087DB5]/30' : 'text-[#D8F1FA]/80 hover:text-white hover:bg-[#0E3557]' }}">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    Bookings
                </a>
                <a href="/admin/services" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->is('admin/services*') ? 'bg-[#087DB5] text-white font-bold shadow-md shadow-[#087DB5]/30' : 'text-[#D8F1FA]/80 hover:text-white hover:bg-[#0E3557]' }}">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Services Catalog
                </a>
                <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->is('admin/users*') ? 'bg-[#087DB5] text-white font-bold shadow-md shadow-[#087DB5]/30' : 'text-[#D8F1FA]/80 hover:text-white hover:bg-[#0E3557]' }}">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Users &amp; Staff
                </a>
                <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors {{ request()->is('admin/settings*') ? 'bg-[#087DB5] text-white font-bold shadow-md shadow-[#087DB5]/30' : 'text-[#D8F1FA]/80 hover:text-white hover:bg-[#0E3557]' }}">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings &amp; Inbox
                </a>
            </nav>
        </div>

        <div class="pt-6 border-t border-[#BDEBFA]/15 space-y-3">
            <a href="/" target="_blank" class="flex items-center gap-2 text-xs font-mono text-[#D8F1FA]/80 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5 text-[#BDEBFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Live Public Website
            </a>
            <form action="/admin/logout" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 text-xs font-mono text-rose-300 hover:text-rose-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Admin Content Area -->
    <div class="flex-1 flex flex-col min-w-0 relative bg-[#EBF7FD]">
        <!-- Subtle ice texture background blend -->
        <div class="absolute inset-0 pointer-events-none opacity-25 z-0">
            <img src="/images/hero-frost-texture.jpg" class="w-full h-full object-cover mix-blend-multiply" alt="">
        </div>

        <div class="relative z-10 flex-1 flex flex-col">
            <header class="h-16 border-b border-[#BDEBFA] bg-white/70 backdrop-blur-md px-6 lg:px-12 flex items-center justify-between">
                <div>
                    <h1 class="font-heading font-extrabold text-base sm:text-lg text-[#071F35]">@yield('header_title', 'Admin Console')</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-2 bg-[#D8F1FA] border border-[#BDEBFA] text-[#087DB5] text-xs font-mono font-bold px-3 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Administrator · Talkeetna, AK
                    </span>
                </div>
            </header>

            <main class="flex-1 p-6 lg:p-12 overflow-y-auto max-w-7xl w-full mx-auto space-y-8">
                @if(session('success'))
                    <div class="p-4 border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-mono rounded-xl shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 border border-rose-300 bg-rose-50 text-rose-700 text-xs font-mono rounded-xl shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.getElementById('admin-mobile-nav-toggle')?.addEventListener('click', () => {
            const nav = document.getElementById('admin-nav-links');
            nav.classList.toggle('hidden');
            nav.classList.toggle('flex');
        });
    </script>
</body>
</html>