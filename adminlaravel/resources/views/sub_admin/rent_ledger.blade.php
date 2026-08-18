@extends('layouts.admin')

@section('title', 'Rent Collection Ledger - Rudra Group PG')
@section('page_title', 'Monthly Rent Dues & Collection Ledger')

@section('content')
<div x-data="{ cashModalOpen: false }"
     @close-cash-modal.window="cashModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">August 2026 Rent Dues & Offline Payments</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Record offline cash payments or verify student-submitted UPI transaction proofs.</p>
        </div>
        <button @click="cashModalOpen = true" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-money-bill-wave"></i> Record Offline Cash Payment
        </button>
    </div>

    <!-- Tabulator Rent Dues Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="rent-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                   placeholder="🔍 Search resident name, room...">
            <button id="export-rent-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Dues CSV
            </button>
        </div>
        <div id="rent-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Record Cash Payment -->
    <div x-show="cashModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="cashModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-emerald-600 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave"></i> Record Cash Payment
                </h4>
                <button @click="cashModalOpen = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="record-cash-form" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resident Student</label>
                    <select name="student_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->room ? 'Room ' . $st->room->room_number : 'Unassigned' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Type</label>
                    <select name="payment_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="RENT">Monthly Rent</option>
                        <option value="DEPOSIT">Security Deposit</option>
                        <option value="ELECTRICITY">Electricity Bill</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Cash Amount Received (₹)</label>
                    <input type="number" name="amount" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter amount">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Receipt Remarks</label>
                    <input type="text" name="remarks" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Received in cash at desk">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="cashModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md">Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var duesData = @json($dues);

    var table = new Tabulator("#rent-table", {
        data: duesData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Rent Dues Found",
        columns: [
            {title: "Resident ID", field: "resident_id", minWidth: 140},
            {title: "Student Name", field: "student_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room & Bed", field: "room", minWidth: 140},
            {title: "Monthly Rent", field: "rent", minWidth: 110},
            {title: "Due Date", field: "due_date", minWidth: 110},
            {title: "Mode", field: "payment_mode", minWidth: 100},
            {title: "Ref / UTR", field: "utr", minWidth: 140},
            {title: "Status", field: "status", minWidth: 120, formatter: function(cell){
                var status = cell.getValue();
                if (status === "Verified" || status === "VERIFIED" || status === "Paid" || status === "PAID") {
                    return '<span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-450 text-xs font-bold px-2.5 py-1 rounded-full">PAID</span>';
                }
                if (status === "Rejected" || status === "REJECTED") {
                    return '<span class="bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 text-xs font-bold px-2.5 py-1 rounded-full">REJECTED</span>';
                }
                return '<span class="bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 text-xs font-bold px-2.5 py-1 rounded-full">PENDING</span>';
            }},
            {title: "Action", field: "status", minWidth: 120, formatter: function(cell){
                var status = cell.getValue();
                if (status === "Verified" || status === "VERIFIED" || status === "Paid" || status === "PAID") {
                    return '<span class="text-xs font-bold text-emerald-600 dark:text-emerald-450 flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Approved</span>';
                }
                if (status === "Rejected" || status === "REJECTED") {
                    return '<span class="text-xs font-bold text-rose-600 dark:text-rose-450 flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>';
                }
                return '<span class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1"><i class="fa-regular fa-clock"></i> Pending Audit</span>';
            }},
            {title: "A/R - Reject", field: "id", minWidth: 180, formatter: function(cell){
                return '<div class="flex gap-1.5">' +
                       '  <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 approve-btn"><i class="fa-solid fa-circle-check"></i> Approve</button>' +
                       '  <button class="bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 reject-btn"><i class="fa-solid fa-circle-xmark"></i> Reject</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('approve-btn') || e.target.closest('.approve-btn')) {
                    verifyPayment(data.id);
                } else if (e.target.classList.contains('reject-btn') || e.target.closest('.reject-btn')) {
                    rejectPayment(data.id);
                }
            }},
        ]
    });

    function verifyPayment(id) {
        Swal.fire({
            title: 'Verify Rent/Deposit Payment?',
            text: 'This will mark the payment as verified and update student rent status.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Yes, Verify Payment'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("sub-admin/rent-ledger/" + id + "/verify"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to verify payment.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during verification.");
                });
            }
        });
    }

    function rejectPayment(id) {
        Swal.fire({
            title: 'Reject Rent/Deposit Payment?',
            text: 'This will reject the payment and set the resident status back to DUE.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Yes, Reject Payment'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("sub-admin/rent-ledger/" + id + "/reject"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to reject payment.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during rejection.");
                });
            }
        });
    }

    document.getElementById("rent-search").addEventListener("keyup", function(){
        table.setFilter("student_name", "like", this.value);
    });

    document.getElementById("export-rent-csv").addEventListener("click", function(){
        table.download("csv", "rent_ledger.csv");
    });

    document.getElementById("record-cash-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);

        fetch("{{ route('sub_admin.rent_ledger.cash_payment') }}", {
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
                window.dispatchEvent(new CustomEvent('close-cash-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to record cash payment.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });
</script>
@endsection
