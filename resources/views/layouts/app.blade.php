<!DOCTYPE html>
<html lang="en" class="scroll-smooth overflow-x-hidden max-w-full">
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
    <style>
        /* Header Initial State (Over Hero Banner) */
        header#main-header.navbar-top {
            background-color: transparent !important;
            background-image: none !important;
            border-bottom: 1px solid rgba(189, 235, 250, 0.45) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
        }

        /* Header Scrolled State (User's Ice Texture Background + Crisp Dark Navy Text) */
        header#main-header.navbar-scrolled {
            background-color: rgba(235, 247, 253, 0.92) !important;
            background-image: linear-gradient(rgba(240, 249, 254, 0.82), rgba(216, 241, 250, 0.88)), url('/images/hero-frost-texture.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(189, 235, 250, 0.9) !important;
            box-shadow: 0 4px 20px rgba(7, 31, 53, 0.10) !important;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased overflow-x-hidden max-w-full">
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

    <!-- North Fork Auto Gentle Winter Snow Falling Effect (100% Click-Through, Zero Layout Shift) -->
    <canvas id="nfa-snow-canvas" class="fixed inset-0 pointer-events-none select-none z-30" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; user-select: none; z-index: 30;"></canvas>
    <script>
    (function() {
        const canvas = document.getElementById('nfa-snow-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let width = window.innerWidth;
        let height = window.innerHeight;
        canvas.width = width;
        canvas.height = height;

        const isMobile = width < 768;
        const flakeCount = isMobile ? 35 : 75;
        const flakes = [];

        for (let i = 0; i < flakeCount; i++) {
            flakes.push({
                x: Math.random() * width,
                y: Math.random() * height,
                r: Math.random() * 2.2 + 0.8,
                speed: Math.random() * 0.75 + 0.45,
                opacity: Math.random() * 0.55 + 0.35,
                swing: Math.random() * 2,
                swingSpeed: Math.random() * 0.02 + 0.01
            });
        }

        let angle = 0;
        let animationFrameId = null;

        function drawSnow() {
            ctx.clearRect(0, 0, width, height);
            angle += 0.01;

            for (let i = 0; i < flakeCount; i++) {
                const f = flakes[i];
                f.y += f.speed;
                f.x += Math.sin(angle + f.swing) * 0.6;

                if (f.y > height) {
                    f.y = -5;
                    f.x = Math.random() * width;
                }
                if (f.x > width + 5) {
                    f.x = -5;
                } else if (f.x < -5) {
                    f.x = width + 5;
                }

                ctx.beginPath();
                ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2, false);
                ctx.fillStyle = 'rgba(255, 255, 255, ' + f.opacity + ')';
                ctx.fill();
            }

            if (!document.hidden) {
                animationFrameId = requestAnimationFrame(drawSnow);
            }
        }

        function handleResize() {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
        }

        window.addEventListener('resize', handleResize, { passive: true });
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = requestAnimationFrame(drawSnow);
            }
        });

        animationFrameId = requestAnimationFrame(drawSnow);
    })();
    </script>

    @stack('scripts')
</body>
</html>
