@extends('layouts.admin')

@section('title', 'Electricity Bill Audit - Rudra Group PG')
@section('page_title', 'Monthly Electricity Meter Reading Audit')

@section('content')
<div x-data="{ auditModalOpen: false, activeReading: {} }"
     @open-audit-modal.window="activeReading = $event.detail; auditModalOpen = true"
     @close-audit-modal.window="auditModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Student Electricity Meter Submissions</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Audit physical meter photos uploaded by students, verify units consumed, and approve total bills.</p>
        </div>
    </div>

    <!-- Tabulator Electricity Readings Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="elec-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                   placeholder="🔍 Search resident, room...">
            <button id="export-elec-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Audit CSV
            </button>
        </div>
        <div id="electricity-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Meter Audit -->
    <div x-show="auditModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="auditModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-amber-500 text-slate-900 p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-bolt"></i> Audit Meter Reading: <span x-text="activeReading.student"></span>
                </h4>
                <button @click="auditModalOpen = false" class="text-slate-900/80 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 dark:text-slate-400 font-medium block">Room Number</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="'Room ' + activeReading.room"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 dark:text-slate-400 font-medium block">Tariff Unit Rate</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400" x-text="activeReading.rate"></span>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Previous Month Reading:</span>
                        <strong class="text-slate-900 dark:text-slate-100" x-text="activeReading.prev_reading + ' kWh'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Current Student Reading:</span>
                        <strong class="text-blue-600 dark:text-blue-400" x-text="activeReading.curr_reading + ' kWh'"></strong>
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Calculated Units Consumed:</span>
                        <strong class="text-slate-900 dark:text-slate-100" x-text="activeReading.units + ' Units'"></strong>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Total Electricity Bill:</span>
                        <strong class="font-bold text-emerald-600 dark:text-emerald-400" x-text="activeReading.total"></strong>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Physical Meter Photo Attachment</label>
                    <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2 text-center">
                        <img :src="activeReading.photo_url" class="w-full h-44 object-contain rounded-lg">
                    </div>
                </div>

                <div x-show="activeReading && activeReading.status !== 'Approved' && activeReading.status !== 'APPROVED'" class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="rejectReading(activeReading.id)" 
                            class="px-4 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 border border-rose-200 dark:border-rose-900 rounded-xl">Reject</button>
                    <button type="button" @click="approveReading(activeReading.id)" 
                            class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md">
                        <i class="fa-solid fa-check mr-1"></i> Approve Bill
                    </button>
                </div>
                <div x-show="activeReading && (activeReading.status === 'Approved' || activeReading.status === 'APPROVED')" class="flex items-center justify-between pt-4 border-t border-emerald-100 dark:border-emerald-900">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Reading Audited & Approved</span>
                    <button type="button" @click="auditModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-xl">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var readingsData = @json($readings);

    var table = new Tabulator("#electricity-table", {
        data: readingsData,
        layout: "fitColumns",

        placeholder: "No Electricity Submissions Pending Audit",
        columns: [
            {title: "Reading ID", field: "code", minWidth: 130},
            {title: "Student Name", field: "student", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room No", field: "room", minWidth: 100},
            {title: "Current Reading", field: "curr_reading", minWidth: 140, formatter: function(cell){
                return cell.getValue() + " kWh";
            }},
            {title: "Units Consumed", field: "units", minWidth: 140, formatter: function(cell){
                return "<strong>" + cell.getValue() + " Units</strong>";
            }},
            {title: "Total Amount", field: "total", minWidth: 120, formatter: function(cell){
                return "<strong class='text-emerald-600'>" + cell.getValue() + "</strong>";
            }},
            {title: "Submission Date", field: "date", minWidth: 130},
            {title: "Status", field: "status", minWidth: 130, formatter: function(cell){
                return (cell.getValue() === "Approved" || cell.getValue() === "APPROVED")
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Approved</span>'
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending Audit</span>';
            }},
            {title: "Audit Action", field: "id", minWidth: 130, formatter: function(cell){
                var status = cell.getRow().getData().status;
                if (status === "Approved" || status === "APPROVED") {
                    return '<span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-circle-check"></i> Approved</span>';
                }
                return '<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1"><i class="fa-solid fa-bolt"></i> Audit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (data.status !== "Approved" && data.status !== "APPROVED") {
                    window.dispatchEvent(new CustomEvent('open-audit-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("elec-search").addEventListener("keyup", function(){
        table.setFilter("student", "like", this.value);
    });

    document.getElementById("export-elec-csv").addEventListener("click", function(){
        table.download("csv", "electricity_meter_audit.csv");
    });

    function approveReading(id) {
        fetch("/sub-admin/electricity-audit/" + id + "/approve", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            toastr.success(data.message);
            window.dispatchEvent(new CustomEvent('close-audit-modal'));
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            toastr.error("Failed to approve electricity reading.");
        });
    }

    function rejectReading(id) {
        fetch("/sub-admin/electricity-audit/" + id + "/reject", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            toastr.error(data.message);
            window.dispatchEvent(new CustomEvent('close-audit-modal'));
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            toastr.error("Failed to reject electricity reading.");
        });
    }
</script>
@endsection
