<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-zinc-50">
<div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-zinc-900">
        <img
            src="{{ asset('brown.jpg') }}"
            alt="Campus"
            class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative z-10 flex flex-col justify-between p-12 w-full">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 flex items-center justify-center">
                    <img
                        src="{{ asset('Brown-Logo.png') }}"
                        alt="Student Management Logo"
                        class="w-full h-full object-contain">
                </div>
                <span class="text-xl font-bold tracking-tight text-white">Student Management</span>
            </div>
            <div class="max-w-md">
                <h2 class="text-4xl font-bold text-white leading-tight mb-6">
                    Empowering education through seamless management.
                </h2>
                <p class="text-zinc-300 text-lg">
                    Manage student records with a modern Laravel + Livewire dashboard.
                </p>
            </div>
            <div class="flex items-center gap-4 text-zinc-400 text-sm">
                <span>© {{ date('Y') }} Student Management Portal</span>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 lg:p-24">
        <div class="w-full max-w-md">
            <div class="lg:hidden flex flex-col items-center mb-12">
                <div class="w-16 h-16 bg-zinc-900 rounded-2xl flex items-center justify-center mb-4 shadow-xl">
                        <img src="{{ asset('Brown-Logo.png') }}" alt="Student Management Logo" class="w-10 h-10 object-contain">
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Student Management</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-bold text-zinc-900 mb-2">Welcome back</h2>
                <p class="text-zinc-600">Please enter your details to sign in.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-100 p-4 rounded-xl text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-zinc-600 uppercase tracking-widest ml-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@example.com') }}"
                           required
                           class="w-full px-4 py-4 bg-white border border-zinc-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-zinc-900/5 focus:border-zinc-900 text-zinc-900 shadow-sm"
                           placeholder="admin@example.com">
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center px-1">
                        <label for="login-password" class="block text-xs font-bold text-zinc-600 uppercase tracking-widest">Password</label>
                    </div>
                    <div class="relative">
                        <input id="login-password" type="password" name="password"
                               required
                               autocomplete="current-password"
                               class="w-full px-4 py-4 pr-12 bg-white border border-zinc-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-zinc-900/5 focus:border-zinc-900 text-zinc-900 shadow-sm"
                               placeholder="••••••••"
                               value="password123">
                        <button type="button" id="toggle-login-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-900/20"
                                aria-label="Show password"
                                aria-controls="login-password"
                                aria-pressed="false">
                            <span class="toggle-password-show" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <span class="toggle-password-hide hidden" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-zinc-900 text-white py-4 rounded-2xl font-bold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-xl shadow-zinc-900/10 flex items-center justify-center gap-2">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    var input = document.getElementById('login-password');
    var btn = document.getElementById('toggle-login-password');
    if (!input || !btn) return;
    var showIcon = btn.querySelector('.toggle-password-show');
    var hideIcon = btn.querySelector('.toggle-password-hide');
    btn.addEventListener('click', function () {
        var visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
        btn.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        if (showIcon && hideIcon) {
            showIcon.classList.toggle('hidden', !visible);
            hideIcon.classList.toggle('hidden', visible);
        }
    });
})();
</script>
</body>
</html>

