@extends('layouts.admin')

@section('title', 'Super Admin Dashboard - Rudra Group PG')
@section('page_title', 'Executive Master Dashboard')

@section('content')
<!-- Key Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Monthly Revenue</p>
                <h3 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-2">{{ $metrics['total_revenue'] }}</h3>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-indian-rupee-sign text-xl"></i>
            </div>
        </div>
        @if(isset($metrics['revenue_change']) && $metrics['revenue_change'] !== null)
            <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold {{ $metrics['revenue_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40' : 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/40' }} w-fit px-2.5 py-1 rounded-full">
                <i class="fa-solid fa-arrow-{{ $metrics['revenue_change'] >= 0 ? 'up' : 'down' }}"></i> {{ ($metrics['revenue_change'] >= 0 ? '+' : '') . $metrics['revenue_change'] }}% from last month
            </div>
        @else
            <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 w-fit px-2.5 py-1 rounded-full">
                <i class="fa-regular fa-calendar"></i> Current Month Realized
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Overall Occupancy Rate</p>
                <h3 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-2">{{ $metrics['occupancy_rate'] }}</h3>
            </div>
            <div class="bg-teal-50 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-chart-pie text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 w-fit px-2.5 py-1 rounded-full">
            {{ $metrics['occupied_beds'] }} / {{ $metrics['total_beds'] }} Beds Occupied
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Branches</p>
                <h3 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 mt-2">{{ $metrics['active_branches'] }}</h3>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-network-wired text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40 w-fit px-2.5 py-1 rounded-full">
            {{ $metrics['active_branches'] }} of {{ $metrics['total_branches'] }} {{ \Illuminate\Support\Str::plural('Branch', $metrics['total_branches']) }} Active
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pending Approvals</p>
                <h3 class="text-2xl font-extrabold {{ $metrics['pending_approvals'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-slate-100' }} mt-2">{{ $metrics['pending_approvals'] }}</h3>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 p-3.5 rounded-2xl">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold {{ $metrics['pending_approvals'] > 0 ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/40' : 'text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60' }} w-fit px-2.5 py-1 rounded-full">
            {{ $metrics['pending_approvals'] > 0 ? 'Requires Sub Admin Review' : 'All Requests Cleared' }}
        </div>
    </div>
</div>

<!-- Charts Analytics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Branch Monthly Revenue Analytics</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Actual collections per registered branch</p>
            </div>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-3 py-1.5 rounded-lg">Last 6 Months</span>
        </div>
        <div class="relative h-64">
            @if(count($branchRevenueDatasets) > 0)
                <canvas id="revenueChart"></canvas>
            @else
                <div class="h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                    <i class="fa-solid fa-chart-simple text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm font-medium">No active branches configured</p>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Occupancy Breakdown</h3>
        <div class="relative h-64 flex items-center justify-center">
            @if($metrics['total_beds'] > 0)
                <canvas id="occupancyDoughnut"></canvas>
            @else
                <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                    <i class="fa-solid fa-bed text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm font-medium">No rooms or beds configured</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Interactive Tabulator Audit Log Table -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-600"></i> Live Master Audit Trail (Tabulator.js)
        </h3>
        <input type="text" id="table-search" 
               class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" 
               placeholder="🔍 Search Audit Logs...">
    </div>
    <div id="audit-logs-table"></div>
</div>
@endsection

@section('scripts')
<script>
    var auditData = @json($recentActivities);
    
    var table = new Tabulator("#audit-logs-table", {
        data: auditData,
        layout: "fitColumns",
        responsiveLayout: "collapse",

        columns: [
            {title: "Time", field: "timestamp", width: 140},
            {title: "User / Role", field: "user", width: 220},
            {title: "Action Performed", field: "action", formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Details", field: "details"},
        ],
    });

    document.getElementById("table-search").addEventListener("keyup", function(){
        table.setFilter("action", "like", this.value);
    });

    // Branch Monthly Revenue Chart.js Setup
    @if(count($branchRevenueDatasets) > 0)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json($revenueMonths),
            datasets: @json($branchRevenueDatasets)
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
                    position: 'bottom'
                }
            }
        }
    });
    @endif

    // Occupancy Doughnut Setup
    @if($metrics['total_beds'] > 0)
    const occupiedCount = {{ (int) $metrics['occupied_beds'] }};
    const availableCount = {{ (int) $metrics['available_beds'] }};
    const isDark = document.documentElement.classList.contains('dark');
    const availableColor = isDark ? '#334155' : '#E2E8F0';

    const ctxDoughnut = document.getElementById('occupancyDoughnut').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Occupied (' + occupiedCount + ')', 'Available (' + availableCount + ')'],
            datasets: [{
                data: [occupiedCount, availableCount],
                backgroundColor: ['#10B981', availableColor],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%'
        }
    });
    @endif
</script>
@endsection
