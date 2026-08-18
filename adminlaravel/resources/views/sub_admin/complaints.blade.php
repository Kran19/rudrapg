@extends('layouts.admin')

@section('title', 'Complaints & Notice Desk - Rudra Group PG')
@section('page_title', 'Complaints Desk & Branch Announcement Notices')

@section('content')
<div x-data="{ noticeModalOpen: false, editTicketModalOpen: false, editForm: { db_id: '', ticket: '', student: '', room: '', category: '', title: '', description: '', raw_status: '', raw_priority: '', resolution_remarks: '' } }"
     @open-edit-ticket-modal.window="editForm = { ...$event.detail }; editTicketModalOpen = true"
     @close-edit-ticket-modal.window="editTicketModalOpen = false"
     @close-notice-modal.window="noticeModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Branch Complaints & Announcements</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Track student maintenance service tickets and publish branch announcement notices.</p>
        </div>
        <button @click="noticeModalOpen = true" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-bullhorn"></i> Broadcast Branch Notice
        </button>
    </div>

    <!-- Tabulator Complaints Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-headset text-blue-600"></i> Active Maintenance Tickets (Tabulator.js)
            </h4>
            <div class="flex items-center gap-2">
                <input type="text" id="ticket-search" 
                       class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                       placeholder="🔍 Search ticket title...">
                <button id="export-tickets-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
                    <i class="fa-solid fa-file-csv"></i> Export CSV
                </button>
            </div>
        </div>
        <div id="tickets-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Broadcast Notice -->
    <div x-show="noticeModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="noticeModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-blue-400"></i> Broadcast Branch Notice
                </h4>
                <button @click="noticeModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="broadcast-notice-form" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Notice Title</label>
                    <input type="text" name="title" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="e.g. Water Tank Cleaning Schedule">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Notice Details</label>
                    <textarea name="content" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" rows="3" placeholder="Enter instructions for residents..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Priority Category</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option value="Important Announcement">Important Announcement</option>
                        <option value="Maintenance Notice">Maintenance Notice</option>
                        <option value="Rent Due Notice">Rent Due Notice</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_important" value="0">
                    <input type="checkbox" name="is_important" value="1" checked id="impChk" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="impChk" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Mark as High Priority Push Notification</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="noticeModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Broadcast Notice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: Edit Ticket Status -->
    <div x-show="editTicketModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editTicketModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-wrench text-amber-400"></i> Edit Ticket: <span x-text="editForm.ticket"></span>
                </h4>
                <button @click="editTicketModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-ticket-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="editForm.db_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resident Student</label>
                        <input type="text" readonly x-model="editForm.student" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Room No</label>
                        <input type="text" readonly x-model="editForm.room" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Issue Title</label>
                    <input type="text" readonly x-model="editForm.title" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Description</label>
                    <textarea readonly x-text="editForm.description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Priority</label>
                        <select name="priority" required x-model="editForm.raw_priority" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="LOW">LOW</option>
                            <option value="MEDIUM">MEDIUM</option>
                            <option value="HIGH">HIGH</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Ticket Status</label>
                        <select name="status" required x-model="editForm.raw_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="OPEN">OPEN</option>
                            <option value="IN_PROGRESS">IN PROGRESS</option>
                            <option value="RESOLVED">RESOLVED</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resolution Remarks / Result</label>
                    <textarea name="resolution_remarks" x-model="editForm.resolution_remarks" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="Provide details on how this issue was resolved..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editTicketModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var ticketsData = @json($tickets);

    var table = new Tabulator("#tickets-table", {
        data: ticketsData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Active Maintenance Tickets",
        columns: [
            {title: "Ticket No", field: "ticket", minWidth: 120, formatter: function(cell){
                return "<code class='bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded text-xs font-mono'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student", minWidth: 140, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room No", field: "room", minWidth: 90},
            {title: "Category", field: "category", minWidth: 110, formatter: function(cell){
                return '<span class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Complain", field: "title", minWidth: 150},
            {title: "Reason", field: "description", minWidth: 200, formatter: function(cell){
                return '<span class="text-xs text-slate-600 dark:text-slate-400 font-normal line-clamp-2">' + cell.getValue() + '</span>';
            }},
            {title: "Priority", field: "priority", minWidth: 100, formatter: function(cell){
                var p = cell.getValue();
                if(p === 'High') return '<span class="bg-rose-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">High</span>';
                if(p === 'Medium') return '<span class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">Medium</span>';
                return '<span class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">Low</span>';
            }},
            {title: "Created Date", field: "date", minWidth: 110},
            {title: "Status", field: "status", minWidth: 100, formatter: function(cell){
                var s = cell.getValue();
                if(s === 'Resolved' || s === 'RESOLVED' || s === 'Solved') return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Solved</span>';
                return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
            }},
            {title: "Actions", field: "db_id", minWidth: 90, formatter: function(cell){
                return '<div class="flex gap-2">' +
                       '  <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 edit-ticket-btn"><i class="fa-solid fa-eye"></i> View</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('edit-ticket-btn') || e.target.closest('.edit-ticket-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-ticket-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("ticket-search").addEventListener("keyup", function(){
        table.setFilter("title", "like", this.value);
    });

    document.getElementById("export-tickets-csv").addEventListener("click", function(){
        table.download("csv", "maintenance_tickets.csv");
    });

    document.getElementById("broadcast-notice-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);

        fetch("{{ route('sub_admin.complaints.broadcast_notice') }}", {
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
                window.dispatchEvent(new CustomEvent('close-notice-modal'));
                this.reset();
            } else {
                toastr.error(data.message || "Failed to broadcast notice.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during notice broadcasting.");
        });
    });

    document.getElementById("edit-ticket-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="id"]').value;

        fetch("/sub-admin/complaints/" + id + "/update", {
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
                window.dispatchEvent(new CustomEvent('close-edit-ticket-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update ticket.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    function deleteTicket(id, row) {
        Swal.fire({
            title: 'Delete Complaint Ticket?',
            text: 'This will permanently delete this ticket record.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Ticket'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/sub-admin/complaints/" + id, {
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
                        row.delete();
                    } else {
                        toastr.error(data.message || "Failed to delete ticket.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during deletion.");
                });
            }
        });
    }

    // Auto-refresh complaints data every 10 seconds without page reload
    function refreshComplaintsTable() {
        fetch("{{ route('sub_admin.complaints_data') }}", {
            headers: {
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            table.setData(data);
        })
        .catch(err => {
            console.debug('Failed to auto-refresh complaints:', err);
        });
    }

    // Sync complaints table every 10 seconds
    setInterval(refreshComplaintsTable, 10000);
</script>
@endsection
