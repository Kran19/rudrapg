@extends('layouts.admin')

@section('title', 'Rent Collection Ledger - Rudra Group PG')
@section('page_title', 'Monthly Rent Dues & Collection Ledger')

@section('content')
<div x-data="rentLedgerApp()" x-init="init()" 
     @close-payment-modal.window="paymentModalOpen = false"
     @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true"
     @close-lightbox.window="lightboxOpen = false"
     @keydown.escape.window="lightboxOpen = false; paymentModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Rent Dues, Partial Deposits & Offline Payments</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Record partial payments, cash deposits, track remaining balance left, or audit UPI receipts.</p>
        </div>
        <button @click="openModal()" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-hand-holding-dollar"></i> Record Manual / Partial Payment
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

    <!-- Modal: Record Manual / Partial Payment -->
    <div x-show="paymentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <div @click.away="paymentModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all my-8">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-5 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar"></i> Record Manual / Partial Payment
                    </h4>
                    <p class="text-xs text-emerald-100 mt-0.5">Accept partial or full payments and track the remaining balance left.</p>
                </div>
                <button @click="paymentModalOpen = false" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="record-payment-form" @submit.prevent="submitPayment" class="p-6 space-y-4">
                @csrf
                <!-- Resident Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resident Student</label>
                    <select name="student_id" x-model="selectedStudentId" @change="onStudentChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Choose Resident --</option>
                        <template x-for="st in students" :key="st.id">
                            <option :value="st.id" x-text="st.full_name + ' (' + (st.room_number !== 'Unassigned' ? 'Room ' + st.room_number : 'Unassigned') + ' • Rent: ₹' + st.monthly_rent + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Payment Type -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Category</label>
                        <select name="payment_type" x-model="paymentType" @change="onPaymentTypeChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="RENT">Monthly Rent</option>
                            <option value="DEPOSIT">Security Deposit</option>
                            <option value="ELECTRICITY">Electricity Bill</option>
                            <option value="OTHER">Other Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="paymentMode" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="CASH">Cash at Desk</option>
                            <option value="UPI">UPI Transfer</option>
                            <option value="BANK_TRANSFER">Bank Transfer (NEFT/IMPS)</option>
                            <option value="CHEQUE">Cheque / Demand Draft</option>
                        </select>
                    </div>
                </div>

                <!-- Live Amount Calculation Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Total Expected Amount (₹)
                        </label>
                        <input type="number" step="0.01" min="0" name="total_due" x-model.number="totalDue" required 
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                               placeholder="e.g. 8000">
                        <span class="text-[11px] text-slate-400 mt-1 block">Full rent or bill due</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-emerald-700 dark:text-emerald-400">
                            Amount Paid Now (₹)
                        </label>
                        <input type="number" step="0.01" min="1" name="amount" x-model.number="amountPaid" required 
                               class="w-full px-3.5 py-2.5 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-300 dark:border-emerald-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none" 
                               placeholder="e.g. 4000">
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 block">Deposited amount</span>
                    </div>
                </div>

                <!-- Dynamic Real-Time Summary Alert Box -->
                <div class="p-3.5 rounded-xl border transition-all"
                     :class="remainingAmount > 0 ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800'">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider block"
                                  :class="remainingAmount > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-emerald-800 dark:text-emerald-300'">
                                Balance Remaining Left
                            </span>
                            <span class="text-xl font-extrabold"
                                  :class="remainingAmount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'"
                                  x-text="'₹' + Number(remainingAmount).toLocaleString('en-IN')">
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase"
                                  :class="remainingAmount > 0 ? 'bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-200'"
                                  x-text="remainingAmount > 0 ? 'Partially Paid' : 'Fully Cleared'">
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-1"
                                  x-text="'Paid ₹' + Number(amountPaid || 0).toLocaleString('en-IN') + ' of ₹' + Number(totalDue || 0).toLocaleString('en-IN')">
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="remaining_amount" :value="remainingAmount">
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Receipt Remarks / Note</label>
                    <input type="text" name="remarks" x-model="remarks" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                           placeholder="e.g. Received ₹4,000 cash, balance ₹4,000 to be paid on 20th">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="paymentModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" :disabled="isSubmitting" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md flex items-center gap-2">
                        <template x-if="isSubmitting">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </template>
                        <span x-text="'Record Payment (Paid: ₹' + Number(amountPaid || 0).toLocaleString() + ' • Left: ₹' + Number(remainingAmount || 0).toLocaleString() + ')'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fullscreen Payment Proof Lightbox Modal -->
    <div x-show="lightboxOpen" style="display: none;"
         x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[70] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-4xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-receipt text-blue-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <div class="flex items-center gap-2">
                <a :href="lightboxSrc" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Original
                </a>
                <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700 text-white">
                    <i class="fa-solid fa-xmark mr-1"></i> Close Lightbox (Esc)
                </button>
            </div>
        </div>
        <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-transform hover:scale-125 cursor-zoom-in" title="Hover to zoom, click 'Open Original' for full resolution">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var duesData = @json($dues);
    var rawStudents = @json($students);

    function rentLedgerApp() {
        return {
            paymentModalOpen: false,
            lightboxOpen: false,
            lightboxSrc: '',
            lightboxTitle: '',
            students: rawStudents,
            selectedStudentId: '',
            paymentType: 'RENT',
            paymentMode: 'CASH',
            totalDue: 0,
            amountPaid: 0,
            remarks: '',
            isSubmitting: false,

            get remainingAmount() {
                var total = parseFloat(this.totalDue) || 0;
                var paid = parseFloat(this.amountPaid) || 0;
                return Math.max(0, total - paid);
            },

            init() {
                var urlParams = new URLSearchParams(window.location.search);
                var studentParam = urlParams.get('student_id');
                if (studentParam) {
                    this.openModal(studentParam);
                }
            },

            openModal(studentId = null) {
                if (studentId) {
                    this.selectedStudentId = studentId;
                } else if (!this.selectedStudentId && this.students.length > 0) {
                    this.selectedStudentId = this.students[0].id;
                }
                this.onStudentChange();
                this.paymentModalOpen = true;
            },

            onStudentChange() {
                var student = this.students.find(s => String(s.id) === String(this.selectedStudentId));
                if (student) {
                    if (this.paymentType === 'RENT') {
                        this.totalDue = (student.rent_balance_due > 0) ? student.rent_balance_due : student.monthly_rent;
                    } else if (this.paymentType === 'DEPOSIT') {
                        this.totalDue = student.security_deposit || 0;
                    } else {
                        this.totalDue = student.monthly_rent || 0;
                    }
                    this.amountPaid = 0;
                }
            },

            onPaymentTypeChange() {
                this.onStudentChange();
            },

            submitPayment(e) {
                var form = document.getElementById("record-payment-form");
                var formData = new FormData(form);
                this.isSubmitting = true;

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
                    this.isSubmitting = false;
                    if (data.status === "success") {
                        toastr.success(data.message);
                        this.paymentModalOpen = false;
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        toastr.error(data.message || "Failed to record payment.");
                    }
                })
                .catch(err => {
                    this.isSubmitting = false;
                    toastr.error("An error occurred during submission.");
                });
            }
        };
    }

    var table = new Tabulator("#rent-table", {
        data: duesData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Rent Dues Found",
        columns: [
            {title: "Resident ID", field: "resident_id", minWidth: 120},
            {title: "Student Name", field: "student_name", minWidth: 150, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room & Bed", field: "room", minWidth: 130},
            {title: "Total Due", field: "formatted_total", minWidth: 105, formatter: function(cell){
                return "<span class='font-semibold text-slate-700 dark:text-slate-300'>" + (cell.getValue() || '—') + "</span>";
            }},
            {title: "Amount Paid", field: "rent", minWidth: 110, formatter: function(cell){
                return "<span class='font-bold text-emerald-600 dark:text-emerald-400'>" + cell.getValue() + "</span>";
            }},
            {title: "Amount Left", field: "formatted_left", minWidth: 115, formatter: function(cell){
                var row = cell.getRow().getData();
                var remaining = parseFloat(row.remaining_amount) || 0;
                if (remaining > 0) {
                    return "<span class='bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 text-xs font-black px-2 py-0.5 rounded-md'>₹" + remaining.toLocaleString() + " Left</span>";
                }
                return "<span class='text-slate-400 text-xs font-semibold'>₹0 (Cleared)</span>";
            }},
            {title: "Due Date", field: "due_date", minWidth: 100},
            {title: "Mode", field: "payment_mode", minWidth: 95},
            {title: "Ref / UTR", field: "utr", minWidth: 140, formatter: function(cell){
                var row = cell.getRow().getData();
                var utr = row.utr || 'N/A';
                if (row.proof_url) {
                    return '<div class="flex items-center gap-1.5 font-mono text-xs"><span class="truncate">' + utr + '</span><button type="button" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 p-0.5 view-proof-btn" title="View Uploaded Payment Proof"><i class="fa-solid fa-eye text-xs"></i></button></div>';
                }
                return '<span class="font-mono text-xs text-slate-800 dark:text-slate-200">' + utr + '</span>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (data.proof_url && (e.target.classList.contains('view-proof-btn') || e.target.closest('.view-proof-btn'))) {
                    window.dispatchEvent(new CustomEvent('open-lightbox', {
                        detail: {
                            src: data.proof_url,
                            title: 'Payment Proof: ' + (data.student_name || 'Resident') + ' (UTR: ' + (data.utr || 'N/A') + ' - ' + (data.rent || '') + ')'
                        }
                    }));
                }
            }},
            {title: "Proof Receipt", field: "proof_url", minWidth: 130, formatter: function(cell){
                var row = cell.getRow().getData();
                if (row.proof_url) {
                    return '<button type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 rounded-lg transition-colors view-proof-btn" title="View Uploaded Payment Proof"><i class="fa-solid fa-eye"></i> View Proof</button>';
                }
                return '<span class="text-slate-400 dark:text-slate-500 text-xs italic">No Proof</span>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (data.proof_url && (e.target.classList.contains('view-proof-btn') || e.target.closest('.view-proof-btn'))) {
                    window.dispatchEvent(new CustomEvent('open-lightbox', {
                        detail: {
                            src: data.proof_url,
                            title: 'Payment Proof: ' + (data.student_name || 'Resident') + ' (UTR: ' + (data.utr || 'N/A') + ' - ' + (data.rent || '') + ')'
                        }
                    }));
                }
            }},
            {title: "Status", field: "status", minWidth: 140, formatter: function(cell){
                var row = cell.getRow().getData();
                var raw = (row.raw_status || '').toUpperCase();
                var rem = parseFloat(row.remaining_amount) || 0;

                if (raw === "PARTIALLY_PAID" || (rem > 0 && raw !== "REJECTED")) {
                    return '<span class="bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 w-max"><i class="fa-solid fa-clock-rotate-left"></i> PARTIAL</span>';
                }
                if (raw === "VERIFIED" || raw === "PAID") {
                    return '<span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-450 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 w-max"><i class="fa-solid fa-check"></i> PAID</span>';
                }
                if (raw === "REJECTED") {
                    return '<span class="bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 text-xs font-bold px-2.5 py-1 rounded-full">REJECTED</span>';
                }
                return '<span class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 text-xs font-bold px-2.5 py-1 rounded-full">PENDING</span>';
            }},
            {title: "Action", field: "id", minWidth: 200, hozAlign: "center", formatter: function(cell){
                var row = cell.getRow().getData();
                var raw = (row.raw_status || '').toUpperCase();
                var studentId = row.student_id;

                var html = '<div class="inline-flex items-center gap-2 whitespace-nowrap py-1">';
                if (row.proof_url) {
                    html += '<button type="button" class="w-8 h-8 inline-flex items-center justify-center bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900/70 rounded-xl shadow-xs transition-all cursor-pointer view-proof-btn shrink-0" title="View Uploaded Payment Proof Receipt"><i class="fa-solid fa-eye text-xs"></i></button>';
                }
                if (raw === "PENDING") {
                    html += '<button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer approve-btn shrink-0"><i class="fa-solid fa-check"></i> Approve</button>';
                    html += '<button type="button" class="bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer reject-btn shrink-0"><i class="fa-solid fa-xmark"></i> Reject</button>';
                } else {
                    html += '<button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer add-partial-btn shrink-0" title="Record another partial payment"><i class="fa-solid fa-plus"></i> Add Payment</button>';
                }
                html += '</div>';
                return html;
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('view-proof-btn') || e.target.closest('.view-proof-btn')) {
                    if (data.proof_url) {
                        window.dispatchEvent(new CustomEvent('open-lightbox', {
                            detail: {
                                src: data.proof_url,
                                title: 'Payment Proof: ' + (data.student_name || 'Resident') + ' (UTR: ' + (data.utr || 'N/A') + ' - ' + (data.rent || '') + ')'
                            }
                        }));
                    }
                } else if (e.target.classList.contains('approve-btn') || e.target.closest('.approve-btn')) {
                    verifyPayment(data.id);
                } else if (e.target.classList.contains('reject-btn') || e.target.closest('.reject-btn')) {
                    rejectPayment(data.id);
                } else if (e.target.classList.contains('add-partial-btn') || e.target.closest('.add-partial-btn')) {
                    var alpine = Alpine.$data(document.querySelector('[x-data]'));
                    if (alpine && data.student_id) {
                        alpine.openModal(data.student_id);
                    }
                }
            }},
        ]
    });

    function verifyPayment(id) {
        Swal.fire({
            title: 'Verify Payment?',
            text: 'This will approve the payment record and update the student rent balance.',
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
            title: 'Reject Payment?',
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
</script>
@endsection
