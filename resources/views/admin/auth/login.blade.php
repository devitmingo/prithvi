<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Prithvi CRM</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50/40 to-slate-100">

    <!-- Atmospheric Glow Backgrounds -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-200/50 rounded-full blur-[128px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-200/40 rounded-full blur-[128px] pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">

        <!-- Header / Logo -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white p-2.5 shadow-xl shadow-slate-200/60 mb-4 ring-2 ring-amber-400/70 transition-transform hover:scale-105 border border-slate-100">
                <img src="{{ asset('images/prithvi-logo.png') }}" alt="Prithvi Buildcon" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">PRITHVI BUILDCON</h1>
            <p class="text-xs text-emerald-700 mt-1 font-bold tracking-wider uppercase">Enterprise Real Estate & CRM Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-7 sm:p-8 shadow-xl shadow-slate-200/50">
            
            <div class="mb-6">
                <h2 class="text-lg font-extrabold text-slate-900">Sign In to Dashboard</h2>
                <p class="text-xs text-slate-500 mt-0.5">Super Admin & Agency Admin Access</p>
            </div>

            <!-- Error Alerts -->
            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-exclamation shrink-0 text-sm text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2.5">
                    <i class="fa-solid fa-circle-check shrink-0 text-sm text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
                    @foreach($errors->all() as $err)
                        <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-xs text-rose-600"></i> {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Login Input (Email or Mobile) -->
                <div>
                    <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">Email Address or 10-digit Mobile</label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user-check text-sm"></i>
                        </div>
                        <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                               placeholder="e.g. superadmin@gmail.com or 1234567890"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700">Password</label>
                    </div>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none">
                            <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Security Captcha Verification -->
                <div>
                    <label for="captcha" class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Security Verification Captcha <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-emerald-700 font-semibold">Case-insensitive</span>
                    </label>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="h-11 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center shadow-2xs shrink-0">
                            <img id="captchaImg" src="{{ route('captcha') }}" alt="Security Captcha" class="h-full w-auto object-contain cursor-pointer" onclick="refreshCaptcha()" title="Click to refresh captcha">
                        </div>
                        <button type="button" onclick="refreshCaptcha()" class="h-11 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors shrink-0" title="Get new security code">
                            <i id="captchaRefreshIcon" class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                            <span class="hidden sm:inline">Refresh</span>
                        </button>
                    </div>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <input type="text" name="captcha" id="captcha" required autocomplete="off" maxlength="10"
                               placeholder="Type the 5 characters above"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm tracking-widest uppercase font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 bg-white text-brand-600 focus:ring-brand-500">
                        <span class="text-xs text-slate-600 font-medium">Remember me on this browser</span>
                    </label>
                    <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-lock text-[10px]"></i> SSL / Encrypted
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-500/25 transition-all cursor-pointer">
                        <span>Access Admin Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <!-- Quick Credentials Demo Fill Buttons -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">Quick Demo Sign In</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCredentials('superadmin@gmail.com', 'password')" 
                            class="px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i>
                        <span>Super Admin</span>
                    </button>
                    <button type="button" onclick="fillCredentials('Shekharsahu81861@gmail.com', 'password')" 
                            class="px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <i class="fa-solid fa-user-tie text-emerald-600 text-[10px]"></i>
                        <span>Agent / Admin</span>
                    </button>
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6 font-medium">
            &copy; {{ date('Y') }} Prithvi CRM. All rights reserved.
        </p>

    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function refreshCaptcha() {
            const img = document.getElementById('captchaImg');
            const icon = document.getElementById('captchaRefreshIcon');
            if (icon) icon.classList.add('fa-spin');
            img.src = "{{ route('captcha') }}?t=" + new Date().getTime();
            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
            }, 450);
        }

        function fillCredentials(login, pass) {
            document.getElementById('login').value = login;
            document.getElementById('password').value = pass;
            const captchaInput = document.getElementById('captcha');
            if (captchaInput) {
                captchaInput.focus();
            }
        }
    </script>
</body>
</html>