<!DOCTYPE html>
<html lang="en" 
      x-data="{ 
          sidebarOpen: false, 
          profileDropdownOpen: false, 
          notificationOpen: false,
          searchModalOpen: false,
          darkMode: localStorage.getItem('theme') === 'dark' 
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rudra Group PG Admin Portal')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            900: '#0f172a',
                        },
                        slate: {
                            850: '#172033',
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
    
    <!-- Google Fonts Poppins & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Pro/Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Alpine.js v3 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 v11 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastr Notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Tabulator.js Core & Tailwind Compatible Styling -->
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator_simple.min.css" rel="stylesheet">
    <script src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    
    <script>
        window.APP_BASE_URL = "{{ url('/') }}";
        window.appUrl = function(path) {
            return window.APP_BASE_URL.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
        };
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Tabulator Pure Tailwind Styling Overrides */
        .tabulator {
            border-radius: 1rem;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            background-color: #FFFFFF;
            font-size: 0.875rem;
            overflow: hidden;
        }

        .dark .tabulator {
            background-color: #0F172A !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
        }

        .tabulator .tabulator-header {
            background-color: #0F172A !important;
            color: #FFFFFF !important;
            font-weight: 600;
            border-bottom: 2px solid #1E293B !important;
        }

        .tabulator .tabulator-header .tabulator-col {
            background-color: #0F172A !important;
            color: #FFFFFF !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
            padding: 12px 16px !important;
        }

        .tabulator-row {
            border-bottom: 1px solid #F1F5F9 !important;
            transition: background-color 0.15s ease;
        }

        .dark .tabulator-row {
            background-color: #0F172A !important;
            border-bottom-color: #1E293B !important;
            color: #F8FAFC !important;
        }

        .dark .tabulator-row.tabulator-row-even {
            background-color: #162032 !important;
        }

        .tabulator-row:hover {
            background-color: #F8FAFC !important;
        }

        .dark .tabulator-row:hover {
            background-color: #1E293B !important;
        }

        .tabulator-cell {
            padding: 14px 16px !important;
            vertical-align: middle !important;
        }

        .dark .tabulator-cell {
            color: #F8FAFC !important;
        }

        .tabulator-footer {
            background-color: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            padding: 10px 16px !important;
        }

        .dark .tabulator-footer {
            background-color: #0F172A !important;
            border-top-color: #334155 !important;
        }

        .tabulator-page {
            border-radius: 0.5rem !important;
            padding: 4px 12px !important;
            margin: 0 2px !important;
            border: 1px solid #CBD5E1 !important;
            font-weight: 500;
        }

        .dark .tabulator-page {
            color: #F8FAFC !important;
            border-color: #334155 !important;
        }

        .dark .tabulator-page:not(.active):hover {
            background-color: #1E293B !important;
        }

        .tabulator-page.active {
            background-color: #2563EB !important;
            color: #FFFFFF !important;
            border-color: #2563EB !important;
        }

        /* Toastr Custom Design */
        #toast-container > div {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important;
            opacity: 0.98 !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        .dark ::-webkit-scrollbar-track {
            background: #0F172A;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

<div class="flex min-h-screen relative">
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 z-50 flex flex-col transition-transform duration-300 ease-in-out border-r border-slate-800 shadow-xl lg:shadow-none">
        
        <!-- Brand Header -->
        <div class="p-5 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 text-white p-2.5 rounded-xl shadow-md shadow-blue-500/20">
                    <i class="fa-solid fa-building-user text-xl"></i>
                </div>
                <div>
                    <h1 class="text-white font-bold text-base tracking-tight">RUDRA GROUP PG</h1>
                    <p class="text-blue-400 text-xs font-medium">Enterprise Admin Panel</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Role Badge Indicator -->
        <div class="px-4 py-3 border-b border-slate-800/60">
            <div class="bg-slate-800/70 border border-slate-700/50 rounded-xl p-2.5 text-center">
                <span class="text-xs font-semibold tracking-wider text-cyan-400 uppercase flex items-center justify-center gap-1.5">
                    @if(request()->is('sub-admin*'))
                        <i class="fa-solid fa-shield-halved text-cyan-400"></i> SUB ADMIN VIEW
                    @else
                        <i class="fa-solid fa-user-shield text-blue-400"></i> SUPER ADMIN VIEW
                    @endif
                </span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
            @if(request()->is('sub-admin*'))
                <!-- Sub Admin Menu -->
                <a href="{{ route('sub_admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i> Operational Dashboard
                </a>
                <a href="{{ route('sub_admin.verifications') }}" 
                   class="flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.verifications') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-user-check w-5 text-center"></i> Verification Desk
                    </span>
                    <span id="badge-verifications" class="{{ isset($pendingRegistrationCount) && $pendingRegistrationCount > 0 ? '' : 'hidden' }} bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingRegistrationCount ?? 0 }}</span>
                </a>
                <a href="{{ route('sub_admin.bed_map') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.bed_map') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-bed w-5 text-center"></i> Bed Allocation Grid
                </a>
                <a href="{{ route('sub_admin.rent_ledger') }}" 
                   class="flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.rent_ledger') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Rent Collection Dues
                    </span>
                    <span id="badge-rent" class="{{ isset($pendingPaymentCount) && $pendingPaymentCount > 0 ? '' : 'hidden' }} bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingPaymentCount ?? 0 }}</span>
                </a>
                <a href="{{ route('sub_admin.electricity_audit') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.electricity_audit') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-bolt w-5 text-center"></i> Electricity Meter Audit
                </a>
                <a href="{{ route('sub_admin.complaints') }}" 
                   class="flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.complaints') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-headset w-5 text-center"></i> Complaints & Notices
                    </span>
                    <span id="badge-complaints" class="{{ isset($pendingComplaintsCount) && $pendingComplaintsCount > 0 ? '' : 'hidden' }} bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingComplaintsCount ?? 0 }}</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-full pt-1.5 mt-1.5 border-t border-slate-800/60">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm text-rose-400 hover:text-white hover:bg-rose-900/30 transition-all duration-200">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Logout Account
                    </button>
                </form>
            @else
                <!-- Super Admin Menu -->
                <a href="{{ route('super_admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i> Executive Dashboard
                </a>
                <a href="{{ route('super_admin.branches') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.branches') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-network-wired w-5 text-center"></i> Branch Management
                </a>
                <a href="{{ route('super_admin.sub_admins') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.sub_admins') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-user-shield w-5 text-center"></i> Sub Admin Management
                </a>
                <a href="{{ route('super_admin.rooms_master') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.rooms_master') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-door-open w-5 text-center"></i> Master Room Matrix
                </a>
                <a href="{{ route('super_admin.students') }}" 
                   class="flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.students') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-users w-5 text-center"></i> Student Directory
                    </span>
                    <span id="badge-verifications-super" class="{{ isset($pendingRegistrationCount) && $pendingRegistrationCount > 0 ? '' : 'hidden' }} bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingRegistrationCount ?? 0 }}</span>
                </a>
                <a href="{{ route('super_admin.finance') }}" 
                   class="flex items-center justify-between px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.finance') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-wallet w-5 text-center"></i> Financial & Revenue Hub
                    </span>
                    <span id="badge-rent-super" class="{{ isset($pendingPaymentCount) && $pendingPaymentCount > 0 ? '' : 'hidden' }} bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingPaymentCount ?? 0 }}</span>
                </a>
                <a href="{{ route('super_admin.settings') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.settings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-sliders w-5 text-center"></i> Audit Logs & Settings
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-full pt-1.5 mt-1.5 border-t border-slate-800/60">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm text-rose-400 hover:text-white hover:bg-rose-900/30 transition-all duration-200">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Logout Account
                    </button>
                </form>
            @endif
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-500">© 2026 Rudra Group PG v2.4</p>
        </div>
    </aside>

    <!-- Main Content Area Container -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Sticky Navbar Header -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30 px-3 sm:px-6 py-2.5 flex items-center justify-between shadow-xs transition-colors duration-200">
            <!-- Left Side: Mobile Toggle, Breadcrumbs & Title -->
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl border border-slate-200 dark:border-slate-700 shrink-0">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div class="min-w-0">
                    <!-- Breadcrumbs Component -->
                    <nav class="hidden sm:flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-400 font-medium mb-0.5">
                        <span>Portal</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        <span>{{ request()->is('sub-admin*') ? 'Sub Admin' : 'Super Admin' }}</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 dark:text-blue-400 font-semibold truncate">@yield('page_title', 'Dashboard')</span>
                    </nav>
                    <h2 class="text-sm sm:text-base lg:text-lg font-bold text-slate-900 dark:text-white tracking-tight truncate max-w-[130px] sm:max-w-xs md:max-w-none">@yield('page_title', 'Dashboard')</h2>
                </div>
            </div>

            <!-- Center: Global Search Input Placeholder -->
            <div class="hidden md:flex items-center max-w-xs w-full">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" 
                           placeholder="Search resident, room, UTR... (Ctrl+K)" 
                           @keydown.window.prevent.ctrl.k="searchModalOpen = true"
                           class="w-full pl-9 pr-4 py-1.5 bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <!-- Right Side: Dark Mode Toggle, Notifications & Profile -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Dark Mode Toggle Button -->
                <button @click="darkMode = !darkMode" 
                        class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                        title="Toggle Light / Dark Mode">
                    <template x-if="!darkMode">
                        <i class="fa-solid fa-moon text-sm sm:text-base text-amber-500"></i>
                    </template>
                    <template x-if="darkMode">
                        <i class="fa-solid fa-sun text-sm sm:text-base text-amber-400"></i>
                    </template>
                </button>

                <!-- Notifications Bell Dropdown -->
                <div class="relative">
                    <button @click="notificationOpen = !notificationOpen" @click.away="notificationOpen = false"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors relative">
                        <i class="fa-solid fa-bell text-sm sm:text-base"></i>
                        <span id="badge-notifications-bell" class="{{ isset($systemNotifications) && count($systemNotifications) > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ isset($systemNotifications) ? count($systemNotifications) : 0 }}</span>
                    </button>

                    <!-- Notifications Drawer -->
                    <div x-show="notificationOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">System Alerts</span>
                            @if(isset($systemNotifications) && count($systemNotifications) > 0)
                                <span class="text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300 font-semibold px-2 py-0.5 rounded-full">{{ count($systemNotifications) }} New</span>
                            @endif
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
                            @if(isset($systemNotifications) && count($systemNotifications) > 0)
                                @foreach($systemNotifications as $notification)
                                    <a href="{{ $notification['link'] }}" class="p-3 block hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $notification['title'] }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $notification['time'] }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $notification['message'] }}</p>
                                    </a>
                                @endforeach
                            @else
                                <div class="p-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                    No new notifications.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Role Switcher Pill (Responsive) -->
                @auth
                    @if(auth()->user()->role === 'SUPER_ADMIN')
                        <div class="hidden sm:flex bg-slate-100 dark:bg-slate-700 p-1 rounded-full border border-slate-200 dark:border-slate-600 items-center">
                            <a href="{{ route('super_admin.dashboard') }}" 
                               class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold transition-all duration-200 {{ !request()->is('sub-admin*') ? 'bg-slate-900 text-white dark:bg-blue-600 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                                🛡️ Executive
                            </a>
                            <a href="{{ route('sub_admin.dashboard') }}" 
                               class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold transition-all duration-200 {{ request()->is('sub-admin*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                                🛡️ Sub Admin
                            </a>
                        </div>
                    @else
                        <div class="hidden sm:flex bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold items-center gap-1">
                            <i class="fa-solid fa-shield-halved"></i> Sub Admin Desk
                        </div>
                    @endif
                @endauth

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button @click="profileDropdownOpen = !profileDropdownOpen" @click.away="profileDropdownOpen = false" 
                            class="flex items-center gap-2 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 px-1"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileDropdownOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-700">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ auth()->check() ? auth()->user()->name : 'Administrator Desk' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ auth()->check() ? auth()->user()->email : 'admin@rudrapg.com' }}</p>
                        </div>
                        @if(auth()->check() && auth()->user()->role === 'SUPER_ADMIN')
                            <a href="{{ route('super_admin.settings') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60">
                                <i class="fa-solid fa-circle-user text-slate-400"></i> System Settings
                            </a>
                        @endif
                        <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        <button onclick="confirmLogout()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 font-medium">
                            <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Body Page Content -->
        <main class="flex-1 p-4 lg:p-8 w-full mx-auto">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // Global SweetAlert2 Logout Confirmation
    function confirmLogout() {
        Swal.fire({
            title: 'Confirm Sign Out?',
            text: "Are you sure you want to end your current session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F172A',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Yes, Sign Out'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    // Configure Toastr Options Globally
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Real-Time Sidebar & Notification Count Badges Update
    function updateSidebarBadges() {
        const isSubAdmin = window.location.pathname.includes('/sub-admin');
        const isSuperAdmin = window.location.pathname.includes('/super-admin');
        
        let endpoint = '';
        if (isSubAdmin) {
            endpoint = "{{ route('sub_admin.sidebar_counts') }}";
        } else if (isSuperAdmin) {
            endpoint = "{{ route('super_admin.sidebar_counts') }}";
        } else {
            return;
        }

        fetch(endpoint, {
            headers: {
                "Accept": "application/json"
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('API offline');
            return res.json();
        })
        .then(data => {
            // 1. Sub Admin Badges
            const regBadge = document.getElementById('badge-verifications');
            if (regBadge) {
                if (data.pending_registrations > 0) {
                    regBadge.textContent = data.pending_registrations;
                    regBadge.classList.remove('hidden');
                } else {
                    regBadge.classList.add('hidden');
                }
            }

            const paymentBadge = document.getElementById('badge-rent');
            if (paymentBadge) {
                if (data.pending_payments > 0) {
                    paymentBadge.textContent = data.pending_payments;
                    paymentBadge.classList.remove('hidden');
                } else {
                    paymentBadge.classList.add('hidden');
                }
            }

            const complaintBadge = document.getElementById('badge-complaints');
            if (complaintBadge) {
                if (data.pending_complaints > 0) {
                    complaintBadge.textContent = data.pending_complaints;
                    complaintBadge.classList.remove('hidden');
                } else {
                    complaintBadge.classList.add('hidden');
                }
            }

            // 2. Super Admin Badges
            const regBadgeSuper = document.getElementById('badge-verifications-super');
            if (regBadgeSuper) {
                if (data.pending_registrations > 0) {
                    regBadgeSuper.textContent = data.pending_registrations;
                    regBadgeSuper.classList.remove('hidden');
                } else {
                    regBadgeSuper.classList.add('hidden');
                }
            }

            const paymentBadgeSuper = document.getElementById('badge-rent-super');
            if (paymentBadgeSuper) {
                if (data.pending_payments > 0) {
                    paymentBadgeSuper.textContent = data.pending_payments;
                    paymentBadgeSuper.classList.remove('hidden');
                } else {
                    paymentBadgeSuper.classList.add('hidden');
                }
            }

            // 3. Header Notification Bell Badge
            const bellBadge = document.getElementById('badge-notifications-bell');
            if (bellBadge) {
                const totalAlerts = (data.pending_registrations || 0) + (data.pending_payments || 0) + (data.pending_complaints || 0);
                if (totalAlerts > 0) {
                    bellBadge.textContent = totalAlerts;
                    bellBadge.classList.remove('hidden');
                } else {
                    bellBadge.classList.add('hidden');
                }
            }

            // 4. Sub Admin Dashboard Dynamic KPI Updates
            const dbOccupancyRate = document.getElementById('db-occupancy-rate');
            if (dbOccupancyRate) {
                dbOccupancyRate.textContent = data.occupancy_rate;
            }
            const dbOccupiedBedsLabel = document.getElementById('db-occupied-beds-label');
            if (dbOccupiedBedsLabel) {
                dbOccupiedBedsLabel.textContent = `🟢 ${data.occupied_beds} / ${data.total_beds} Beds Occupied`;
            }
            const dbPendingVerifications = document.getElementById('db-pending-verifications');
            if (dbPendingVerifications) {
                dbPendingVerifications.textContent = data.pending_verifications;
            }
            const dbOverdueRents = document.getElementById('db-overdue-rents');
            if (dbOverdueRents) {
                dbOverdueRents.textContent = data.overdue_rents;
            }
            const dbMonthlyRevenue = document.getElementById('db-monthly-revenue');
            if (dbMonthlyRevenue) {
                dbMonthlyRevenue.textContent = data.monthly_revenue;
            }
            const dbBreakdownOccupied = document.getElementById('db-breakdown-occupied');
            if (dbBreakdownOccupied) {
                dbBreakdownOccupied.textContent = data.occupied_beds;
            }
            const dbBreakdownAvailable = document.getElementById('db-breakdown-available');
            if (dbBreakdownAvailable) {
                dbBreakdownAvailable.textContent = data.available_beds;
            }
            if (window.occupancyDoughnut) {
                window.occupancyDoughnut.data.datasets[0].data = [data.occupied_beds, data.available_beds];
                window.occupancyDoughnut.update();
            }
        })
        .catch(err => {
            console.debug('Badge sync deferred:', err.message);
        });
    }

    // Initialize and sync every 10 seconds
    updateSidebarBadges();
    setInterval(updateSidebarBadges, 10000);
</script>

@yield('scripts')

</body>
</html>
