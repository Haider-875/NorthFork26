<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | North Fork Auto</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Sora:wght@700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: 'hsl(240 6% 6%)',
                        card: 'hsl(240 5% 9%)',
                        primary: 'hsl(349 77% 50%)',
                        border: 'hsl(215 19% 22%)'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-background text-white flex items-center justify-center p-4">
    <div class="w-full max-w-md border border-border bg-card p-8">
        <div class="text-center mb-8">
            <span class="inline-flex items-center justify-center w-10 h-10 border-2 border-primary text-primary font-bold text-xl mb-3">N</span>
            <h1 class="text-2xl font-bold">North Fork Auto</h1>
            <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider font-mono">Staff &amp; Admin Portal</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 border border-red-500/40 bg-red-500/10 text-red-400 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/admin/login" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs uppercase font-bold text-gray-300 mb-2">Email Address</label>
                <input type="email" name="email" required value="{{ old('email', 'admin@northforkauto.com') }}" class="w-full bg-background border border-border px-4 py-3 text-sm focus:outline-none focus:border-primary">
            </div>
            <div>
                <label class="block text-xs uppercase font-bold text-gray-300 mb-2">Password</label>
                <input type="password" name="password" required value="admin123456" class="w-full bg-background border border-border px-4 py-3 text-sm focus:outline-none focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 text-sm uppercase tracking-wide transition">
                Sign In to Dashboard
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/" class="text-xs text-gray-400 hover:text-primary">← Return to Public Website</a>
        </div>
    </div>
</body>
</html>
