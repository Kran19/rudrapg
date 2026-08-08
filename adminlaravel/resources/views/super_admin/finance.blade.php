@extends('layouts.admin')

@section('title', 'Financial & Revenue Hub - Rudra Group PG')
@section('page_title', 'Consolidated Financial & Revenue Hub')

@section('content')
<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-emerald-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Monthly Collections</p>
        <h3 class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $financeSummary['total_collections_this_month'] }}</h3>
    </div>
    <div class="bg-white rounded-2xl p-6 border-l-4 border-l-amber-500 border-t border-r border-b border-slate-200 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Cash in Hand</p>
        <h3 class="text-2xl font-extrabold text-amber-600 mt-2">{{ $financeSummary['total_cash_in_hand'] ?? '₹0' }}</h3>
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

<!-- Sub-Admin Manager Cash Collection Breakdown -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i> Sub-Admin Manager Cash Holdings Breakdown
            </h3>
            <p class="text-xs text-slate-500">Track physical cash collected by each branch sub-admin manager.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($managerCashLedger as $manager)
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-blue-600"></i> {{ $manager['manager_name'] }}
                        </h4>
                        <p class="text-xs text-slate-500">{{ $manager['branch_name'] }} • {{ $manager['manager_email'] }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-500 uppercase block">Total Cash Held</span>
                        <span class="text-lg font-extrabold text-emerald-600">{{ $manager['total_cash_collected'] }}</span>
                    </div>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-slate-700 uppercase mb-2">Itemized Cash Receipts</h5>
                    @if(count($manager['recent_cash_entries']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-200/60 text-slate-700 font-bold uppercase">
                                    <tr>
                                        <th class="p-2">Date & Time</th>
                                        <th class="p-2">Resident</th>
                                        <th class="p-2">Amount</th>
                                        <th class="p-2">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($manager['recent_cash_entries'] as $entry)
                                        <tr class="hover:bg-slate-100">
                                            <td class="p-2 text-slate-600">{{ $entry['date'] }}</td>
                                            <td class="p-2 font-semibold text-slate-900">{{ $entry['student'] }}</td>
                                            <td class="p-2 font-bold text-emerald-600">{{ $entry['amount'] }}</td>
                                            <td class="p-2 font-mono text-[10px] text-slate-500">{{ $entry['utr'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">No cash receipts recorded yet for this manager.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Tabulator Financial Ledger Table -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8 overflow-x-auto">
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
        layout: "fitDataFill",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No Financial Transactions Found",
        columns: [
            {title: "Txn Reference", field: "tx_reference", minWidth: 160, formatter: function(cell){
                return "<code class='bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs font-mono'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Branch", field: "branch_name", minWidth: 140},
            {title: "Payment Type", field: "payment_type", minWidth: 120, formatter: function(cell){
                var type = cell.getValue();
                if(type === 'Rent' || type === 'RENT') return '<span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Rent</span>';
                if(type === 'Security Deposit' || type === 'DEPOSIT') return '<span class="bg-purple-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Deposit</span>';
                return '<span class="bg-cyan-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Electricity</span>';
            }},
            {title: "Amount", field: "amount", minWidth: 110, formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Payment Mode", field: "payment_mode", minWidth: 120},
            {title: "Ref / UTR No", field: "utr", minWidth: 140},
            {title: "Date", field: "date", minWidth: 120},
            {title: "Status", field: "status", minWidth: 120, formatter: function(cell){
                return (cell.getValue() === "Verified" || cell.getValue() === "VERIFIED" || cell.getValue() === "PAID")
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
