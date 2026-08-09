@extends('layouts.admin')

@section('title', 'Student Verification Desk - Rudra Group PG')
@section('page_title', 'Student Verification Desk (Naroda Branch)')

@section('content')
<div x-data="{ verifyModalOpen: false, activeStudent: {}, selectedBedId: '', availableBeds: (window.availableBedsData || []) }"
     @open-verify-modal.window="activeStudent = $event.detail; selectedBedId = ''; verifyModalOpen = true"
     @close-verify-modal.window="verifyModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Student Booking Approval Queue</h3>
            <p class="text-xs text-slate-500">Verify student Aadhaar/PAN documents and offline payment proofs before bed allocation.</p>
        </div>
    </div>

    <!-- Tabulator Verification Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="verify-search" 
                   class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search applicant name, room...">
            <button id="export-verifications-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Queue CSV
            </button>
        </div>
        <div id="verifications-table"></div>
    </div>

    <!-- Pure Tailwind Modal: View Document Verification -->
    <div x-show="verifyModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="verifyModalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-blue-400"></i> Document Verification: <span x-text="activeStudent.student_name"></span>
                </h4>
                <button @click="verifyModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Left: Student Details & Bed Assignment -->
                <div class="md:col-span-4 space-y-4 border-r border-slate-100 pr-4">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card"></i> Applicant Details
                    </h5>
                    
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Booking Reference</span>
                            <span class="font-mono text-sm font-bold text-slate-900" x-text="activeStudent.id"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Phone Number</span>
                            <span class="text-xs font-semibold text-slate-800" x-text="activeStudent.phone"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">Aadhaar Number</span>
                            <span class="text-xs font-semibold text-slate-800" x-text="activeStudent.aadhaar"></span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 font-medium block">PAN Card Number</span>
                            <span class="text-xs font-semibold text-slate-800" x-text="activeStudent.pan"></span>
                        </div>
                    </div>

                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 pt-2">
                        <i class="fa-solid fa-bed"></i> Assign / Allocate Bed
                    </h5>
                    <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-100 space-y-2 text-xs">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Room & Bed:</label>
                            <select x-model="selectedBedId" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Auto-allocate or Assign Bed Later</option>
                                <template x-for="bed in availableBeds" :key="bed.id">
                                    <option :value="bed.id" x-text="bed.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-slate-600">Current Spot:</span>
                            <span class="font-bold text-slate-900" x-text="'Room ' + activeStudent.room_number + ' (' + activeStudent.bed_code + ')'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Monthly Rent:</span>
                            <span class="font-bold text-blue-600" x-text="activeStudent.rent"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Security Deposit:</span>
                            <span class="font-bold text-slate-900" x-text="activeStudent.deposit"></span>
                        </div>
                    </div>
                </div>

                <!-- Center/Right: Images Preview -->
                <div class="md:col-span-8 space-y-4">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-images"></i> Identity & Payment Attachments
                    </h5>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Aadhaar Front Image</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center">
                                <img :src="activeStudent.aadhaar_front" class="w-full h-32 object-cover rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Aadhaar Back Image</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center">
                                <img :src="activeStudent.aadhaar_back" class="w-full h-32 object-cover rounded-lg">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Payment Proof Screenshot (Rent + Deposit)</label>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 text-center">
                            <img :src="activeStudent.payment_proof" class="w-full h-44 object-contain rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions with SweetAlert2 Triggers -->
            <div x-show="activeStudent && activeStudent.status !== 'Approved' && activeStudent.status !== 'APPROVED'" class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" @click="rejectBooking(activeStudent)" 
                        class="px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-xmark mr-1"></i> Reject Application
                </button>
                <button type="button" @click="approveBooking(activeStudent, selectedBedId)" 
                        class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition-all">
                    <i class="fa-solid fa-check mr-1"></i> Approve Booking & Key Handover
                </button>
            </div>
            <div x-show="activeStudent && (activeStudent.status === 'Approved' || activeStudent.status === 'APPROVED')" class="p-4 bg-emerald-50 border-t border-emerald-100 flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i> Application Approved & Bed Allocated</span>
                <button type="button" @click="verifyModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.availableBedsData = @json($availableBeds);
    var queueData = @json($queue);

    var table = new Tabulator("#verifications-table", {
        data: queueData,
        layout: "fitDataFill",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No Booking Verification Requests Pending",
        columns: [
            {title: "Booking ID", field: "id", minWidth: 150, formatter: function(cell){
                return "<code class='bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs font-mono'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone", field: "phone", minWidth: 130},
            {title: "Requested Bed", field: "bed_code", minWidth: 160, formatter: function(cell, row){
                return "Room " + cell.getRow().getData().room_number + " (" + cell.getValue() + ")";
            }},
            {title: "Rent", field: "rent", minWidth: 100},
            {title: "Deposit", field: "deposit", minWidth: 100},
            {title: "Date", field: "date", minWidth: 120},
            {title: "Status", field: "status", minWidth: 140, formatter: function(cell){
                return (cell.getValue() === "Approved" || cell.getValue() === "APPROVED")
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-circle-check"></i> Approved</span>'
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-clock"></i> Pending</span>';
            }},
            {title: "Actions", field: "id", minWidth: 130, formatter: function(cell){
                var status = cell.getRow().getData().status;
                if (status === "Approved" || status === "APPROVED") {
                    return '<span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-circle-check"></i> Approved</span>';
                }
                return '<button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-xs flex items-center gap-1"><i class="fa-solid fa-file-contract"></i> Audit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (data.status !== "Approved" && data.status !== "APPROVED") {
                    window.dispatchEvent(new CustomEvent('open-verify-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("verify-search").addEventListener("keyup", function(){
        table.setFilter("student_name", "like", this.value);
    });

    document.getElementById("export-verifications-csv").addEventListener("click", function(){
        table.download("csv", "verification_queue.csv");
    });

    function approveBooking(student, selectedBedId) {
        Swal.fire({
            title: 'Approve Student Registration?',
            text: "This will verify the student profile and confirm booking.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16A34A',
            confirmButtonText: 'Yes, Approve Booking'
        }).then((result) => {
            if (result.isConfirmed) {
                var targetId = student.db_id || student.id;
                fetch("/sub-admin/verifications/" + targetId + "/approve", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ bed_id: selectedBedId || null })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        window.dispatchEvent(new CustomEvent('close-verify-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to approve verification.");
                    }
                })
                .catch(err => {
                    toastr.error("Failed to approve verification.");
                });
            }
        });
    }

    function rejectBooking(student) {
        Swal.fire({
            title: 'Reject Application?',
            text: "Please confirm application rejection.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Reject'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/sub-admin/verifications/" + student.id + "/reject", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    toastr.error(data.message);
                    window.dispatchEvent(new CustomEvent('close-verify-modal'));
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(err => {
                    toastr.error("Failed to reject application.");
                });
            }
        });
    }
</script>
@endsection
