@extends('layouts.admin')

@section('title', 'Student Directory - Rudra Group PG')
@section('page_title', 'Master Student Directory (All Branches)')

@section('content')
<div x-data="{ lightboxOpen: false, lightboxSrc: '', lightboxTitle: '', editStudentModalOpen: false, editForm: { db_id: '', full_name: '', phone: '', branch_id: '', raw_joining_date: '', raw_kyc_status: '', raw_rent_status: '' } }"
     @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true"
     @open-edit-student-modal.window="editForm = { ...$event.detail }; editStudentModalOpen = true"
     @close-edit-student-modal.window="editStudentModalOpen = false">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-slate-900">Global Resident Student Directory</h3>
        <p class="text-xs text-slate-500">Search and filter active resident students across all Rudra Group PG branches.</p>
    </div>
    <button id="export-students-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i> Export Directory CSV
    </button>
</div>

<!-- Tabulator Student Directory Table -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
        <input type="text" id="student-search" 
               class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full md:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
               placeholder="🔍 Search by Name, Phone, Aadhaar...">
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="branch-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All Branches</option>
                <option value="Naroda Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Naroda Branch</option>
                <option value="Satellite Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Satellite Branch</option>
                <option value="Prahlad Nagar Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Prahlad Nagar Branch</option>
                <option value="SG Highway Branch" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">SG Highway Branch</option>
            </select>
            <select id="kyc-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All KYC Status</option>
                <option value="VERIFIED" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Verified</option>
                <option value="PENDING" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Pending</option>
            </select>
        </div>
    </div>
    <div id="students-table"></div>
</div>

    <!-- Fullscreen Image Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" style="display: none;"
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

    <!-- Pure Tailwind Modal: Edit Student Record -->
    <div x-show="editStudentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editStudentModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-users text-amber-400"></i> Edit Student Record: <span x-text="editForm.full_name"></span>
                </h4>
                <button @click="editStudentModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-student-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="db_id" x-model="editForm.db_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name</label>
                        <input type="text" name="full_name" required x-model="editForm.full_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" required x-model="editForm.phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Assigned PG Branch</label>
                        <select name="branch_id" required x-model="editForm.branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Joining Date</label>
                        <input type="date" name="joining_date" x-model="editForm.raw_joining_date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">KYC Status</label>
                        <select name="kyc_status" required x-model="editForm.raw_kyc_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="PENDING">PENDING</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Status</label>
                        <select name="rent_status" required x-model="editForm.raw_rent_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="PENDING">PENDING</option>
                            <option value="PAID">PAID</option>
                            <option value="OVERDUE">OVERDUE</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="deleteStudent(editForm.db_id)" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1.5"><i class="fa-solid fa-trash"></i> Delete Student</button>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="editStudentModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
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
    var studentData = @json($students);

    var table = new Tabulator("#students-table", {
        data: studentData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Resident Students Found",
        columns: [
            {title: "Student ID", field: "id", minWidth: 140},
            {title: "Full Name", field: "full_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone Number", field: "phone", minWidth: 130},
            {title: "PG Branch", field: "branch_name", minWidth: 140, formatter: function(cell){
                return '<span class="bg-slate-900 text-white text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Room & Bed", field: "room_bed", minWidth: 140},
            {title: "Joining Date", field: "joining_date", minWidth: 120},
            {title: "KYC Status", field: "kyc_status", minWidth: 120, formatter: function(cell){
                var val = cell.getValue();
                if (val === "VERIFIED" || val === "APPROVED") {
                    return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>';
                }
                return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">' + (val ? val.replace('_', ' ') : 'Pending') + '</span>';
            }},
            {title: "Rent Status", field: "rent_status", minWidth: 120, formatter: function(cell){
                if (cell.getValue() == "PAID" || cell.getValue() == "Paid") return '<span class="bg-emerald-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Paid</span>';
                if (cell.getValue() == "PENDING" || cell.getValue() == "Pending") return '<span class="bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
                return '<span class="bg-rose-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Overdue</span>';
            }},
            {title: "Actions", field: "id", minWidth: 220, formatter: function(cell){
                return '<div class="flex gap-1.5">' +
                       '  <button class="bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-bold px-2 py-1.5 rounded-lg transition-colors view-kyc-btn"><i class="fa-solid fa-id-card font-bold"></i> KYC</button>' +
                       '  <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2 py-1.5 rounded-lg shadow-sm edit-student-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                       '  <button class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-2 py-1.5 rounded-lg shadow-sm delete-student-btn"><i class="fa-solid fa-trash"></i> Delete</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('view-kyc-btn') || e.target.closest('.view-kyc-btn')) {
                    if(data.profile_photo || data.aadhaar_front) {
                        window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: data.profile_photo || data.aadhaar_front, title: 'KYC Docs for ' + data.full_name } }));
                    } else {
                        toastr.info("No KYC images uploaded for this student yet.");
                    }
                } else if (e.target.classList.contains('edit-student-btn') || e.target.closest('.edit-student-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-student-modal', { detail: data }));
                } else if (e.target.classList.contains('delete-student-btn') || e.target.closest('.delete-student-btn')) {
                    deleteStudent(data.db_id);
                }
            }},
        ]
    });

    document.getElementById("student-search").addEventListener("keyup", function(){
        table.setFilter("full_name", "like", this.value);
    });

    document.getElementById("branch-filter").addEventListener("change", function(){
        if(this.value === "") {
            table.clearFilter();
        } else {
            table.setFilter("branch_name", "=", this.value);
        }
    });

    document.getElementById("kyc-filter").addEventListener("change", function(){
        if(this.value === "") {
            table.clearFilter();
        } else {
            table.setFilter("kyc_status", "=", this.value);
        }
    });

    document.getElementById("export-students-csv").addEventListener("click", function(){
        table.download("csv", "students_directory.csv");
    });

    document.getElementById("edit-student-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="db_id"]').value;

        fetch("/super-admin/students/" + id + "/update", {
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
                window.dispatchEvent(new CustomEvent('close-edit-student-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update student.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    function deleteStudent(id) {
        Swal.fire({
            title: 'Delete Student Record?',
            text: 'This will remove the resident profile and release their assigned bed allocation.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Resident'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/super-admin/students/" + id, {
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
                        window.dispatchEvent(new CustomEvent('close-edit-student-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to delete student.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during deletion.");
                });
            }
        });
    }
</script>
@endsection
