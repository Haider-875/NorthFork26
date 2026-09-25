<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal | North Fork Auto')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/northfork.css') }}" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background: hsl(240 6% 6%) !important; color: hsl(220 20% 98%) !important; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', sans-serif; color: hsl(220 20% 98%); }
        a { color: inherit; text-decoration: none; }
        a:hover { color: inherit; text-decoration: none; }
        .font-mono-data { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: hsl(349 77% 50%); }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-background text-foreground antialiased flex flex-col md:flex-row">
    <!-- Admin Sidebar -->
    <aside id="admin-sidebar" class="w-full md:w-64 border-b md:border-b-0 md:border-r border-border bg-card/60 flex flex-col shrink-0">
        <div class="h-16 flex items-center justify-between px-6 border-b border-border">
            <a href="/admin" class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 border-2 border-primary text-primary font-heading font-800 text-base leading-none">N</span>
                <span class="font-heading font-800 text-sm tracking-tight">NFA Admin</span>
            </a>
            <button id="admin-mobile-nav-toggle" class="md:hidden p-2 text-muted-foreground hover:text-foreground">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <nav id="admin-nav-links" class="hidden md:flex flex-col p-4 space-y-1 flex-1">
            <a href="/admin" class="flex items-center gap-3 px-3 py-2 text-sm rounded font-heading font-600 {{ request()->is('admin') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="/admin/bookings" class="flex items-center gap-3 px-3 py-2 text-sm rounded font-heading font-600 {{ request()->is('admin/bookings*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Bookings
            </a>
            <a href="/admin/services" class="flex items-center gap-3 px-3 py-2 text-sm rounded font-heading font-600 {{ request()->is('admin/services*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                Services
            </a>
            <a href="/admin/users" class="flex items-center gap-3 px-3 py-2 text-sm rounded font-heading font-600 {{ request()->is('admin/users*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users &amp; Staff
            </a>
            <a href="/admin/settings" class="flex items-center gap-3 px-3 py-2 text-sm rounded font-heading font-600 {{ request()->is('admin/settings*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Shop Settings
            </a>
            
            <div class="pt-6 mt-6 border-t border-border">
                <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2 text-xs text-muted-foreground hover:text-foreground">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Live Website
                </a>
                <form action="/admin/logout" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-xs text-primary hover:bg-secondary rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <!-- Main Admin Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 border-b border-border bg-card/40 px-6 flex items-center justify-between">
            <h1 class="font-heading font-700 text-lg">@yield('header_title', 'Admin Console')</h1>
            <div class="flex items-center gap-4">
                <span class="font-mono-data text-xs bg-secondary px-3 py-1 border border-border text-foreground">Logged in as: Administrator</span>
            </div>
        </header>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 p-4 border border-emerald-500/40 bg-emerald-500/10 text-emerald-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 border border-primary/40 bg-primary/10 text-primary text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        document.getElementById('admin-mobile-nav-toggle')?.addEventListener('click', () => {
            const nav = document.getElementById('admin-nav-links');
            nav.classList.toggle('hidden');
            nav.classList.toggle('flex');
        });
    </script>
    @stack('scripts')
</body>
</html>
