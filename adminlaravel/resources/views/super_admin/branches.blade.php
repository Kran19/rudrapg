@extends('layouts.admin')

@section('title', 'Branch Management - Rudra Group PG')
@section('page_title', 'PG Branch Directory & QR Generator')

@section('content')
<div x-data="{ addBranchModalOpen: false, qrModalOpen: false, qrBranchName: '', qrBranchCode: '' }">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">PG Branch Master Table</h3>
            <p class="text-xs text-slate-500">Manage PG branches, assign managers, set unit tariffs, and view QR standees.</p>
        </div>
        <button @click="addBranchModalOpen = true" 
                class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Add New PG Branch
        </button>
    </div>

    <!-- Tabulator Branch Directory Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <input type="text" id="branch-search" 
                   class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search Branch Name, Code, Manager...">
            <button id="export-csv" class="px-3.5 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>
        <div id="branches-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Add Branch -->
    <div x-show="addBranchModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="addBranchModalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-building text-blue-400"></i> Create New PG Branch
                </h4>
                <button @click="addBranchModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Branch Code</label>
                    <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. PG-NRD-05">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Branch Name</label>
                    <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Bopal Branch">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Physical Address</label>
                    <textarea class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="2" placeholder="Full street address"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Electricity Unit Tariff</label>
                        <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" value="₹10.00 / unit">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Manager Name</label>
                        <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Branch Manager">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="addBranchModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="button" @click="addBranchModalOpen = false; toastr.success('Branch created successfully!')" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Branch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: QR Code Generator -->
    <div x-show="qrModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="qrModalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-sm overflow-hidden text-center transform transition-all p-6">
            <div class="flex justify-end">
                <button @click="qrModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <h4 class="font-bold text-lg text-slate-900" x-text="qrBranchName">Naroda Branch</h4>
            <p class="text-xs text-slate-500 font-mono" x-text="qrBranchCode">PG-NRD-01</p>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 my-4 inline-block shadow-inner">
                <svg width="180" height="180" viewBox="0 0 100 100" fill="none">
                    <rect width="100" height="100" rx="8" fill="white"/>
                    <rect x="10" y="10" width="25" height="25" fill="#0F172A"/>
                    <rect x="14" y="14" width="17" height="17" fill="white"/>
                    <rect x="18" y="18" width="9" height="9" fill="#0F172A"/>
                    
                    <rect x="65" y="10" width="25" height="25" fill="#0F172A"/>
                    <rect x="69" y="14" width="17" height="17" fill="white"/>
                    <rect x="73" y="18" width="9" height="9" fill="#0F172A"/>
                    
                    <rect x="10" y="65" width="25" height="25" fill="#0F172A"/>
                    <rect x="14" y="69" width="17" height="17" fill="white"/>
                    <rect x="18" y="73" width="9" height="9" fill="#0F172A"/>
                    
                    <rect x="40" y="10" width="10" height="10" fill="#2563EB"/>
                    <rect x="40" y="25" width="10" height="10" fill="#0F172A"/>
                    <rect x="50" y="40" width="15" height="15" fill="#2563EB"/>
                    <rect x="65" y="65" width="12" height="12" fill="#0F172A"/>
                    <rect x="80" y="80" width="10" height="10" fill="#2563EB"/>
                </svg>
            </div>
            
            <p class="text-xs text-slate-500 mb-4">Scan with Rudra PG Student App to lock branch context.</p>
            <button @click="toastr.info('Printing QR Code Standee...'); qrModalOpen = false" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 rounded-xl shadow-md flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> Print QR Standee
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var branchData = @json($branches);

    var table = new Tabulator("#branches-table", {
        data: branchData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        columns: [
            {title: "Branch Code", field: "code", width: 130, formatter: function(cell){
                return "<span class='bg-slate-900 text-white font-mono text-xs px-2.5 py-1 rounded-md'>" + cell.getValue() + "</span>";
            }},
            {title: "Branch Name", field: "name", width: 180, formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "City", field: "city", width: 120},
            {title: "Manager", field: "manager", width: 150},
            {title: "Beds Occupied", field: "occupied_beds", formatter: function(cell, row){
                var total = cell.getRow().getData().beds_count;
                return cell.getValue() + " / " + total + " beds";
            }},
            {title: "Unit Rate", field: "unit_rate", width: 130},
            {title: "QR Standee", field: "qr_hash", formatter: function(cell){
                return '<button class="border border-blue-600 text-blue-600 hover:bg-blue-50 text-xs font-semibold py-1 px-2.5 rounded-lg transition-colors flex items-center gap-1"><i class="fa-solid fa-qrcode"></i> View QR</button>';
            }, cellClick: function(e, cell){
                var rowData = cell.getRow().getData();
                var alpineData = Alpine.$data(document.querySelector('[x-data]'));
                alpineData.qrBranchName = rowData.name;
                alpineData.qrBranchCode = rowData.code;
                alpineData.qrModalOpen = true;
            }},
            {title: "Status", field: "status", width: 100, formatter: function(cell){
                return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Active</span>';
            }},
        ]
    });

    document.getElementById("branch-search").addEventListener("keyup", function(){
        table.setFilter("name", "like", this.value);
    });

    document.getElementById("export-csv").addEventListener("click", function(){
        table.download("csv", "branches.csv");
    });
</script>
@endsection
