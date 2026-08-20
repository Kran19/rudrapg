@extends('layouts.admin')

@section('title', 'Branch Management - Rudra Group PG')
@section('page_title', 'PG Branch Directory & QR Generator')

@section('content')
<div x-data="{ addBranchModalOpen: false, editBranchModalOpen: false, qrModalOpen: false, qrBranchName: '', qrBranchCode: '', editForm: { id: '', name: '', city: '', address: '', phone: '', email: '', electricity_unit_rate: 10.0, manager_name: '', manager_phone: '' } }"
     @open-qr-modal.window="qrBranchName = $event.detail.name; qrBranchCode = $event.detail.code; qrModalOpen = true"
     @open-edit-branch-modal.window="editForm = { ...$event.detail }; editBranchModalOpen = true"
     @close-branch-modal.window="addBranchModalOpen = false"
     @close-edit-branch-modal.window="editBranchModalOpen = false">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">PG Branch Master Table</h3>
            <p class="text-xs text-slate-500">Manage PG branches, assign managers, set unit tariffs, and view QR standees.</p>
        </div>
        <button @click="addBranchModalOpen = true" 
                class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Add New PG Branch
        </button>
    </div>

    <!-- Tabulator Branch Directory Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
            <input type="text" id="branch-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search Branch Name, Code, Manager...">
            <button id="export-csv" class="px-3.5 py-2 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>
        <div id="branches-table"></div>
    </div>

    <!-- Pure Tailwind Modal: Add Branch -->
    <div x-show="addBranchModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="addBranchModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-building text-blue-400"></i> Create New PG Branch
                </h4>
                <button @click="addBranchModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="create-branch-form" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Code</label>
                        <input type="text" name="code" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. PG-BPL-05">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Name</label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Bopal Branch">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">City</label>
                    <input type="text" name="city" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Ahmedabad">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Physical Address</label>
                    <textarea name="address" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="2" placeholder="Full street address"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Phone</label>
                        <input type="text" name="phone" required pattern="\d{10}" maxlength="10" minlength="10" title="Phone number must be exactly 10 digits" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. 9876543210">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Email</label>
                        <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="bopal@rudrapg.com">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Unit Tariff (₹)</label>
                        <input type="number" step="0.5" name="electricity_unit_rate" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" value="10.00">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Manager Name</label>
                        <input type="text" name="manager_name" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Manager Name">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Manager Phone</label>
                        <input type="text" name="manager_phone" required pattern="\d{10}" maxlength="10" minlength="10" title="Phone number must be exactly 10 digits" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. 9876543210">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="addBranchModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Branch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: Edit Branch -->
    <div x-show="editBranchModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editBranchModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-building text-amber-400"></i> Edit PG Branch: <span x-text="editForm.name"></span>
                </h4>
                <button @click="editBranchModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-branch-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="editForm.id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Code (Read Only)</label>
                        <input type="text" name="code" readonly x-model="editForm.code" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm focus:outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Name</label>
                        <input type="text" name="name" required x-model="editForm.name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">City</label>
                    <input type="text" name="city" required x-model="editForm.city" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Physical Address</label>
                    <textarea name="address" required x-model="editForm.address" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="2"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Phone</label>
                        <input type="text" name="phone" required x-model="editForm.phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Branch Email</label>
                        <input type="email" name="email" required x-model="editForm.email" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Unit Tariff (₹)</label>
                        <input type="number" step="0.5" name="electricity_unit_rate" required x-model="editForm.electricity_unit_rate" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Manager Name</label>
                        <input type="text" name="manager_name" required x-model="editForm.manager_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Manager Phone</label>
                        <input type="text" name="manager_phone" required x-model="editForm.manager_phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="deleteBranch(editForm.id)" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1.5"><i class="fa-solid fa-trash"></i> Delete Branch</button>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="editBranchModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: QR Code Generator -->
    <div x-show="qrModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="qrModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-sm overflow-hidden text-center transform transition-all p-6">
            <div class="flex justify-end">
                <button @click="qrModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <h4 class="font-bold text-lg text-slate-900 dark:text-slate-100" x-text="qrBranchName">Naroda Branch</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono" x-text="qrBranchCode">PG-NRD-01</p>

            <div class="bg-white p-3 rounded-2xl border border-slate-200 dark:border-slate-700 my-4 inline-block shadow-md">
                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(qrBranchCode)" 
                     class="w-44 h-44 object-contain rounded-lg" 
                     alt="Branch Reception QR">
            </div>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Students must scan this QR standee with the Rudra PG Mobile App to start onboarding.</p>
            <div class="flex gap-2">
                <button @click="window.open('https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' + encodeURIComponent(qrBranchCode), '_blank')"
                        class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2.5 rounded-xl shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-download"></i> Download PNG
                </button>
                <button @click="window.print(); qrModalOpen = false" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 rounded-xl shadow-md flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-print"></i> Print Standee
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var branchData = @json($branches);

    var table = new Tabulator("#branches-table", {
        data: branchData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No PG Branches Found",
        columns: [
            {title: "Branch Code", field: "code", minWidth: 130, formatter: function(cell){
                return "<span class='bg-slate-900 text-white font-mono text-xs px-2.5 py-1 rounded-md'>" + cell.getValue() + "</span>";
            }},
            {title: "Branch Name", field: "name", minWidth: 180, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "City", field: "city", minWidth: 110},
            {title: "Manager", field: "manager_name", minWidth: 140},
            {title: "Unit Rate", field: "electricity_unit_rate", minWidth: 120, formatter: function(cell){
                return "₹" + cell.getValue() + " / unit";
            }},
            {title: "QR Standee", field: "qr_code_hash", minWidth: 120, formatter: function(cell){
                return '<button class="border border-blue-600 text-blue-600 hover:bg-blue-50 text-xs font-semibold py-1 px-2.5 rounded-lg transition-colors flex items-center gap-1"><i class="fa-solid fa-qrcode"></i> View QR</button>';
            }, cellClick: function(e, cell){
                var rowData = cell.getRow().getData();
                window.dispatchEvent(new CustomEvent('open-qr-modal', { detail: rowData }));
            }},
            {title: "Status", field: "status", minWidth: 110, hozAlign: "center", formatter: function(cell){
                var val = cell.getValue();
                var checked = (val === 'ACTIVE' || val === 'active') ? 'checked' : '';
                var id = cell.getRow().getData().id;
                return '<label class="relative inline-flex items-center cursor-pointer mt-1">' +
                       '  <input type="checkbox" class="sr-only peer status-toggle" data-id="' + id + '" ' + checked + '>' +
                       '  <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500"></div>' +
                       '</label>';
            }, cellClick: function(e, cell){
                if(e.target.classList.contains('status-toggle') || e.target.closest('.status-toggle')){
                    var data = cell.getRow().getData();
                    toggleBranchStatus(data.id, cell);
                }
            }},
            {title: "Actions", field: "id", minWidth: 100, formatter: function(cell){
                return '<div class="flex gap-1.5">' +
                       '  <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 edit-branch-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('edit-branch-btn') || e.target.closest('.edit-branch-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-branch-modal', { detail: data }));
                }
            }},
        ]
    });

    document.getElementById("branch-search").addEventListener("keyup", function(){
        table.setFilter("name", "like", this.value);
    });

    document.getElementById("export-csv").addEventListener("click", function(){
        table.download("csv", "branches.csv");
    });

    document.getElementById("create-branch-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);

        fetch("{{ route('super_admin.branches.store') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    let firstError = "";
                    for (const field in data.errors) {
                        firstError = data.errors[field][0];
                        break; // Stop at the first error field
                    }
                    toastr.error(firstError, "Validation Error");
                    return Promise.reject("Validation Error");
                }
                return Promise.reject(data.message || "Server Error");
            }
            return data;
        })
        .then(data => {
            if (data.status === "success") {
                toastr.success(data.message);
                window.dispatchEvent(new CustomEvent('close-branch-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to create Branch.");
            }
        })
        .catch(err => {
            if (err !== "Validation Error") {
                toastr.error(typeof err === 'string' ? err : "An error occurred during submission.");
            }
        });
    });

    document.getElementById("edit-branch-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="id"]').value;

        fetch(appUrl("super-admin/branches/" + id + "/update"), {
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
                window.dispatchEvent(new CustomEvent('close-edit-branch-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update branch.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    function toggleBranchStatus(id, cell) {
        fetch(appUrl("super-admin/branches/" + id + "/toggle-status"), {
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
                cell.setValue(data.status_val);
            } else {
                toastr.error(data.message || "Failed to update status.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred while updating status.");
        });
    }

    function deleteBranch(id) {
        Swal.fire({
            title: 'Delete PG Branch?',
            text: 'This will permanently remove this branch and all its associated rooms, beds, and data.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Branch'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("super-admin/branches/" + id), {
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
                        window.dispatchEvent(new CustomEvent('close-edit-branch-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to delete branch.");
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
