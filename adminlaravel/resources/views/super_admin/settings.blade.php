@extends('layouts.admin')

@section('title', 'System Audit Logs & Settings - Rudra Group PG')
@section('page_title', 'System Settings & Audit Logs')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
    <!-- Left Column: Global Settings & Security -->
    <div class="lg:col-span-5 space-y-6">
        <!-- Global Settings Form -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs h-fit">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-gear text-blue-600"></i> Global System Settings
            </h3>
            <form class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Default Electricity Unit Rate (₹)</label>
                    <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" value="₹10.00 / unit">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Security Deposit Multiplier</label>
                    <select class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">1.5x Monthly Rent</option>
                        <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">2.0x Monthly Rent</option>
                        <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Fixed ₹10,000</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Due Cutoff Date</label>
                    <select class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">5th of every month</option>
                        <option class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">10th of every month</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" checked id="wa" class="rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-blue-600 focus:ring-blue-500">
                    <label for="wa" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Automated WhatsApp Rent Reminders</label>
                </div>
                <button type="button" onclick="toastr.success('Global settings saved!')" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-xl shadow-md transition-all">
                    Save Platform Settings
                </button>
            </form>
        </div>

        <!-- Account Security / Change Password Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs"
             x-data="{ showCur: false, showNew: false, showConf: false }">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-500"></i> Account Password & Security
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Change your administrator login password</p>

            <form id="settings-change-password-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Current Password</label>
                    <div class="relative">
                        <input :type="showCur ? 'text' : 'password'" 
                               name="current_password" 
                               id="settings-cur-pwd"
                               required 
                               autocomplete="current-password"
                               placeholder="Enter your current password" 
                               class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <button type="button" @click="showCur = !showCur" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid text-xs" :class="showCur ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">New Password</label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" 
                               name="password" 
                               id="settings-new-pwd"
                               required 
                               minlength="6"
                               autocomplete="new-password"
                               placeholder="Minimum 6 characters" 
                               class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid text-xs" :class="showNew ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Confirm New Password</label>
                    <div class="relative">
                        <input :type="showConf ? 'text' : 'password'" 
                               name="password_confirmation" 
                               id="settings-conf-pwd"
                               required 
                               minlength="6"
                               autocomplete="new-password"
                               placeholder="Re-enter new password" 
                               class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <button type="button" @click="showConf = !showConf" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid text-xs" :class="showConf ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="settings-change-pwd-btn"
                        class="w-full bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold text-sm py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-lock"></i>
                    <span>Update Admin Password</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Immutable Audit Log Table -->
    <div class="lg:col-span-7 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-x-auto">
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-blue-600"></i> Immutable Audit Activity Trail
        </h3>
        <div id="audit-table"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var logsData = @json($auditLogs);

    var table = new Tabulator("#audit-table", {
        data: logsData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No System Audit Activity Found",
        columns: [
            {title: "ID", field: "id", minWidth: 80},
            {title: "Timestamp", field: "timestamp", minWidth: 160, formatter: function(cell) { return "<span class='text-slate-700 dark:text-slate-300'>" + cell.getValue() + "</span>"; }},
            {title: "User", field: "user", minWidth: 180, formatter: function(cell) { return "<span class='text-slate-900 dark:text-slate-100 font-semibold'>" + cell.getValue() + "</span>"; }},
            {title: "Action Performed", field: "action", minWidth: 200, formatter: function(cell) { return "<span class='text-slate-700 dark:text-slate-300'>" + cell.getValue() + "</span>"; }},
            {title: "Module", field: "module", minWidth: 110, formatter: function(cell){
                return '<span class="bg-slate-900 text-white text-xs font-medium px-2 py-0.5 rounded">' + cell.getValue() + '</span>';
            }},
            {title: "IP Address", field: "ip", minWidth: 120},
        ]
    });

    // In-page Admin Password Change Handler
    const settingsPwdForm = document.getElementById("settings-change-password-form");
    if (settingsPwdForm) {
        settingsPwdForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById("settings-change-pwd-btn");
            const originalHtml = submitBtn.innerHTML;

            const curPwd = document.getElementById("settings-cur-pwd").value;
            const newPwd = document.getElementById("settings-new-pwd").value;
            const confPwd = document.getElementById("settings-conf-pwd").value;

            if (!curPwd) {
                toastr.warning("Please enter your current password.");
                return;
            }
            if (newPwd.length < 6) {
                toastr.warning("New password must be at least 6 characters.");
                return;
            }
            if (newPwd !== confPwd) {
                toastr.warning("New password and confirmation do not match.");
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

            const formData = new FormData(settingsPwdForm);

            fetch("{{ route('account.change_password') }}", {
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
                            break;
                        }
                        toastr.error(firstError || data.message, "Validation Error");
                        return Promise.reject("Validation Error");
                    }
                    return Promise.reject(data.message || "Failed to update password.");
                }
                return data;
            })
            .then(data => {
                toastr.success(data.message || "Password changed successfully!");
                settingsPwdForm.reset();
            })
            .catch(err => {
                if (err !== "Validation Error") {
                    toastr.error(typeof err === 'string' ? err : "An error occurred while changing password.");
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
        });
    }
</script>
@endsection
