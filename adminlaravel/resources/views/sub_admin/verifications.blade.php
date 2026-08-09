@extends('layouts.admin')

@section('title', 'Student Verification Desk - Rudra Group PG')
@section('page_title', 'Student Verification Desk (Naroda Branch)')

@section('content')
<div x-data="{ verifyModalOpen: false, activeStudent: {}, selectedBedId: '', availableBeds: (window.availableBedsData || []), lightboxOpen: false, lightboxSrc: '', lightboxTitle: '' }"
     @open-verify-modal.window="activeStudent = $event.detail; selectedBedId = ''; verifyModalOpen = true"
     @close-verify-modal.window="verifyModalOpen = false"
     @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true">
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
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-blue-400"></i> Applicant Audit Desk: <span class="text-blue-300" x-text="activeStudent.student_name"></span>
                </h4>
                <button @click="verifyModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Left: Student Details & Bed Assignment -->
                <div class="md:col-span-5 space-y-4 border-r border-slate-100 pr-4">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card"></i> Personal & Contact Info
                    </h5>
                    
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Ref Code:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="activeStudent.id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Phone:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.phone"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Email:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.email"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Aadhaar No:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.aadhaar"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">PAN No:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.pan"></span>
                        </div>
                    </div>

                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-people-roof"></i> Parent & Address Info
                    </h5>
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Parent Name:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.parent_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-medium">Parent Phone:</span>
                            <span class="font-semibold text-slate-800" x-text="activeStudent.parent_phone"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium block mb-0.5">Permanent Address:</span>
                            <span class="font-normal text-slate-700 block leading-tight" x-text="activeStudent.address"></span>
                        </div>
                    </div>

                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-bed"></i> Assign Room & Bed
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
                            <span class="font-bold text-slate-900" x-text="(!activeStudent.room_number || activeStudent.room_number === 'Pending' || activeStudent.room_number === 'Unassigned') ? 'Room Not Assigned Yet' : 'Room ' + activeStudent.room_number + ' (' + activeStudent.bed_code + ')'"></span>
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

                <!-- Center/Right: Images Preview & Lightbox Triggers -->
                <div class="md:col-span-7 space-y-4">
                    <h5 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-images"></i> Identity & Document Attachments</span>
                        <span class="text-[10px] text-slate-400 font-normal"><i class="fa-solid fa-magnifying-glass-plus mr-0.5"></i> Click image to zoom</span>
                    </h5>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Passport Profile Photo</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.profile_photo && $dispatch('open-lightbox', { src: activeStudent.profile_photo, title: 'Profile Photo - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.profile_photo">
                                    <div class="relative">
                                        <img :src="activeStudent.profile_photo" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.profile_photo">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-user-gear text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Aadhaar Front Image</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.aadhaar_front && $dispatch('open-lightbox', { src: activeStudent.aadhaar_front, title: 'Aadhaar Front - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.aadhaar_front">
                                    <div class="relative">
                                        <img :src="activeStudent.aadhaar_front" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.aadhaar_front">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-id-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Aadhaar Back Image</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.aadhaar_back && $dispatch('open-lightbox', { src: activeStudent.aadhaar_back, title: 'Aadhaar Back - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.aadhaar_back">
                                    <div class="relative">
                                        <img :src="activeStudent.aadhaar_back" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.aadhaar_back">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-id-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">PAN Card Attachment</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.pan_card && $dispatch('open-lightbox', { src: activeStudent.pan_card, title: 'PAN Card - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.pan_card">
                                    <div class="relative">
                                        <img :src="activeStudent.pan_card" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.pan_card">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-credit-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Payment Proof (Rent + Deposit Receipt)</label>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 text-center relative group overflow-hidden">
                            <template x-if="activeStudent.payment_proof">
                                <div class="cursor-pointer" @click="$dispatch('open-lightbox', { src: activeStudent.payment_proof, title: 'Payment Receipt - ' + activeStudent.student_name })">
                                    <img :src="activeStudent.payment_proof" class="w-full h-36 object-contain rounded-lg group-hover:scale-105 transition-all">
                                    <div class="mt-1 text-[11px] text-blue-600 font-bold"><i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Click to Zoom High-Res Payment Proof</div>
                                </div>
                            </template>
                            <template x-if="!activeStudent.payment_proof">
                                <div class="p-4 bg-amber-50/60 rounded-lg border border-amber-100 text-center">
                                    <i class="fa-solid fa-clock text-amber-500 text-lg mb-1"></i>
                                    <div class="text-xs font-bold text-amber-800">Payment Upload Pending from Resident App</div>
                                    <p class="text-[11px] text-amber-600 mt-0.5">Payment proof can be submitted by resident after profile approval and bed allocation.</p>
                                </div>
                            </template>
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

    <!-- Fullscreen Image Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" 
         x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[60] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-4xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-plus text-blue-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <i class="fa-solid fa-xmark mr-1"></i> Close Lightbox (Esc)
            </button>
        </div>
        <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-transform hover:scale-125 cursor-zoom-in">
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
            {title: "Requested Bed", field: "bed_code", minWidth: 160, formatter: function(cell){
                var room = cell.getRow().getData().room_number;
                var bed = cell.getValue();
                if (!room || room === "Pending" || room === "Unassigned") {
                    return "<span class='text-amber-600 font-semibold text-xs'><i class='fa-solid fa-bed mr-1'></i> Pending Assignment</span>";
                }
                return "Room " + room + " (" + bed + ")";
            }},
            {title: "Rent", field: "rent", minWidth: 140, formatter: function(cell){
                var val = cell.getValue();
                if (!val || val === "Pending Room Allocation" || val === "₹0") {
                    return "<span class='text-slate-400 italic text-xs'>Pending Allocation</span>";
                }
                return "<strong class='text-slate-900'>" + val + "</strong>";
            }},
            {title: "Deposit", field: "deposit", minWidth: 140, formatter: function(cell){
                var val = cell.getValue();
                if (!val || val === "Pending Room Allocation" || val === "₹0") {
                    return "<span class='text-slate-400 italic text-xs'>Pending Allocation</span>";
                }
                return "<strong class='text-slate-900'>" + val + "</strong>";
            }},
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
