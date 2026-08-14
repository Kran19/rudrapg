@extends('layouts.admin')

@section('title', 'Rent Collection Ledger - Rudra Group PG')
@section('page_title', 'Monthly Rent Dues & Collection Ledger')

@section('content')
<div x-data="{ cashModalOpen: false, editModalOpen: false, editForm: { id: '', student_name: '', amount: 0, raw_due_date: '', payment_mode: '', utr: '', raw_status: '' } }"
     @close-cash-modal.window="cashModalOpen = false"
     @close-edit-modal.window="editModalOpen = false"
     @open-edit-modal.window="editForm = { ...$event.detail }; editModalOpen = true">
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
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="cashModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-emerald-600 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar"></i> Record Cash Payment
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

    <!-- Pure Tailwind Modal: Edit Payment -->
    <div x-show="editModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-blue-600 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Payment Record
                </h4>
                <button @click="editModalOpen = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-payment-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="editForm.id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Student Name</label>
                    <input type="text" readonly x-model="editForm.student_name" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm focus:outline-none cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Amount (₹)</label>
                    <input type="number" name="amount" required x-model="editForm.amount" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter amount">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Due Date</label>
                    <input type="date" name="due_date" x-model="editForm.raw_due_date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Mode</label>
                    <select name="payment_mode" required x-model="editForm.payment_mode" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="CASH">CASH</option>
                        <option value="UPI">UPI</option>
                        <option value="CHECK">CHECK</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Ref / UTR Number</label>
                    <input type="text" name="utr" x-model="editForm.utr" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter transaction reference">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Status</label>
                    <select name="status" required x-model="editForm.raw_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="PENDING">Pending Verification</option>
                        <option value="PAID">Paid</option>
                        <option value="VERIFIED">Verified</option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="deletePayment(editForm.id)" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1"><i class="fa-solid fa-trash"></i> Delete</button>
                    <div class="flex gap-3">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Changes</button>
                    </div>
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
            {title: "Room & Bed", field: "room", minWidth: 150},
            {title: "Monthly Rent", field: "rent", minWidth: 120},
            {title: "Due Date", field: "due_date", minWidth: 120},
            {title: "Mode", field: "payment_mode", minWidth: 100},
            {title: "Ref / UTR", field: "utr", minWidth: 140},
            {title: "Status", field: "status", minWidth: 140, formatter: function(cell){
                var status = cell.getValue();
                if (status.includes("Paid")) return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
                if (status.includes("Pending")) return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
                return '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
            }},
            {title: "Action", field: "id", minWidth: 130, formatter: function(cell){
                var status = cell.getRow().getData().status;
                if (status === "Paid" || status === "PAID" || status === "VERIFIED") {
                    return '<span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-circle-check"></i> Verified</span>';
                }
                return '<button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1"><i class="fa-solid fa-check"></i> Verify</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (data.status !== "Paid" && data.status !== "PAID" && data.status !== "VERIFIED") {
                    verifyPayment(data.id);
                }
            }},
            {title: "Edit", field: "id", minWidth: 100, formatter: function(cell){
                return '<button class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
            }},
        ]
    });

    function verifyPayment(id) {
        Swal.fire({
            title: 'Verify Rent/Deposit Payment?',
            text: 'This will mark the payment as verified and update student rent status.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Yes, Verify Payment'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/sub-admin/rent-ledger/" + id + "/verify", {
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

    function deletePayment(id) {
        Swal.fire({
            title: 'Delete Payment Record?',
            text: 'This will permanently delete this payment transaction and associated ledger entries.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Payment'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/sub-admin/rent-ledger/" + id, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        window.dispatchEvent(new CustomEvent('close-edit-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to delete payment.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during deletion.");
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

    document.getElementById("edit-payment-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="id"]').value;

        fetch("/sub-admin/rent-ledger/" + id + "/update", {
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
                window.dispatchEvent(new CustomEvent('close-edit-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update payment.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during update.");
        });
    });
</script>
@endsection
