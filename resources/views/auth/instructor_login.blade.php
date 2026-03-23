<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instructor Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-zinc-50">
<div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-zinc-900">
        <img
            src="{{ asset('brown.jpg') }}"
            alt="Background"
            class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/50"></div>

        <div class="relative z-10 flex flex-col justify-between p-12 w-full">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 overflow-hidden rounded-xl flex items-center justify-center shadow-lg bg-white">
                    <img
                        src="{{ asset('Brown-Logo.png') }}"
                        alt="Student Admin Logo"
                        class="w-full h-full object-contain">
                </div>
                <span class="text-xl font-bold tracking-tight text-white">Instructor Portal</span>
            </div>

            <div class="max-w-md">
                <h2 class="text-4xl font-bold text-white leading-tight mb-6">
                    Enter grades and advise students.
                </h2>
                <p class="text-zinc-300 text-lg">
                    Secure access for instructors.
                </p>
            </div>

            <div class="flex items-center gap-4 text-zinc-400 text-sm">
                <span>© {{ date('Y') }} Instructor Portal</span>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 lg:p-24">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex flex-col items-center mb-12">
                <div class="w-16 h-16 bg-zinc-900 rounded-2xl flex items-center justify-center mb-4 shadow-xl">
                    <img src="{{ asset('Brown-Logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Instructor Portal</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-zinc-900 mb-2">Welcome back</h2>
                <p class="text-zinc-600">Please sign in to manage grades.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-100 p-4 rounded-xl text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('instructor.login.attempt') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-zinc-600 uppercase tracking-widest ml-1">Email Address</label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           required
                           class="w-full px-4 py-4 bg-white border border-zinc-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-zinc-900/5 focus:border-zinc-900 text-zinc-900 shadow-sm"
                           placeholder="instructor@example.com">
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label class="block text-xs font-bold text-zinc-600 uppercase tracking-widest">Password</label>
                    </div>
                    <input type="password" name="password"
                           required
                           class="w-full px-4 py-4 bg-white border border-zinc-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-zinc-900/5 focus:border-zinc-900 text-zinc-900 shadow-sm"
                           placeholder="••••••••">
                </div>

                <button type="submit"
                        class="w-full bg-zinc-900 text-white py-4 rounded-2xl font-bold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-xl shadow-zinc-900/10">
                    Sign In
                </button>

                <div class="text-center text-sm">
                    <a href="{{ route('login') }}" class="text-zinc-500 hover:text-zinc-900">Admin login</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

