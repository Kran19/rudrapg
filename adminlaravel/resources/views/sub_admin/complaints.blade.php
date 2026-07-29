@extends('layouts.admin')

@section('title', 'Complaints & Notice Desk - Rudra Group PG')
@section('page_title', 'Complaints Desk & Branch Announcement Notices')

@section('content')
<div x-data="{ noticeModalOpen: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Branch Complaints & Announcements</h3>
            <p class="text-xs text-slate-500">Track student maintenance service tickets and publish branch announcement notices.</p>
        </div>
        <button @click="noticeModalOpen = true" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-bullhorn"></i> Broadcast Branch Notice
        </button>
    </div>

    <!-- Tabulator Complaints Table -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-headset text-blue-600"></i> Active Maintenance Tickets (Tabulator.js)
            </h4>
            <input type="text" id="ticket-search" 
                   class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                   placeholder="🔍 Search ticket title...">
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
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-blue-400"></i> Broadcast Branch Notice
                </h4>
                <button @click="noticeModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notice Title</label>
                    <input type="text" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. Water Tank Cleaning Schedule">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notice Details</label>
                    <textarea class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="3" placeholder="Enter instructions for residents..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Priority Category</label>
                    <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option>Important Announcement</option>
                        <option>Maintenance Notice</option>
                        <option>Rent Due Notice</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="noticeModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="button" @click="noticeModalOpen = false; toastr.success('Notice broadcasted to Flutter Student Mobile App!')" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Broadcast Notice</button>
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
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        columns: [
            {title: "Ticket No", field: "ticket", width: 130, formatter: function(cell){
                return "<code class='bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-xs'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student", formatter: function(cell){
                return "<strong class='text-slate-900'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room No", field: "room", width: 100},
            {title: "Category", field: "category", width: 120, formatter: function(cell){
                return '<span class="bg-slate-100 text-slate-700 text-xs font-medium px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Issue Title", field: "title"},
            {title: "Priority", field: "priority", width: 110, formatter: function(cell){
                var p = cell.getValue();
                if(p === 'High') return '<span class="bg-rose-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">High</span>';
                if(p === 'Medium') return '<span class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">Medium</span>';
                return '<span class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">Low</span>';
            }},
            {title: "Created Date", field: "date", width: 120},
            {title: "Status", field: "status", width: 120, formatter: function(cell){
                var s = cell.getValue();
                if(s === 'Resolved') return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Resolved</span>';
                if(s === 'In Progress') return '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">In Progress</span>';
                return '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">Open</span>';
            }},
        ]
    });

    document.getElementById("ticket-search").addEventListener("keyup", function(){
        table.setFilter("title", "like", this.value);
    });
</script>
@endsection
