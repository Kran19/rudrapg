<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false, profileDropdownOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rudra Group PG Admin Portal')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
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

        .tabulator-row:hover {
            background-color: #F8FAFC !important;
        }

        .tabulator-cell {
            padding: 14px 16px !important;
            vertical-align: middle !important;
        }

        .tabulator-footer {
            background-color: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            padding: 10px 16px !important;
        }

        .tabulator-page {
            border-radius: 0.5rem !important;
            padding: 4px 12px !important;
            margin: 0 2px !important;
            border: 1px solid #CBD5E1 !important;
            font-weight: 500;
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
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">

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
                        <i class="fa-solid fa-crown text-amber-400"></i> SUPER ADMIN VIEW
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
                    <span class="bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">3</span>
                </a>
                <a href="{{ route('sub_admin.bed_map') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.bed_map') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-bed w-5 text-center"></i> Bed Allocation Grid
                </a>
                <a href="{{ route('sub_admin.rent_ledger') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.rent_ledger') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Rent Collection Dues
                </a>
                <a href="{{ route('sub_admin.electricity_audit') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.electricity_audit') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-bolt w-5 text-center"></i> Electricity Meter Audit
                </a>
                <a href="{{ route('sub_admin.complaints') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('sub_admin.complaints') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-headset w-5 text-center"></i> Complaints & Notices
                </a>
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
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.students') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i> Student Directory
                </a>
                <a href="{{ route('super_admin.finance') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.finance') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-wallet w-5 text-center"></i> Financial & Revenue Hub
                </a>
                <a href="{{ route('super_admin.settings') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl font-medium text-sm transition-all duration-200 {{ request()->routeIs('super_admin.settings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-sliders w-5 text-center"></i> Audit Logs & Settings
                </a>
            @endif
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-500">© 2026 Rudra Group PG v2.4</p>
        </div>
    </aside>

    <!-- Main Content Area Container -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 lg:px-8 py-3.5 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
            </div>

            <!-- Role Switcher & User Action Controls -->
            <div class="flex items-center gap-3">
                <!-- Role Switcher Pill -->
                <div class="bg-slate-100 p-1 rounded-full border border-slate-200/80 flex items-center">
                    <a href="{{ route('super_admin.dashboard') }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ !request()->is('sub-admin*') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        👑 Super Admin
                    </a>
                    <a href="{{ route('sub_admin.dashboard') }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 {{ request()->is('sub-admin*') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        🛡️ Sub Admin
                    </a>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" 
                            class="flex items-center gap-2 p-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            A
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 px-1"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-900">Administrator Desk</p>
                            <p class="text-xs text-slate-500">admin@rudrapg.com</p>
                        </div>
                        <a href="#" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-circle-user text-slate-400"></i> Profile Settings
                        </a>
                        <a href="#" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-shield-halved text-slate-400"></i> Security Credentials
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button onclick="confirmLogout()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-medium">
                            <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Body Page Content -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // Global SweetAlert2 Logout Confirmation
    function confirmLogout() {
        Swal.fire({
            title: 'Confirm Logout?',
            text: "Are you sure you want to end your current admin session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F172A',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Yes, Sign Out'
        }).then((result) => {
            if (result.isConfirmed) {
                toastr.success('Successfully logged out.');
                setTimeout(() => window.location.reload(), 1000);
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
</script>

@yield('scripts')

</body>
</html>
