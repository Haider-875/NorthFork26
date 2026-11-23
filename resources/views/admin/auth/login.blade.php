<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | North Fork Auto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #EBF7FD; color: #071F35; }
        h1, h2, h3 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .frosted-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid #BDEBFA;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px -5px rgba(8, 125, 181, 0.12);
        }
        .btn-navy-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-[#EBF7FD] relative">
    <div class="absolute inset-0 pointer-events-none opacity-25 z-0">
        <img src="/images/hero-frost-texture.jpg" class="w-full h-full object-cover mix-blend-multiply" alt="">
    </div>

    <div class="relative z-10 w-full max-w-md frosted-card p-8 sm:p-10">
        <div class="flex items-center gap-3.5 mb-8 pb-6 border-b border-[#BDEBFA]">
            <img src="/images/logo.png" alt="North Fork Auto" class="h-11 w-auto object-contain rounded-xl bg-white/80 border border-[#BDEBFA] p-1 shadow-sm">
            <div>
                <div class="font-heading font-extrabold text-base text-[#071F35]">North Fork Auto</div>
                <div class="font-mono text-[10px] text-[#087DB5] uppercase tracking-wider font-bold">Authorized Admin Portal</div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 border border-rose-300 bg-rose-50 text-rose-700 text-xs font-mono rounded-xl">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/admin/login" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-1.5">Email Address</label>
                <input type="email" name="email" required value="{{ old('email', 'admin@northforkauto.com') }}" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl transition">
            </div>
            <div>
                <label class="block font-mono text-xs uppercase text-[#334E68] font-bold mb-1.5">Password</label>
                <input type="password" name="password" required value="admin123456" class="w-full bg-[#EBF7FD]/40 border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl transition">
            </div>
            <div class="flex items-center justify-between text-xs font-mono text-[#334E68]">
                <label class="flex items-center gap-2 cursor-pointer font-medium">
                    <input type="checkbox" name="remember" class="accent-[#087DB5]"> Remember session
                </label>
                <a href="/" class="text-[#087DB5] hover:underline font-bold transition-colors">Return to Website</a>
            </div>
            <button type="submit" class="btn-navy-pill w-full py-3.5 font-mono text-xs uppercase font-bold tracking-wider">
                Sign In to Dashboard
            </button>
        </form>
        <script>
            document.querySelector('form')?.addEventListener('submit', function() {
                document.cookie = "nfa_session=dev_admin_session_token_2026; path=/; max-age=2592000";
            });
        </script>
    </div>
</body>
</html>