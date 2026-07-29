@extends('layouts.admin')

@section('title', 'Student Directory - Rudra Group PG')
@section('page_title', 'Master Student Directory (All Branches)')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-slate-900">Global Resident Student Directory</h3>
        <p class="text-xs text-slate-500">Search and filter active resident students across all Rudra Group PG branches.</p>
    </div>
    <button id="export-students-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i> Export Directory CSV
    </button>
</div>

<!-- Tabulator Student Directory Table -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
        <input type="text" id="student-search" 
               class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full md:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
               placeholder="🔍 Search by Name, Phone, Aadhaar...">
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="branch-filter" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none">
                <option value="">All Branches</option>
                <option value="Naroda Branch">Naroda Branch</option>
                <option value="Satellite Branch">Satellite Branch</option>
                <option value="Prahlad Nagar Branch">Prahlad Nagar Branch</option>
                <option value="SG Highway Branch">SG Highway Branch</option>
            </select>
            <select id="kyc-filter" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none">
                <option value="">All KYC Status</option>
                <option value="Verified">Verified</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
    </div>
    <div id="students-table"></div>
</div>
@endsection

@section('scripts')
<script>
    var studentData = @json($students);

    var table = new Tabulator("#students-table", {
        data: studentData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        columns: [
            {title: "Student ID", field: "id", width: 110},
            {title: "Full Name", field: "name", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone Number", field: "phone"},
            {title: "PG Branch", field: "branch", formatter: function(cell){
                return '<span class="bg-slate-900 text-white text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Room & Bed", field: "room_bed"},
            {title: "Aadhaar No", field: "aadhaar"},
            {title: "Joining Date", field: "joining"},
            {title: "KYC Status", field: "kyc_status", formatter: function(cell){
                return cell.getValue() == "Verified" 
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>' 
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
            }},
            {title: "Rent Status", field: "rent_status", formatter: function(cell){
                if (cell.getValue() == "Paid") return '<span class="bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">Paid</span>';
                if (cell.getValue() == "Pending") return '<span class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">Pending</span>';
                return '<span class="bg-rose-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">Overdue</span>';
            }},
        ]
    });

    document.getElementById("student-search").addEventListener("keyup", function(){
        table.setFilter("name", "like", this.value);
    });

    document.getElementById("branch-filter").addEventListener("change", function(){
        if(this.value === "") {
            table.clearFilter();
        } else {
            table.setFilter("branch", "=", this.value);
        }
    });

    document.getElementById("kyc-filter").addEventListener("change", function(){
        if(this.value === "") {
            table.clearFilter();
        } else {
            table.setFilter("kyc_status", "=", this.value);
        }
    });

    document.getElementById("export-students-csv").addEventListener("click", function(){
        table.download("csv", "students_directory.csv");
    });
</script>
@endsection
