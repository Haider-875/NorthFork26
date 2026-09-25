<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'North Fork Auto | Trusted Auto Repair in Talkeetna, Alaska')</title>
    <meta name="description" content="@yield('description', 'North Fork Auto provides honest, high-quality automotive repair, diagnostics, and maintenance in Talkeetna, Alaska.')">
    
    <!-- Google Fonts: Sora (Headings), Inter (Body), JetBrains Mono (Data) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: 'hsl(240 6% 6%)',
                        foreground: 'hsl(220 20% 98%)',
                        card: {
                            DEFAULT: 'hsl(240 5% 9%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        popover: {
                            DEFAULT: 'hsl(240 6% 8%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        primary: {
                            DEFAULT: 'hsl(349 77% 50%)',
                            foreground: 'hsl(0 0% 100%)'
                        },
                        secondary: {
                            DEFAULT: 'hsl(240 5% 13%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        muted: {
                            DEFAULT: 'hsl(240 5% 13%)',
                            foreground: 'hsl(217 13% 65%)'
                        },
                        accent: {
                            DEFAULT: 'hsl(240 5% 16%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        border: 'hsl(215 19% 22%)',
                        input: 'hsl(215 19% 22%)',
                        ring: 'hsl(349 77% 50%)'
                    },
                    fontFamily: {
                        heading: ['Sora', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        body: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace']
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --background: 240 6% 6%;
            --foreground: 220 20% 98%;
            --card: 240 5% 9%;
            --card-foreground: 220 20% 98%;
            --primary: 349 77% 50%;
            --primary-foreground: 0 0% 100%;
            --secondary: 240 5% 13%;
            --secondary-foreground: 220 20% 98%;
            --muted: 240 5% 13%;
            --muted-foreground: 217 13% 65%;
            --accent: 240 5% 16%;
            --accent-foreground: 220 20% 98%;
            --border: 215 19% 22%;
            --input: 215 19% 22%;
            --ring: 349 77% 50%;
            --font-heading: "Sora", ui-sans-serif, system-ui, sans-serif;
            --font-body: "Inter", ui-sans-serif, system-ui, sans-serif;
            --font-mono: "JetBrains Mono", ui-monospace, monospace;
        }
        body {
            font-family: var(--font-body);
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
        }
        .section-pad {
            /* max-width: 1280px; */
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        @media (min-width: 640px) {
            .section-pad {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        .font-mono-data {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: hsl(var(--primary));
        }
        .btn-engine {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 2px;
            font-family: var(--font-heading);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-engine:hover {
            box-shadow: 0 0 0 1px hsl(var(--primary)), 0 0 24px -4px hsl(var(--primary) / 0.7);
            transform: translateY(-1px);
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: hsl(var(--background));
        }
        ::-webkit-scrollbar-thumb {
            background: hsl(var(--muted));
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: hsl(var(--border));
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-background text-foreground antialiased selection:bg-primary selection:text-white">
    <!-- Header / Navbar -->
    @include('components.navbar')

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Spacer for mobile bottom bar -->
    <div class="h-14 lg:hidden" aria-hidden="true"></div>

    <!-- Mobile Sticky Bottom Bar -->
    @include('components.mobile-bottom-bar')

    @stack('scripts')
</body>
</html>
