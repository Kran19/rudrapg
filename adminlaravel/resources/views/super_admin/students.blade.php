@extends('layouts.admin')

@section('title', 'Student Directory - Rudra Group PG')
@section('page_title', 'Master Student Directory (All Branches)')

@section('content')
<div x-data="{ lightboxOpen: false, lightboxSrc: '', lightboxTitle: '' }" @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true">
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
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
        <input type="text" id="student-search" 
               class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full md:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
               placeholder="🔍 Search by Name, Phone, Aadhaar...">
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="branch-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All Branches</option>
                <option value="Naroda Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Naroda Branch</option>
                <option value="Satellite Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Satellite Branch</option>
                <option value="Prahlad Nagar Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Prahlad Nagar Branch</option>
                <option value="SG Highway Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">SG Highway Branch</option>
            </select>
            <select id="kyc-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All KYC Status</option>
                <option value="VERIFIED" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Verified</option>
                <option value="PENDING" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Pending</option>
            </select>
        </div>
    </div>
    <div id="students-table"></div>
</div>

    <!-- Fullscreen Image Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" style="display: none;"
         x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[60] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-4xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-plus text-blue-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <i class="fa-solid fa-xmark mr-1"></i> Close Lightbox (Esc)
            </button>
        </div>
        <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-transform hover:scale-125 cursor-zoom-in">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var studentData = @json($students);

    var table = new Tabulator("#students-table", {
        data: studentData,
        layout: "fitColumns",

        placeholder: "No Resident Students Found",
        columns: [
            {title: "Student ID", field: "id", minWidth: 140},
            {title: "Full Name", field: "full_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone Number", field: "phone", minWidth: 130},
            {title: "PG Branch", field: "branch_name", minWidth: 140, formatter: function(cell){
                return '<span class="bg-slate-900 text-white text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Room & Bed", field: "room_bed", minWidth: 140},
            {title: "Joining Date", field: "joining_date", minWidth: 120},
            {title: "KYC Status", field: "kyc_status", minWidth: 120, formatter: function(cell){
                var val = cell.getValue();
                if (val === "VERIFIED" || val === "APPROVED") {
                    return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>';
                }
                return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">' + (val ? val.replace('_', ' ') : 'Pending') + '</span>';
            }},
            {title: "Rent Status", field: "rent_status", minWidth: 120, formatter: function(cell){
                if (cell.getValue() == "PAID" || cell.getValue() == "Paid") return '<span class="bg-emerald-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Paid</span>';
                if (cell.getValue() == "PENDING" || cell.getValue() == "Pending") return '<span class="bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
                return '<span class="bg-rose-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Overdue</span>';
            }},
            {title: "Actions", field: "id", minWidth: 120, formatter: function(cell){
                return '<button class="bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors"><i class="fa-solid fa-id-card mr-1"></i> View KYC</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if(data.profile_photo || data.aadhaar_front) {
                    window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: data.profile_photo || data.aadhaar_front, title: 'KYC Docs for ' + data.full_name } }));
                } else {
                    toastr.info("No KYC images uploaded for this student yet.");
                }
            }},
        ]
    });

    document.getElementById("student-search").addEventListener("keyup", function(){
        table.setFilter("full_name", "like", this.value);
    });

    document.getElementById("branch-filter").addEventListener("change", function(){
        if(this.value === "") {
            table.clearFilter();
        } else {
            table.setFilter("branch_name", "=", this.value);
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
