<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'North Fork Automotive | Reliable Auto Repair in Talkeetna, Alaska')</title>
    <meta name="description" content="@yield('description', 'North Fork Automotive provides honest, high-quality automotive repair, diagnostics, and maintenance in Talkeetna, Alaska.')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/app.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#EBF7FD',
                        foreground: '#071F35',
                        navy: {
                            DEFAULT: '#071F35',
                            surface: '#0B2B47',
                            card: '#0E3557',
                            deep: '#041321'
                        },
                        icy: {
                            DEFAULT: '#BDEBFA',
                            light: '#E6F8FE',
                            glow: 'rgba(189, 235, 250, 0.45)'
                        },
                        sky: {
                            DEFAULT: '#D8F1FA',
                            soft: '#EEF9FD'
                        },
                        ocean: {
                            DEFAULT: '#087DB5',
                            hover: '#0A92D4',
                            dark: '#065D88'
                        }
                    },
                    fontFamily: {
                        heading: ['Sora', 'system-ui', 'sans-serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Navbar -->
    @include('components.navbar')

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Chatbot Component -->
    @include('components.chatbot')

    @stack('scripts')
</body>
</html>
