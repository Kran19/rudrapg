@extends('layouts.admin')

@section('title', 'Super Admin Dashboard - Rudra Group PG')
@section('page_title', 'Executive Master Dashboard')

@section('content')
<!-- Key Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Monthly Revenue</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $metrics['total_revenue'] }}</h3>
            </div>
            <div class="bg-blue-50 text-blue-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-indian-rupee-sign text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 w-fit px-2.5 py-1 rounded-full">
            <i class="fa-solid fa-arrow-up"></i> +12% from last month
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Overall Occupancy Rate</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $metrics['occupancy_rate'] }}</h3>
            </div>
            <div class="bg-teal-50 text-teal-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-chart-pie text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-slate-600 bg-slate-100 w-fit px-2.5 py-1 rounded-full">
            320 / 385 Beds Occupied
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Branches</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $metrics['active_branches'] }}</h3>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-network-wired text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-emerald-600 bg-emerald-50 w-fit px-2.5 py-1 rounded-full">
            All 4 Branches Active
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Approvals</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-2">{{ $metrics['pending_approvals'] }}</h3>
            </div>
            <div class="bg-rose-50 text-rose-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-rose-600 bg-rose-50 w-fit px-2.5 py-1 rounded-full">
            Requires Sub Admin Review
        </div>
    </div>
</div>

<!-- Charts Analytics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900">Branch Monthly Revenue Analytics</h3>
            <select class="text-xs font-medium bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none">
                <option>Year 2026</option>
                <option>Year 2025</option>
            </select>
        </div>
        <div class="relative h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4">Occupancy Breakdown</h3>
        <div class="relative h-64 flex items-center justify-center">
            <canvas id="occupancyDoughnut"></canvas>
        </div>
    </div>
</div>

<!-- Interactive Tabulator Audit Log Table -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-600"></i> Live Master Audit Trail (Tabulator.js)
        </h3>
        <input type="text" id="table-search" 
               class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" 
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
        pagination: "local",
        paginationSize: 5,
        columns: [
            {title: "Time", field: "timestamp", width: 140},
            {title: "User / Role", field: "user", width: 220},
            {title: "Action Performed", field: "action", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Details", field: "details"},
        ],
    });

    document.getElementById("table-search").addEventListener("keyup", function(){
        table.setFilter("action", "like", this.value);
    });

    // Chart.js Setup
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [
                { label: 'Naroda Branch', data: [5.2, 5.5, 5.8, 6.0, 6.1, 6.3, 6.5], backgroundColor: '#0F172A' },
                { label: 'Satellite Branch', data: [4.0, 4.2, 4.5, 4.8, 5.0, 5.2, 5.5], backgroundColor: '#2563EB' },
                { label: 'Prahlad Nagar', data: [4.5, 4.7, 5.0, 5.1, 5.3, 5.6, 5.8], backgroundColor: '#14B8A6' },
                { label: 'SG Highway', data: [6.0, 6.2, 6.4, 6.6, 6.8, 7.0, 7.2], backgroundColor: '#F59E0B' },
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    const ctxDoughnut = document.getElementById('occupancyDoughnut').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Occupied (320)', 'Available (65)'],
            datasets: [{ data: [320, 65], backgroundColor: ['#16A34A', '#E2E8F0'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endsection
