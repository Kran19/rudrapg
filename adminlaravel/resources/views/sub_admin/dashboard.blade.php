@extends('layouts.admin')

@section('title', 'Sub Admin Dashboard - Naroda Branch')
@section('page_title', 'Naroda Branch Operational Dashboard')

@section('content')
<!-- Key Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <!-- Branch Occupancy Rate Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Occupancy Rate</p>
                <h3 id="db-occupancy-rate" class="text-2xl font-extrabold text-slate-900 dark:text-white mt-2">{{ $branchInfo['occupancy_rate'] }}</h3>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-bed text-xl"></i>
            </div>
        </div>
        <div id="db-occupied-beds-label" class="mt-4 text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/40 dark:text-emerald-400 w-fit px-2.5 py-1 rounded-full">
            🟢 {{ $branchInfo['occupied_beds'] }} / {{ $branchInfo['total_beds'] }} Beds Occupied
        </div>
    </div>

    <!-- Pending Verifications Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pending Verifications</p>
                <h3 id="db-pending-verifications" class="text-2xl font-extrabold text-amber-600 mt-2">{{ $branchInfo['pending_verifications'] }}</h3>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-user-check text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-amber-700 bg-amber-50 dark:bg-amber-900/40 dark:text-amber-400 w-fit px-2.5 py-1 rounded-full">
            KYC & Payment Proof Queue
        </div>
    </div>

    <!-- Overdue Rent Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Overdue Rent Dues</p>
                <h3 id="db-overdue-rents" class="text-2xl font-extrabold text-rose-600 mt-2">{{ $branchInfo['overdue_rents'] }}</h3>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-rose-700 bg-rose-50 dark:bg-rose-900/40 dark:text-rose-400 w-fit px-2.5 py-1 rounded-full">
            Requires Collection Action
        </div>
    </div>

    <!-- Monthly Revenue Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Monthly Collections</p>
                <h3 id="db-monthly-revenue" class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">{{ $branchInfo['monthly_revenue'] }}</h3>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 w-fit px-2.5 py-1 rounded-full">
            This Month's Realized Revenue
        </div>
    </div>
</div>

<!-- Charts & Process Workflow Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Student Onboarding workflow guide card (2/3 width) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-route text-blue-600"></i> Student Lifecycle Onboarding Process
                </h3>
                <span class="text-[10px] bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">Operational Map</span>
            </div>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                Onboarding new residents follows a structured 3-step pipeline. Complete audits sequentially to activate their residency.
            </p>

            <!-- Onboarding Stepper -->
            <div class="relative pl-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-6">
                <!-- Step 1 -->
                <div class="relative">
                    <span class="absolute -left-[31px] top-0.5 bg-blue-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold ring-4 ring-white dark:ring-slate-800">1</span>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">KYC & Document Review</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Verify candidate identification proofs (Aadhaar & PAN cards).</p>
                        </div>
                        <a href="{{ route('sub_admin.verifications') }}" class="sm:self-center bg-slate-100 hover:bg-blue-600 hover:text-white dark:bg-slate-700 dark:hover:bg-blue-600 text-slate-700 dark:text-slate-200 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-clipboard-check"></i> Audit KYC
                        </a>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative">
                    <span class="absolute -left-[31px] top-0.5 bg-amber-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold ring-4 ring-white dark:ring-slate-800">2</span>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Bed & Room Allocation</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Assign a vacant bed based on their package selection.</p>
                        </div>
                        <a href="{{ route('sub_admin.bed_map') }}" class="sm:self-center bg-slate-100 hover:bg-amber-600 hover:text-white dark:bg-slate-700 dark:hover:bg-amber-600 text-slate-700 dark:text-slate-200 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-bed"></i> Assign Bed
                        </a>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative">
                    <span class="absolute -left-[31px] top-0.5 bg-emerald-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold ring-4 ring-white dark:ring-slate-800">3</span>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Rent Payment Validation</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Verify rent/deposit payment proof to mark the resident as Active.</p>
                        </div>
                        <a href="{{ route('sub_admin.rent_ledger') }}" class="sm:self-center bg-slate-100 hover:bg-emerald-600 hover:text-white dark:bg-slate-700 dark:hover:bg-emerald-600 text-slate-700 dark:text-slate-200 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Verify Dues
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doughnut Occupancy Breakdown (1/3 width) -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs flex flex-col justify-between">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Occupancy Breakdown</h3>
        <div class="relative h-44 flex items-center justify-center">
            <canvas id="branchOccupancyDoughnut"></canvas>
        </div>
        <div class="flex justify-center gap-4 mt-4 text-xs">
            <span class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-semibold">
                <span class="w-3 h-3 bg-emerald-500 rounded-full inline-block"></span> Occupied (<span id="db-breakdown-occupied">{{ $branchInfo['occupied_beds'] }}</span>)
            </span>
            <span class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-semibold">
                <span id="available-indicator-dot" class="w-3 h-3 bg-slate-200 dark:bg-slate-700 rounded-full inline-block"></span> Available (<span id="db-breakdown-available">{{ $branchInfo['available_beds'] }}</span>)
            </span>
        </div>
    </div>
