@extends('layouts.admin')

@section('title', 'System Audit Logs & Settings - Rudra Group PG')
@section('page_title', 'System Settings & Audit Logs')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
    <!-- Global Settings Form -->
    <div class="lg:col-span-5 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs h-fit">
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
</script>
@endsection
