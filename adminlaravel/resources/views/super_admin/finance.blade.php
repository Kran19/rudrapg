@extends('layouts.admin')

@section('title', 'Financial & Revenue Hub - Rudra Group PG')
@section('page_title', 'Consolidated Financial & Revenue Hub')

@section('content')
<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-emerald-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Monthly Collections</p>
        <h3 class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $financeSummary['total_collections_this_month'] }}</h3>
    </div>
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-rose-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Rent Dues</p>
        <h3 class="text-2xl font-extrabold text-rose-600 mt-2">{{ $financeSummary['pending_rent_dues'] }}</h3>
    </div>
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-blue-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Security Deposits Held</p>
        <h3 class="text-2xl font-extrabold text-blue-600 mt-2">{{ $financeSummary['total_security_deposits_held'] }}</h3>
    </div>
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-cyan-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Electricity Collections</p>
        <h3 class="text-2xl font-extrabold text-cyan-600 mt-2">{{ $financeSummary['electricity_collections'] }}</h3>
    </div>
</div>

<!-- Tabulator Financial Ledger Table -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
    <div class="flex items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-blue-600"></i> Master Transaction Ledger (Tabulator.js)
        </h3>
        <button id="export-finance-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <i class="fa-solid fa-file-csv"></i> Export Ledger CSV
        </button>
    </div>
    <div id="finance-table"></div>
</div>
@endsection

@section('scripts')
<script>
    var txData = @json($transactions);

    var table = new Tabulator("#finance-table", {
        data: txData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        columns: [
            {title: "Txn ID", field: "tx_id", width: 150, formatter: function(cell){
                return "<code class='bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Branch", field: "branch"},
            {title: "Payment Type", field: "type", formatter: function(cell){
                var type = cell.getValue();
                if(type === 'Rent') return '<span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Rent</span>';
                if(type === 'Security Deposit') return '<span class="bg-purple-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Deposit</span>';
                return '<span class="bg-cyan-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Electricity</span>';
            }},
            {title: "Amount", field: "amount", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Payment Mode", field: "mode"},
            {title: "Ref / UTR No", field: "ref"},
            {title: "Date", field: "date"},
            {title: "Status", field: "status", formatter: function(cell){
                return cell.getValue() === "Verified" 
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>' 
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
            }},
        ]
    });

    document.getElementById("export-finance-csv").addEventListener("click", function(){
        table.download("csv", "financial_ledger.csv");
    });
</script>
@endsection
