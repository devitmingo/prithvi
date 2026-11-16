<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login - Prithvi CRM</title>
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
<body class="h-full min-h-screen flex items-center justify-center p-3 sm:p-4 relative overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-100">

    <!-- Atmospheric Glow Backgrounds -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-200/50 rounded-full blur-[128px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-200/40 rounded-full blur-[128px] pointer-events-none"></div>

    <div class="w-full max-w-[420px] relative z-10 flex flex-col justify-center">

        <!-- Header / Logo -->
        <div class="text-center mb-4">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white p-1.5 shadow-md shadow-slate-200/80 mb-2 ring-1 ring-amber-400/80 transition-transform hover:scale-105 border border-slate-100">
                <img src="{{ asset('images/prithvi-logo.png') }}" alt="Prithvi Buildcon" class="w-full h-full object-contain">
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none">PRITHVI BUILDCON</h1>
            <p class="text-[11px] text-amber-600 mt-1 font-extrabold tracking-wider uppercase flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-crown text-[10px]"></i>
                <span>Super Admin Portal</span>
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-xl shadow-slate-200/50">
            
            <div class="mb-4 text-center sm:text-left">
                <h2 class="text-base font-extrabold text-slate-900">Sign In to Dashboard</h2>
                <p class="text-[11px] text-slate-500">Super Administrator Access Only</p>
            </div>

            <!-- Error Alerts -->
            @if(session('error'))
                <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation shrink-0 text-sm text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-check shrink-0 text-sm text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-3 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-0.5">
                    @foreach($errors->all() as $err)
                        <p class="flex items-center gap-1.5"><i class="fa-solid fa-circle-xmark text-xs text-rose-600"></i> {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-3">
                @csrf

                <!-- Login Input (Email or Mobile) -->
                <div>
                    <label for="login" class="block text-[11px] font-bold text-slate-700 mb-1">Email or 10-digit Mobile</label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user-shield text-xs"></i>
                        </div>
                        <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                               placeholder="e.g. superadmin@gmail.com"
                               class="w-full pl-9 pr-3.5 h-10 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-[11px] font-bold text-slate-700 mb-1">Password</label>
                    <div class="relative rounded-xl shadow-2xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               placeholder="••••••••"
                               class="w-full pl-9 pr-9 h-10 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition-colors">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none">
                            <i id="eye-icon" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Single Line Captcha Verification -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="captcha" class="block text-[11px] font-bold text-slate-700">
                            Security Captcha <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-emerald-700 font-semibold">Case-insensitive</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Captcha Image -->
                        <div class="h-10 w-28 rounded-xl overflow-hidden border border-slate-200 bg-slate-950 flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" onclick="refreshCaptcha()" title="Click to refresh captcha">
                            <img id="captchaImg" src="{{ route('captcha') }}" alt="Captcha" class="h-full w-full object-cover">
                        </div>
                        <!-- Refresh Icon Button -->
                        <button type="button" onclick="refreshCaptcha()" class="h-10 w-9 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center transition-colors shrink-0" title="Refresh security code">
                            <i id="captchaRefreshIcon" class="fa-solid fa-arrows-rotate text-emerald-600 text-xs"></i>
                        </button>
                        <!-- Code Input in the same single line -->
                        <div class="relative flex-1 min-w-0">
                            <input type="text" name="captcha" id="captcha" required autocomplete="off" maxlength="10"
                                   placeholder="Enter code"
                                   class="w-full px-3 h-10 bg-slate-50/70 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-xs tracking-wider uppercase font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Remember Me & Security Badge -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-3.5 h-3.5 rounded border-slate-300 bg-white text-brand-600 focus:ring-brand-500">
                        <span class="text-[11px] text-slate-600 font-medium">Remember me</span>
                    </label>
                    <span class="text-[10px] text-emerald-700 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-shield-halved text-[9px]"></i> Protected
                    </span>
                </div>

                <!-- Submit Button -->
                <div class="pt-1">
                    <button type="submit" class="w-full h-11 flex items-center justify-center gap-2 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-500/25 transition-all cursor-pointer">
                        <span>Access Admin Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </form>

        </div>

        <p class="text-center text-[11px] text-slate-400 mt-3 font-medium">
            &copy; {{ date('Y') }} Prithvi CRM &bull; Super Admin Portal
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
    </script>
</body>
</html>