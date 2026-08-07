<!DOCTYPE html>
<html lang="en" x-data="{ showPassword: false, activePreset: 'super' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Rudra Group PG Admin Portal</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Poppins', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Decorative Glow Effect -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-8 shadow-2xl relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-blue-600 to-indigo-500 text-white rounded-2xl shadow-lg shadow-blue-500/30 mb-4">
                <i class="fa-solid fa-building-user text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Rudra Group PG</h1>
            <p class="text-xs text-slate-400 mt-1">Enterprise Admin & Operational Portal</p>
        </div>

        <!-- Role Quick Preset Selector Buttons -->
        <div class="bg-slate-900/80 p-1.5 rounded-2xl border border-slate-700/60 flex items-center mb-6">
            <button type="button" 
                    @click="activePreset = 'super'; document.getElementById('email').value = 'admin@rudrapg.com'; document.getElementById('password').value = 'password'"
                    :class="activePreset === 'super' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                    class="flex-1 py-2 text-xs font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-crown text-amber-400"></i> Super Admin
            </button>
            <button type="button" 
                    @click="activePreset = 'sub'; document.getElementById('email').value = 'subadmin.naroda@rudrapg.com'; document.getElementById('password').value = 'password'"
                    :class="activePreset === 'sub' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-white'"
                    class="flex-1 py-2 text-xs font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-cyan-400"></i> Sub Admin
            </button>
        </div>

        <!-- Error Notification Alert -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </span>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', 'admin@rudrapg.com') }}" 
                           required 
                           autofocus
                           placeholder="admin@rudrapg.com"
                           class="w-full pl-10 pr-4 py-3 bg-slate-900/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input :type="showPassword ? 'text' : 'password'" 
                           id="password" 
                           name="password" 
                           value="password" 
                           required
                           placeholder="••••••••"
                           class="w-full pl-10 pr-11 py-3 bg-slate-900/60 border border-slate-700/80 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <button type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200">
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-500">
                    <span>Remember session</span>
                </label>
                <span class="text-slate-500 italic">Default password: password</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In to Portal
            </button>
        </form>

        <!-- Download Android App Button -->
        <div class="mt-6">
            <a href="{{ asset('downloads/student-app.apk') }}" download 
               class="w-full py-3 px-4 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold text-sm rounded-xl border border-slate-600 transition-all flex items-center justify-center gap-2">
                <i class="fa-brands fa-android text-emerald-400"></i> Download Student App (APK)
            </a>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-700/50 text-center text-xs text-slate-500">
            © 2026 Rudra Group PG Management System v2.4
        </div>
    </div>

</body>
</html>
