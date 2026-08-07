@extends('layouts.admin')

@section('title', 'Sub Admin Management - Rudra Group PG')
@section('page_title', 'Sub Admin Directory & Branch Assignment')

@section('content')
<div x-data="{ modalOpen: false }"
     @close-subadmin-modal.window="modalOpen = false">
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
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="subadmin-search" 
                   class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search Sub Admin name, email, branch...">
            <button id="export-subadmins-csv" class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
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
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden transform transition-all">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Suresh Patel">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address (Login Username)</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="suresh@rudrapg.com">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="+91 98765 12345">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Account Password</label>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="••••••••">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Assign Controlled Branches</label>
                    <div class="space-y-2 max-h-36 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                        @foreach($allBranches as $branch)
                            <label class="flex items-center gap-2.5 text-sm text-slate-700 font-medium">
                                <input type="checkbox" name="branches[]" value="{{ $branch->id }}" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                {{ $branch->name }} ({{ $branch->code }})
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var subAdminData = @json($subAdmins);

    var table = new Tabulator("#subadmins-table", {
        data: subAdminData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No Sub Admin Accounts Found",
        columns: [
            {title: "ID", field: "id", width: 110},
            {title: "Sub Admin Name", field: "name", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Email (Login)", field: "email"},
            {title: "Phone", field: "phone"},
            {title: "Assigned Branches", field: "assigned_branches", formatter: function(cell){
                var branches = cell.getValue() || [];
                var html = "";
                branches.forEach(function(b){
                    html += '<span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md me-1">' + b + '</span>';
                });
                return html || '<span class="text-slate-400 text-xs">None</span>';
            }},
            {title: "Status", field: "status", width: 100, formatter: function(cell){
                return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Active</span>';
            }},
            {title: "Created Date", field: "created_at", width: 130},
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
                table.addData([data.data], true);
                toastr.success(data.message);
                window.dispatchEvent(new CustomEvent('close-subadmin-modal'));
                this.reset();
            } else {
                toastr.error(data.message || "Failed to create Sub Admin.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });
</script>
@endsection
