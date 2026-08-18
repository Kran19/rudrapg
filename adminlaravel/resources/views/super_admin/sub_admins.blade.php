@extends('layouts.admin')

@section('title', 'Sub Admin Management - Rudra Group PG')
@section('page_title', 'Sub Admin Directory & Branch Assignment')

@section('content')
<div x-data="{ modalOpen: false, editModalOpen: false, editForm: { db_id: '', name: '', email: '', phone: '', password: '', branch_ids: [] } }"
     @open-edit-subadmin-modal.window="editForm = { ...$event.detail }; editModalOpen = true"
     @close-subadmin-modal.window="modalOpen = false; editModalOpen = false">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Sub Admin Directory</h3>
            <p class="text-xs text-slate-500">Create Sub Admin accounts and assign operational control over specific PG branches.</p>
        </div>
        <button @click="modalOpen = true" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add Sub Admin Account
        </button>
    </div>

    <!-- Tabulator Sub Admin Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="subadmin-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                   placeholder="🔍 Search Sub Admin name, email, branch...">
            <button id="export-subadmins-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Directory CSV
            </button>
        </div>
        <div id="subadmins-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Add Sub Admin -->
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="modalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-blue-400"></i> Create Sub Admin Account
                </h4>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="create-subadmin-form" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="e.g. Suresh Patel">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address (Login Username)</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="suresh@rudrapg.com">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="+91 98765 12345">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Account Password</label>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="••••••••">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Assign Controlled Branches</label>
                    <div class="space-y-2 max-h-36 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        @foreach($allBranches as $branch)
                            <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 font-medium">
                                <input type="checkbox" name="branches[]" value="{{ $branch->id }}" checked class="rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-blue-600 focus:ring-blue-500">
                                {{ $branch->name }} ({{ $branch->code }})
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: Edit Sub Admin -->
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
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-amber-400"></i> Edit Sub Admin: <span x-text="editForm.name"></span>
                </h4>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-subadmin-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="db_id" x-model="editForm.db_id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" required x-model="editForm.name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address (Login Username)</label>
                    <input type="email" name="email" required x-model="editForm.email" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" required x-model="editForm.phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="••••••••">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Assign Controlled Branches</label>
                    <div class="space-y-2 max-h-36 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        @foreach($allBranches as $branch)
                            <label class="flex items-center gap-2.5 text-sm text-slate-700 dark:text-slate-300 font-medium">
                                <input type="checkbox" name="branches[]" value="{{ $branch->id }}" 
                                       :checked="editForm.branch_ids && editForm.branch_ids.includes({{ $branch->id }})"
                                       class="rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-blue-600 focus:ring-blue-500">
                                {{ $branch->name }} ({{ $branch->code }})
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="deleteSubAdmin(editForm.db_id)" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1.5"><i class="fa-solid fa-trash"></i> Delete Account</button>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
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
    var subAdminsData = @json($subAdmins);

    var table = new Tabulator("#subadmins-table", {
        data: subAdminsData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Sub Admin Accounts Found",
        columns: [
            {title: "ID", field: "id", minWidth: 110},
            {title: "Sub Admin Name", field: "name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Email (Login)", field: "email", minWidth: 180},
            {title: "Phone", field: "phone", minWidth: 130},
            {title: "Assigned Branches", field: "assigned_branches", minWidth: 180, formatter: function(cell){
                var branches = cell.getValue() || [];
                var html = "";
                branches.forEach(function(b){
                    html += '<span class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold px-2.5 py-1 rounded-md me-1">' + b + '</span>';
                });
                return html || '<span class="text-slate-400 text-xs">None</span>';
            }},
            {title: "Status", field: "status", minWidth: 110, hozAlign: "center", formatter: function(cell){
                var val = cell.getRow().getData().raw_status;
                var checked = (val === 'ACTIVE' || val === 'active') ? 'checked' : '';
                var id = cell.getRow().getData().db_id;
                return '<label class="relative inline-flex items-center cursor-pointer mt-1">' +
                       '  <input type="checkbox" class="sr-only peer status-toggle" data-id="' + id + '" ' + checked + '>' +
                       '  <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>' +
                       '</label>';
            }, cellClick: function(e, cell){
                if(e.target.classList.contains('status-toggle') || e.target.closest('.status-toggle')){
                    var data = cell.getRow().getData();
                    toggleSubAdminStatus(data.db_id, cell);
                }
            }},
            {title: "Created Date", field: "created_at", minWidth: 130},
            {title: "Actions", field: "db_id", minWidth: 100, formatter: function(cell){
                return '<div class="flex gap-1.5">' +
                       '  <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 edit-subadmin-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('edit-subadmin-btn') || e.target.closest('.edit-subadmin-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-subadmin-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("subadmin-search").addEventListener("keyup", function(){
        table.setFilter("name", "like", this.value);
    });

    document.getElementById("export-subadmins-csv").addEventListener("click", function(){
        table.download("csv", "sub_admins_directory.csv");
    });

    document.getElementById("create-subadmin-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);

        fetch("{{ route('super_admin.sub_admins.store') }}", {
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
                window.dispatchEvent(new CustomEvent('close-subadmin-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to create Sub Admin.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    document.getElementById("edit-subadmin-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="db_id"]').value;

        fetch("/super-admin/sub-admins/" + id + "/update", {
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
                window.dispatchEvent(new CustomEvent('close-subadmin-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update Sub Admin.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    function toggleSubAdminStatus(id, cell) {
        fetch("/super-admin/sub-admins/" + id + "/toggle-status", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                toastr.success(data.message);
                var rowData = cell.getRow().getData();
                rowData.raw_status = data.status_val;
                cell.getRow().update(rowData);
            } else {
                toastr.error(data.message || "Failed to update status.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred while updating status.");
        });
    }

    function deleteSubAdmin(id) {
        Swal.fire({
            title: 'Delete Sub Admin Account?',
            text: 'This will revoke all administrative branch privileges for this user.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Account'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("/super-admin/sub-admins/" + id, {
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
                        window.dispatchEvent(new CustomEvent('close-edit-subadmin-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to delete Sub Admin.");
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