</div>

<!-- Revenue Collection Analytics -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Monthly Collection Performance</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Target expected revenue vs actual payments collected over the last 6 months.</p>
        </div>
        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-3 py-1.5 rounded-lg">Last 6 Months</span>
    </div>
    <div class="relative h-64">
        <canvas id="collectionsBarChart"></canvas>
    </div>
</div>

<!-- Tabulator Verification Preview Table -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-600"></i> Pending Student Verification Queue
        </h3>
        <a href="{{ route('sub_admin.verifications') }}" 
           class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500 dark:hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            Open Full Verification Desk
        </a>
    </div>
    <div id="subadmin-dashboard-table"></div>
</div>
@endsection

@section('scripts')
<script>
    // Tabulator Verification Queue Table
    var queueData = @json($pendingVerifications);

    var table = new Tabulator("#subadmin-dashboard-table", {
        data: queueData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No Verification Requests Pending",
        columns: [
            {title: "Booking ID", field: "id", width: 140, formatter: function(cell){
                return "<code class='bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone", field: "phone"},
            {title: "Allocated Spot", field: "room", formatter: function(cell){
                return '<span class="bg-slate-900 text-white text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Monthly Rent", field: "rent"},
            {title: "Security Deposit", field: "deposit"},
            {title: "Submission Date", field: "date"},
            {title: "Action", field: "id", width: 140, formatter: function(cell){
                return '<a href="{{ route("sub_admin.verifications") }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1 w-fit"><i class="fa-solid fa-eye"></i> Review</a>';
            }},
        ]
    });

    // Chart.js - Occupancy Doughnut
    const occupied = {{ $branchInfo['occupied_beds'] }};
    const available = {{ $branchInfo['available_beds'] }};
    const isDark = document.documentElement.classList.contains('dark');
    const availableColor = isDark ? '#334155' : '#E2E8F0';
    
    const doughnutCtx = document.getElementById('branchOccupancyDoughnut').getContext('2d');
    window.occupancyDoughnut = new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Available'],
            datasets: [{
                data: [occupied, available],
                backgroundColor: ['#10B981', availableColor],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '72%'
        }
    });

    // Chart.js - Collections Bar Chart
    const trendData = @json($collectionsTrend);
    const labels = trendData.map(item => item.month);
    const targets = trendData.map(item => item.target);
    const collected = trendData.map(item => item.collected);

    const barCtx = document.getElementById('collectionsBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target Collections (₹)',
                    data: targets,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                },
                {
                    label: 'Actual Collected (₹)',
                    data: collected,
                    backgroundColor: '#10B981',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20
                    }
                }
            }
        }
    });
</script>
@endsection
