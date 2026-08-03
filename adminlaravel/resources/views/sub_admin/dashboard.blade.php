@extends('layouts.admin')

@section('title', 'Sub Admin Dashboard - Naroda Branch')
@section('page_title', 'Naroda Branch Operational Dashboard')

@section('content')
<!-- Key Metric Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-blue-600 border-t border-r border-b border-slate-200 shadow-xs">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Branch Occupancy</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $branchInfo['occupied_beds'] }} / {{ $branchInfo['total_beds'] }}</h3>
            </div>
            <div class="bg-blue-50 text-blue-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-bed text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-emerald-600 bg-emerald-50 w-fit px-2.5 py-1 rounded-full">
            🟢 {{ $branchInfo['available_beds'] }} Beds Available Left
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-amber-500 border-t border-r border-b border-slate-200 shadow-xs">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Verifications</p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-2">{{ $branchInfo['pending_verifications'] }}</h3>
            </div>
            <div class="bg-amber-50 text-amber-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-user-check text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-amber-700 bg-amber-50 w-fit px-2.5 py-1 rounded-full">
            KYC & Payment Proof Queue
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-rose-500 border-t border-r border-b border-slate-200 shadow-xs">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Overdue Rent Dues</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-2">{{ $branchInfo['overdue_rents'] }}</h3>
            </div>
            <div class="bg-rose-50 text-rose-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-rose-700 bg-rose-50 w-fit px-2.5 py-1 rounded-full">
            Requires Collection Action
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-cyan-500 border-t border-r border-b border-slate-200 shadow-xs">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Open Complaints</p>
                <h3 class="text-2xl font-extrabold text-cyan-600 mt-2">{{ $branchInfo['open_complaints'] }}</h3>
            </div>
            <div class="bg-cyan-50 text-cyan-600 p-3.5 rounded-2xl">
                <i class="fa-solid fa-headset text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs font-semibold text-cyan-700 bg-cyan-50 w-fit px-2.5 py-1 rounded-full">
            Branch Service Tickets
        </div>
    </div>
</div>

<!-- Tabulator Verification Preview Table -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-600"></i> Pending Student Verification Queue
        </h3>
        <a href="{{ route('sub_admin.verifications') }}" 
           class="border border-blue-600 text-blue-600 hover:bg-blue-50 text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            Open Full Verification Desk
        </a>
    </div>
    <div id="subadmin-dashboard-table"></div>
</div>
@endsection

@section('scripts')
<script>
    var queueData = @json($pendingVerifications);

    var table = new Tabulator("#subadmin-dashboard-table", {
        data: queueData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 5,
        placeholder: "No Verification Requests Pending",
        columns: [
            {title: "Booking ID", field: "id", width: 140, formatter: function(cell){
                return "<code class='bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
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
</script>
@endsection
