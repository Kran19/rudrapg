@extends('layouts.admin')

@section('title', 'Financial & Revenue Hub - Rudra Group PG')
@section('page_title', 'Consolidated Financial & Revenue Hub')

@section('content')
<div x-data="{ editTxModalOpen: false, editForm: { id: '', student_name: '', amount_val: 0, raw_date: '', payment_mode: '', utr: '', status: '' } }"
     @open-edit-tx-modal.window="editForm = { ...$event.detail }; editTxModalOpen = true"
     @close-edit-tx-modal.window="editTxModalOpen = false">
<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-l-emerald-500 border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-xs">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Monthly Collections</p>
        <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">{{ $financeSummary['total_collections_this_month'] }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-l-amber-500 border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-xs">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Cash in Hand</p>
        <h3 class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">{{ $financeSummary['total_cash_in_hand'] ?? '₹0' }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-l-rose-500 border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-xs">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pending Rent Dues</p>
        <h3 class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">{{ $financeSummary['pending_rent_dues'] }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-l-blue-500 border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-xs">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Security Deposits Held</p>
        <h3 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">{{ $financeSummary['total_security_deposits_held'] }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-l-cyan-500 border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-xs">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Electricity Collections</p>
        <h3 class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400 mt-2">{{ $financeSummary['electricity_collections'] }}</h3>
    </div>
</div>

<!-- Sub-Admin Manager Cash Collection Breakdown -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8">
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i> Sub-Admin Manager Cash Holdings Breakdown
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track physical cash collected by each branch sub-admin manager.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($managerCashLedger as $manager)
            <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-blue-600"></i> {{ $manager['manager_name'] }}
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $manager['branch_name'] }} • {{ $manager['manager_email'] }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase block">Total Cash Held</span>
                        <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">{{ $manager['total_cash_collected'] }}</span>
                    </div>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Itemized Cash Receipts</h5>
                    @if(count($manager['recent_cash_entries']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-200/60 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold uppercase">
                                    <tr>
                                        <th class="p-2">Date & Time</th>
                                        <th class="p-2">Resident</th>
                                        <th class="p-2">Amount</th>
                                        <th class="p-2">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    @foreach($manager['recent_cash_entries'] as $entry)
                                        <tr class="hover:bg-slate-100 dark:hover:bg-slate-800">
                                            <td class="p-2 text-slate-600 dark:text-slate-400">{{ $entry['date'] }}</td>
                                            <td class="p-2 font-semibold text-slate-900 dark:text-slate-100">{{ $entry['student'] }}</td>
                                            <td class="p-2 font-bold text-emerald-600 dark:text-emerald-400">{{ $entry['amount'] }}</td>
                                            <td class="p-2 font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ $entry['utr'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">No cash receipts recorded yet for this manager.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Tabulator Financial Ledger Table -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
    <div class="flex items-center justify-between gap-4 mb-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-blue-600"></i> Master Transaction Ledger (Tabulator.js)
        </h3>
        <button id="export-finance-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
            <i class="fa-solid fa-file-csv"></i> Export Ledger CSV
        </button>
    </div>
    <div id="finance-table"></div>
    
    <!-- Pure Tailwind Modal: Edit Transaction -->
    <div x-show="editTxModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editTxModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-amber-400"></i> Edit Transaction: <span x-text="editForm.tx_reference"></span>
                </h4>
                <button @click="editTxModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-tx-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="editForm.id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resident Student</label>
                    <input type="text" readonly x-model="editForm.student_name" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Transaction Amount (₹)</label>
                        <input type="number" name="amount" required x-model="editForm.amount_val" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Date</label>
                        <input type="date" name="due_date" x-model="editForm.raw_date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Mode</label>
                        <select name="payment_mode" required x-model="editForm.payment_mode" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="CASH">CASH</option>
                            <option value="UPI">UPI</option>
                            <option value="CHECK">CHECK</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">UTR / Ref No</label>
                        <input type="text" name="utr" x-model="editForm.utr" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Verification Status</label>
                    <select name="status" required x-model="editForm.status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option value="PENDING">PENDING</option>
                        <option value="PAID">PAID</option>
                        <option value="VERIFIED">VERIFIED</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editTxModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var txData = @json($transactions);

    var table = new Tabulator("#finance-table", {
        data: txData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Financial Transactions Found",
        columns: [
            {title: "Txn Reference", field: "tx_reference", minWidth: 160, formatter: function(cell){
                return "<code class='bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs font-mono'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Branch", field: "branch_name", minWidth: 140},
            {title: "Payment Type", field: "payment_type", minWidth: 120, formatter: function(cell){
                var type = cell.getValue();
                if(type === 'Rent' || type === 'RENT') return '<span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Rent</span>';
                if(type === 'Security Deposit' || type === 'DEPOSIT') return '<span class="bg-purple-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Deposit</span>';
                return '<span class="bg-cyan-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Electricity</span>';
            }},
            {title: "Amount", field: "amount", minWidth: 110, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Payment Mode", field: "payment_mode", minWidth: 120},
            {title: "Ref / UTR No", field: "utr", minWidth: 140},
            {title: "Proof Image", field: "proof_image", minWidth: 120, formatter: function(cell){
                var url = cell.getValue();
                if(url) {
                    return '<img src="' + url + '" class="h-8 w-12 object-cover rounded cursor-pointer border border-slate-200 hover:scale-110 transition-transform" onclick="window.open(\'' + url + '\', \'_blank\')">';
                }
                return '<span class="text-slate-400 text-xs">No Image</span>';
            }},
            {title: "Date", field: "date", minWidth: 120},
            {title: "Status", field: "status", minWidth: 120, formatter: function(cell){
                return (cell.getValue() === "Verified" || cell.getValue() === "VERIFIED" || cell.getValue() === "PAID")
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>' 
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
            }},
            {title: "Actions", field: "id", minWidth: 120, formatter: function(cell){
                return '<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 edit-tx-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('edit-tx-btn') || e.target.closest('.edit-tx-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-tx-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("export-finance-csv").addEventListener("click", function(){
        table.download("csv", "financial_ledger.csv");
    });

    document.getElementById("edit-tx-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="id"]').value;

        fetch("/super-admin/finance/" + id + "/update", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                toastr.success(data.message);
                window.dispatchEvent(new CustomEvent('close-edit-tx-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update transaction.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });
</script>
@endsection
