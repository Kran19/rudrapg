@extends('layouts.admin')

@section('title', 'Electricity Bill Audit - Rudra Group PG')
@section('page_title', 'Monthly Electricity Meter Reading Audit')

@section('content')
<div x-data="{ 
        auditModalOpen: false, 
        editModalOpen: false, 
        recordModalOpen: false,
        lightboxOpen: false, 
        lightboxSrc: '', 
        lightboxTitle: '', 
        activeReading: {}, 
        editForm: { id: '', student: '', room: '', prev_reading: 0, curr_reading: 0, raw_rate: 0, raw_status: '' },
        recordForm: { student_id: '', current_reading: '', reading_month: '{{ date('F Y') }}', prev_reading: 0, units: 0, total: 0, unit_rate: 10 }
     }"
     @open-audit-modal.window="activeReading = $event.detail; auditModalOpen = true"
     @close-audit-modal.window="auditModalOpen = false"
     @open-edit-modal.window="editForm = { ...$event.detail }; editModalOpen = true"
     @close-edit-modal.window="editModalOpen = false">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Monthly Sub-Meter Readings & Automatic Billing</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Compare move-in registration baseline photos with month-end readings to calculate units and dues.</p>
        </div>
        <button @click="recordModalOpen = true" 
                class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-sm font-bold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-camera"></i> Record Month-End Reading
        </button>
    </div>

    <!-- Tabulator Electricity Readings Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="elec-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                   placeholder="🔍 Search resident, room...">
            <button id="export-elec-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Audit CSV
            </button>
        </div>
        <div id="electricity-table"></div>
    </div>

    <!-- Room Electricity Bill Splitter & Fair Pro-Rata Calculator -->
    @include('sub_admin.partials.room_electricity_splitter')

    <!-- Fullscreen Image Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" style="display: none;"
         x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[60] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-4xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-plus text-amber-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <i class="fa-solid fa-xmark mr-1"></i> Close Lightbox (Esc)
            </button>
        </div>
        <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" 
                 x-on:error="$event.target.src = 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80'"
                 class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-transform hover:scale-125 cursor-zoom-in">
        </div>
    </div>

    <!-- Modal: Meter Audit & Baseline Comparison -->
    <div x-show="auditModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <div @click.away="auditModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-2xl overflow-hidden transform transition-all my-8">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-900 p-5 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-bolt"></i> Audit Sub-Meter Reading & Calculate Bill
                    </h4>
                    <p class="text-xs text-slate-900/80 mt-0.5" x-text="activeReading.student + ' (' + (activeReading.room ? 'Room ' + activeReading.room : 'N/A') + ')'"></p>
                </div>
                <button @click="auditModalOpen = false" class="text-slate-900/80 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Move-in Baseline vs Current Month Comparison Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Move-in Baseline Registration Photo -->
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 bg-slate-50 dark:bg-slate-900">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase flex items-center gap-1">
                                <i class="fa-solid fa-house-user text-blue-500"></i> Move-in Photo
                            </span>
                            <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400" x-text="(activeReading.initial_reading || activeReading.prev_reading) + ' kWh'"></span>
                        </div>
                        <div class="relative group cursor-pointer overflow-hidden rounded-lg bg-slate-200 dark:bg-slate-800 text-center"
                             @click="activeReading.initial_photo_url && (lightboxSrc = activeReading.initial_photo_url, lightboxTitle = 'Move-in Registration Photo (' + (activeReading.initial_reading || activeReading.prev_reading) + ' kWh) - Room ' + activeReading.room, lightboxOpen = true)">
                            <template x-if="activeReading.initial_photo_url">
                                <img :src="activeReading.initial_photo_url" class="w-full h-36 object-contain rounded-lg group-hover:scale-105 transition-all">
                            </template>
                            <template x-if="!activeReading.initial_photo_url">
                                <div class="h-36 flex flex-col items-center justify-center text-slate-400 text-xs">
                                    <i class="fa-solid fa-bolt text-2xl mb-1 text-slate-300"></i>
                                    <span>Initial Registration</span>
                                </div>
                            </template>
                        </div>
                        <span class="text-[10px] text-slate-400 block text-center mt-1">Starting Baseline at Registration</span>
                    </div>

                    <!-- Current Month-End Reading Photo -->
                    <div class="border border-emerald-200 dark:border-emerald-800 rounded-xl p-3 bg-emerald-50/50 dark:bg-emerald-950/20">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 uppercase flex items-center gap-1">
                                <i class="fa-solid fa-calendar-check text-emerald-600"></i> Month-End Photo
                            </span>
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400" x-text="activeReading.curr_reading + ' kWh'"></span>
                        </div>
                        <div class="relative group cursor-pointer overflow-hidden rounded-lg bg-slate-200 dark:bg-slate-800 text-center"
                             @click="activeReading.photo_url && (lightboxSrc = activeReading.photo_url, lightboxTitle = 'Month-End Photo (' + activeReading.curr_reading + ' kWh) - Room ' + activeReading.room, lightboxOpen = true)">
                            <img :src="activeReading.photo_url" 
                                 x-on:error="$event.target.src = 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80'"
                                 class="w-full h-36 object-contain rounded-lg group-hover:scale-105 transition-all">
                        </div>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block text-center mt-1">Submitted at Month-End</span>
                    </div>
                </div>

                <!-- Transparent Mathematical Calculation Box -->
                <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Previous / Baseline Reading:</span>
                        <strong class="text-slate-900 dark:text-slate-100" x-text="activeReading.prev_reading + ' kWh'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Current Month-End Reading:</span>
                        <strong class="text-blue-600 dark:text-blue-400" x-text="activeReading.curr_reading + ' kWh'"></strong>
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Net Units Consumed:</span>
                        <strong class="text-amber-600 dark:text-amber-400 font-extrabold" x-text="activeReading.units + ' Units (kWh)'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Tariff Rate:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeReading.rate + ' per unit'"></span>
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 my-1"></div>
                    <div class="flex justify-between items-center text-sm pt-1">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Total Calculated Electricity Due:</span>
                        <strong class="font-extrabold text-lg text-emerald-600 dark:text-emerald-400" x-text="activeReading.total"></strong>
                    </div>
                </div>

                <!-- Approval Actions -->
                <div x-show="activeReading && activeReading.status !== 'Approved' && activeReading.status !== 'APPROVED'" class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="rejectReading(activeReading.id)" 
                            class="px-4 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 border border-rose-200 dark:border-rose-900 rounded-xl">Reject Reading</button>
                    <button type="button" @click="approveReading(activeReading.id)" 
                            class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Approve Bill & Add to Dues
                    </button>
                </div>
                <div x-show="activeReading && (activeReading.status === 'Approved' || activeReading.status === 'APPROVED')" class="flex items-center justify-between pt-4 border-t border-emerald-100 dark:border-emerald-900">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i> Reading Audited, Bill Generated & Added to Dues</span>
                    <button type="button" @click="auditModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 rounded-xl">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Record Month-End Reading -->
    <div x-show="recordModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto"
         style="display: none;">
        
        <div @click.away="recordModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all my-8">
            <div class="bg-amber-500 text-slate-900 p-5 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-base flex items-center gap-2">
                        <i class="fa-solid fa-camera"></i> Record Month-End Meter Reading
                    </h4>
                    <p class="text-xs text-slate-900/80 mt-0.5">Enter month-end reading to automatically calculate units & bill.</p>
                </div>
                <button @click="recordModalOpen = false" class="text-slate-900/80 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="record-reading-form" @submit.prevent="submitMonthlyReading" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Resident Student & Room</label>
                    <select name="student_id" x-model="recordForm.student_id" @change="onRecordStudentChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Choose Resident --</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}" data-room="{{ $st->room ? $st->room->room_number : 'N/A' }}" data-initial="{{ $st->initial_meter_reading ?? 0 }}">
                                {{ $st->full_name }} ({{ $st->room ? 'Room ' . $st->room->room_number : 'Unassigned' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Previous / Baseline (kWh)</label>
                        <input type="number" step="0.01" readonly x-model="recordForm.prev_reading" 
                               class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm font-semibold cursor-not-allowed">
                        <span class="text-[11px] text-slate-400 mt-1 block">Registration or last month reading</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 text-emerald-700 dark:text-emerald-400">
                            Month-End Reading (kWh)
                        </label>
                        <input type="number" step="0.01" min="0" name="current_reading" x-model.number="recordForm.current_reading" @input="calculateRecordBill()" required 
                               class="w-full px-3.5 py-2.5 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-300 dark:border-emerald-700 text-slate-900 dark:text-slate-100 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none" 
                               placeholder="e.g. 1174.50">
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 block">Current meter display</span>
                    </div>
                </div>

                <!-- Live Bill Calculation Preview -->
                <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-amber-800 dark:text-amber-300">Units Consumed:</span>
                        <strong class="text-amber-900 dark:text-amber-200" x-text="recordForm.units + ' kWh'"></strong>
                    </div>
                    <div class="flex items-center justify-between text-xs mt-1">
                        <span class="font-bold text-amber-800 dark:text-amber-300">Tariff Rate:</span>
                        <span class="text-slate-700 dark:text-slate-300">₹10.00 / unit</span>
                    </div>
                    <div class="border-t border-amber-200 dark:border-amber-800 my-2"></div>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs uppercase text-slate-700 dark:text-slate-300">Calculated Bill:</span>
                        <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400" x-text="'₹' + Number(recordForm.total).toLocaleString('en-IN')"></span>
                    </div>
                </div>

                <!-- Month-End Meter Photo Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Upload Month-End Physical Meter Photo</label>
                    <input type="file" name="meter_photo" accept="image/*" 
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block">Attach clear snapshot showing current reading.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="recordModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-slate-900 bg-amber-500 hover:bg-amber-600 rounded-xl shadow-md">
                        Record & Generate Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var readingsData = @json($readings);

    var table = new Tabulator("#electricity-table", {
        data: readingsData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Electricity Submissions Found",
        columns: [
            {title: "Reading ID", field: "code", minWidth: 120, formatter: function(cell){
                return "<span class='text-blue-600 hover:text-blue-800 dark:text-blue-400 font-semibold cursor-pointer underline'>" + cell.getValue() + "</span>";
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                window.dispatchEvent(new CustomEvent('open-audit-modal', { detail: data }));
            }},
            {title: "Student Name", field: "student", minWidth: 150, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Room No.", field: "room", minWidth: 95},
            {title: "Cycle / Month", field: "reading_month", minWidth: 140},
            {title: "Previous", field: "prev_reading", minWidth: 105, formatter: function(cell){
                return cell.getValue() + " kWh";
            }},
            {title: "Current", field: "curr_reading", minWidth: 105, formatter: function(cell){
                return cell.getValue() + " kWh";
            }},
            {title: "Units Consumed", field: "units", minWidth: 120, formatter: function(cell){
                return "<strong>" + cell.getValue() + " Units</strong>";
            }},
            {title: "Bill Amount", field: "total", minWidth: 110, formatter: function(cell){
                return "<strong class='text-emerald-600 dark:text-emerald-400 font-bold'>" + cell.getValue() + "</strong>";
            }},
            {title: "Status", field: "status", minWidth: 120, formatter: function(cell){
                var val = (cell.getValue() || '').toUpperCase();
                return (val === "APPROVED" || val === "Approved")
                    ? '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Approved</span>'
                    : '<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">Pending Audit</span>';
            }},
            {title: "Audit Action", field: "id", minWidth: 110, formatter: function(cell){
                return '<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1"><i class="fa-solid fa-bolt"></i> Audit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                window.dispatchEvent(new CustomEvent('open-audit-modal', { detail: data }));
            }},
        ]
    });

    document.getElementById("elec-search").addEventListener("keyup", function(){
        table.setFilter("student", "like", this.value);
    });

    document.getElementById("export-elec-csv").addEventListener("click", function(){
        table.download("csv", "electricity_meter_audit.csv");
    });

    function approveReading(id) {
        fetch(appUrl("sub-admin/electricity-audit/" + id + "/approve"), {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            toastr.success(data.message);
            window.dispatchEvent(new CustomEvent('close-audit-modal'));
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            toastr.error("Failed to approve electricity reading.");
        });
    }

    function rejectReading(id) {
        fetch(appUrl("sub-admin/electricity-audit/" + id + "/reject"), {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            toastr.error(data.message);
            window.dispatchEvent(new CustomEvent('close-audit-modal'));
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            toastr.error("Failed to reject electricity reading.");
        });
    }

    function onRecordStudentChange() {
        var select = document.querySelector('select[name="student_id"]');
        var selected = select.options[select.selectedIndex];
        var initial = parseFloat(selected.getAttribute('data-initial')) || 0;

        // Check if there are past readings in readingsData for this student
        var studentId = select.value;
        var pastReadings = readingsData.filter(r => String(r.student_id) === String(studentId));
        var prevReading = initial;
        if (pastReadings.length > 0) {
            prevReading = pastReadings[0].curr_reading;
        }

        var alpine = Alpine.$data(document.querySelector('[x-data]'));
        if (alpine) {
            alpine.recordForm.prev_reading = prevReading;
            alpine.calculateRecordBill();
        }
    }

    function calculateRecordBill() {
        var alpine = Alpine.$data(document.querySelector('[x-data]'));
        if (alpine) {
            var curr = parseFloat(alpine.recordForm.current_reading) || 0;
            var prev = parseFloat(alpine.recordForm.prev_reading) || 0;
            var units = Math.max(0, curr - prev);
            var rate = 10.0;
            alpine.recordForm.units = Math.round(units * 100) / 100;
            alpine.recordForm.total = Math.round(units * rate * 100) / 100;
        }
    }

    function submitMonthlyReading() {
        var form = document.getElementById("record-reading-form");
        var formData = new FormData(form);

        fetch("{{ route('sub_admin.electricity_audit.record_reading') }}", {
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
                var alpine = Alpine.$data(document.querySelector('[x-data]'));
                if (alpine) alpine.recordModalOpen = false;
                setTimeout(() => window.location.reload(), 1200);
            } else {
                toastr.error(data.message || "Failed to record reading.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    }
</script>
@endsection
