@extends('layouts.admin')

@section('title', 'Rent Collection Ledger - Rudra Group PG')
@section('page_title', 'Monthly Rent Dues & Collection Ledger')

@section('content')
<div x-data="{ cashModalOpen: false }"
     @close-cash-modal.window="cashModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">August 2026 Rent Dues & Offline Payments</h3>
            <p class="text-xs text-slate-500">Record offline cash payments or verify student-submitted UPI transaction proofs.</p>
        </div>
        <button @click="cashModalOpen = true" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-money-bill-wave"></i> Record Offline Cash Payment
        </button>
    </div>

    <!-- Tabulator Rent Dues Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="rent-search" 
                   class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search resident name, room...">
            <button id="export-rent-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
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
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden transform transition-all">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resident Student</label>
                    <select name="student_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->room ? 'Room ' . $st->room->room_number : 'Unassigned' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Payment Type</label>
                    <select name="payment_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="RENT">Monthly Rent</option>
                        <option value="DEPOSIT">Security Deposit</option>
                        <option value="ELECTRICITY">Electricity Bill</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cash Amount Received (₹)</label>
                    <input type="number" name="amount" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter amount">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Receipt Remarks</label>
                    <input type="text" name="remarks" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Received in cash at desk">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="cashModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
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
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No Rent Dues Found",
        columns: [
            {title: "Resident ID", field: "resident_id", width: 120},
            {title: "Student Name", field: "student_name", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room & Bed", field: "room"},
            {title: "Monthly Rent", field: "rent"},
            {title: "Due Date", field: "due_date"},
            {title: "Mode", field: "payment_mode"},
            {title: "Ref / UTR", field: "utr"},
            {title: "Status", field: "status", formatter: function(cell){
                var status = cell.getValue();
                if (status.includes("Paid")) return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
                if (status.includes("Pending")) return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
                return '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">' + status + '</span>';
            }},
            {title: "Action", field: "status", width: 130, formatter: function(cell){
                return '<button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1"><i class="fa-solid fa-check"></i> Verify</button>';
            }},
        ]
    });

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
