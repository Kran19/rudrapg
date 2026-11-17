<!-- Room Electricity Bill Splitter & Fair Pro-Rata Calculator Component -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 transition-all" id="room-electricity-splitter-section">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-700/60">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-bolt-lightning"></i>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        Room Electricity Bill Splitter & Pro-Rata Calculator
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Calculate room light bills directly from sub-meter readings and divide fairly among roommates based on move-in date & duration of stay.
                    </p>
                </div>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
            Pro-Rata Segmentation Active
        </span>
    </div>

    <!-- Step 1: Selection & Configuration -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
        <!-- Room Selector -->
        <div>
            <label for="splitter-room-id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                1. Select Room <span class="text-rose-500">*</span>
            </label>
            <select id="splitter-room-id" onchange="onSplitterRoomChanged()"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                <option value="">-- Choose Room --</option>
                @if(isset($rooms))
                    @foreach($rooms as $rm)
                        <option value="{{ $rm->id }}">
                            Room {{ $rm->room_number }} (Floor {{ $rm->floor_number }}, {{ $rm->students_count ?? 0 }} Occupants)
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Billing Month -->
        <div>
            <label for="splitter-billing-month" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                2. Billing Month <span class="text-rose-500">*</span>
            </label>
            <input type="month" id="splitter-billing-month" value="{{ date('Y-m') }}" onchange="onSplitterMonthChanged()"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
        </div>

        <!-- Room Residents Summary Pill -->
        <div class="flex flex-col justify-end">
            <div id="sp-occupants-summary-box" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-users text-blue-500"></i>
                    <span id="sp-occupants-label">Select a room to view occupants</span>
                </div>
                <span id="sp-occupants-badge" class="font-bold text-slate-700 dark:text-slate-200">-</span>
            </div>
        </div>
    </div>

    <!-- Step 2: Sub-Meter Reading & Live Calculation Section -->
    <div id="sp-meter-section" class="p-5 rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/60 mb-6 transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-gauge-high text-amber-600 dark:text-amber-400"></i>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    Room Sub-Meter Reading & Automatic Bill Calculation
                </h4>
            </div>
            <button type="button" onclick="toggleDirectBillMode()" id="btn-toggle-direct-mode" 
                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 underline">
                Switch to direct bill entry (₹)
            </button>
        </div>

        <!-- Meter Reading Input Row -->
        <div id="sp-meter-inputs-row" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Previous Reading -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="sp-prev-reading" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Previous Reading (kWh)
                    </label>
                    <span id="sp-baseline-source-tag" class="text-[10px] text-slate-400 truncate max-w-[140px]" title="Baseline">Baseline: 0</span>
                </div>
                <div class="relative">
                    <input type="number" step="0.01" id="sp-prev-reading" placeholder="0.00" oninput="liveRecalcMeterBill()"
                           class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <button type="button" id="btn-view-baseline-photo" onclick="viewBaselinePhoto()" style="display: none;"
                            class="absolute right-2 top-2 px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200">
                        <i class="fa-solid fa-image"></i> Photo
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Move-in baseline or last month's final reading</p>
            </div>

            <!-- Current Reading -->
            <div>
                <label for="sp-curr-reading" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Current Reading (kWh) <span class="text-rose-500">*</span>
                </label>
                <input type="number" step="0.01" id="sp-curr-reading" placeholder="Enter month-end reading" oninput="liveRecalcMeterBill()"
                       class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                <p class="text-[10px] text-slate-400 mt-1">Read directly from room's sub-meter display</p>
            </div>

            <!-- Unit Rate -->
            <div>
                <label for="sp-unit-rate" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Unit Rate (₹ / kWh)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">₹</span>
                    <input type="number" step="0.5" id="sp-unit-rate" value="10.00" oninput="liveRecalcMeterBill()"
                           class="w-full pl-8 pr-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Branch default electricity tariff rate</p>
            </div>

            <!-- Month-End Meter Photo (Optional) -->
            <div>
                <label for="sp-meter-photo" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Month-End Meter Photo
                </label>
                <input type="file" id="sp-meter-photo" accept="image/*"
                       class="w-full px-2 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-600 dark:text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
                <p class="text-[10px] text-slate-400 mt-1">Audit proof for roommates</p>
            </div>
        </div>

        <!-- Direct Bill Input (Hidden by default, toggled via link) -->
        <div id="sp-direct-bill-row" style="display: none;" class="mb-4">
            <div class="max-w-md">
                <label for="splitter-total-bill" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Fixed Total Room Electricity Bill (₹) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">₹</span>
                    <input type="number" id="splitter-total-bill" placeholder="e.g. 3000" min="0" step="1"
                           class="w-full pl-8 pr-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Enter fixed electricity board bill slip amount directly</p>
            </div>
        </div>

        <!-- Live Computed Consumption Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-white dark:bg-slate-900 border border-amber-200/60 dark:border-amber-800/40">
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Units Consumed</span>
                    <p class="text-base font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">
                        <span id="sp-live-units">0.00</span> <span class="text-xs font-normal text-slate-400">kWh</span>
                    </p>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Calculation Formula</span>
                    <p id="sp-live-formula" class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-0.5">
                        (0.00 - 0.00) × ₹10.00/u
                    </p>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-400 tracking-wider">Total Room Bill</span>
                    <p class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">
                        ₹<span id="sp-live-total-bill">0.00</span>
                    </p>
                </div>
            </div>

            <!-- Trigger Button -->
            <button type="button" id="btn-calculate-splitter" onclick="calculateRoomSplitter()"
                    class="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm py-2.5 px-6 rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-calculator"></i>
                <span>Calculate Fair Split</span>
            </button>
        </div>
    </div>

    <!-- Loading State Indicator -->
    <div id="splitter-loading" class="hidden py-8 text-center">
        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-semibold">
            <i class="fa-solid fa-circle-notch fa-spin text-amber-600 text-sm"></i>
            <span>Calculating units & segmenting periods based on move-in dates...</span>
        </div>
    </div>

    <!-- Error Banner -->
    <div id="splitter-error-banner" class="hidden mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium flex items-center gap-2.5">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
        <span id="splitter-error-message"></span>
    </div>

    <!-- Results Display Section -->
    <div id="splitter-results-panel" class="hidden space-y-6">
        <!-- Room & Billing High-Level Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">Target Room</span>
                <p id="sp-metric-room" class="text-sm font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">-</p>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">Billing Cycle</span>
                <p id="sp-metric-month" class="text-sm font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">-</p>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">Daily Room Rate</span>
                <p id="sp-metric-daily" class="text-sm font-extrabold text-amber-600 dark:text-amber-400 mt-0.5">-</p>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">Total Room Bill</span>
                <p id="sp-metric-total" class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">-</p>
            </div>
        </div>

        <!-- Period Breakdown Timeline / Segment Cards -->
        <div>
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fa-solid fa-timeline text-amber-500"></i> Step-by-Step Period Segmentation
            </h4>
            <div id="sp-periods-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- Injected via JavaScript -->
            </div>
        </div>

        <!-- Resident Breakdown Table -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users text-blue-500"></i> Individual Resident Allocation
                </h4>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">Amounts are editable before pushing</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-700">
                            <th class="py-3 px-4">Resident</th>
                            <th class="py-3 px-3">Bed Code</th>
                            <th class="py-3 px-3">Move-In Date</th>
                            <th class="py-3 px-3">Active Days</th>
                            <th class="py-3 px-4">Period-Wise Calculation Share</th>
                            <th class="py-3 px-4 text-right">Final Bill Share (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="sp-residents-tbody" class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                        <!-- Injected via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Verification & Push Footer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/60">
            <div class="flex items-center gap-3">
                <span id="sp-balance-badge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-check-circle"></i> Balanced: ₹<span id="sp-sum-shares">0</span> / ₹<span id="sp-target-bill">0</span>
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">
                    Sum of resident shares perfectly matches the room bill.
                </span>
            </div>

            <button type="button" id="btn-push-split-dues" onclick="pushRoomSplitDues()"
                    class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-5 rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Generate & Push Bills to Residents' Dues</span>
            </button>
        </div>
    </div>
</div>

<!-- Pro-Rata Splitter JavaScript Engine -->
<script>
    var currentSplitData = null;
    var cachedRoomInfo = null;
    var isDirectBillMode = false;

    function toggleDirectBillMode() {
        isDirectBillMode = !isDirectBillMode;
        var meterRow = document.getElementById("sp-meter-inputs-row");
        var directRow = document.getElementById("sp-direct-bill-row");
        var toggleBtn = document.getElementById("btn-toggle-direct-mode");

        if (isDirectBillMode) {
            meterRow.style.display = "none";
            directRow.style.display = "block";
            toggleBtn.innerText = "Switch to meter reading calculation (kWh)";
        } else {
            meterRow.style.display = "grid";
            directRow.style.display = "none";
            toggleBtn.innerText = "Switch to direct bill entry (₹)";
            liveRecalcMeterBill();
        }
    }

    function onSplitterRoomChanged() {
        var roomId = document.getElementById("splitter-room-id").value;
        var month = document.getElementById("splitter-billing-month").value;
        if (!roomId) {
            document.getElementById("sp-occupants-label").innerText = "Select a room to view occupants";
            document.getElementById("sp-occupants-badge").innerText = "-";
            return;
        }

        // Fetch room meter baseline and active occupants
        fetch("{{ url('sub-admin/room-electricity-info') }}/" + roomId + "?billing_month=" + encodeURIComponent(month), {
            headers: {
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success" && res.data) {
                var d = res.data;
                cachedRoomInfo = d;

                // Update occupants summary
                var count = d.active_residents ? d.active_residents.length : 0;
                document.getElementById("sp-occupants-label").innerText = count + " Active Occupants in Room " + d.room_number;
                document.getElementById("sp-occupants-badge").innerText = count + " Roommates";

                // Update previous reading and baseline source
                document.getElementById("sp-prev-reading").value = d.previous_reading !== null ? d.previous_reading : 0;
                document.getElementById("sp-baseline-source-tag").innerText = d.baseline_source || "Baseline: 0";
                document.getElementById("sp-unit-rate").value = d.unit_rate || 10.0;

                // Photo preview button
                var photoBtn = document.getElementById("btn-view-baseline-photo");
                if (d.baseline_photo_url) {
                    photoBtn.style.display = "inline-block";
                    photoBtn.setAttribute("data-src", d.baseline_photo_url);
                } else {
                    photoBtn.style.display = "none";
                }

                // If a reading for this month already exists, pre-fill current reading
                if (d.current_reading !== null) {
                    document.getElementById("sp-curr-reading").value = d.current_reading;
                } else {
                    document.getElementById("sp-curr-reading").value = "";
                }

                liveRecalcMeterBill();
            }
        })
        .catch(err => {
            console.error("Failed to load room electricity info:", err);
        });
    }

    function onSplitterMonthChanged() {
        var roomId = document.getElementById("splitter-room-id").value;
        if (roomId) {
            onSplitterRoomChanged();
        }
    }

    function viewBaselinePhoto() {
        var photoBtn = document.getElementById("btn-view-baseline-photo");
        var src = photoBtn.getAttribute("data-src");
        if (src) {
            window.open(src, "_blank");
        }
    }

    function liveRecalcMeterBill() {
        if (isDirectBillMode) return;

        var prev = parseFloat(document.getElementById("sp-prev-reading").value) || 0;
        var currVal = document.getElementById("sp-curr-reading").value;
        var curr = currVal !== "" ? parseFloat(currVal) : prev;
        var rate = parseFloat(document.getElementById("sp-unit-rate").value) || 10.0;

        var units = Math.max(0, Math.round((curr - prev) * 100) / 100);
        var total = Math.round(units * rate * 100) / 100;

        document.getElementById("sp-live-units").innerText = units.toFixed(2);
        document.getElementById("sp-live-formula").innerText = "(" + curr.toFixed(2) + " - " + prev.toFixed(2) + ") × ₹" + rate.toFixed(2) + "/u";
        document.getElementById("sp-live-total-bill").innerText = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Sync with total-bill input as fallback
        document.getElementById("splitter-total-bill").value = total;
    }

    function calculateRoomSplitter() {
        var roomId = document.getElementById("splitter-room-id").value;
        var month = document.getElementById("splitter-billing-month").value;
        var loading = document.getElementById("splitter-loading");
        var errorBanner = document.getElementById("splitter-error-banner");
        var errorMsg = document.getElementById("splitter-error-message");
        var resultsPanel = document.getElementById("splitter-results-panel");

        if (!roomId) {
            toastr.warning("Please choose a room to calculate electricity split.");
            return;
        }
        if (!month) {
            toastr.warning("Please select a billing month.");
            return;
        }

        var prev = parseFloat(document.getElementById("sp-prev-reading").value) || 0;
        var curr = document.getElementById("sp-curr-reading").value;
        var rate = parseFloat(document.getElementById("sp-unit-rate").value) || 10.0;
        var directBill = document.getElementById("splitter-total-bill").value;

        var payload = {
            room_id: roomId,
            billing_month: month
        };

        if (isDirectBillMode || (!curr && directBill)) {
            if (!directBill || parseFloat(directBill) <= 0) {
                toastr.warning("Please enter a Total Room Bill amount greater than ₹0.");
                return;
            }
            payload.total_bill = parseFloat(directBill);
        } else {
            if (curr === "") {
                toastr.warning("Please enter the Current Meter Reading (kWh).");
                return;
            }
            var currNum = parseFloat(curr);
            if (currNum < prev) {
                toastr.warning("Current reading (" + currNum + ") cannot be less than previous reading (" + prev + ").");
                return;
            }
            payload.previous_reading = prev;
            payload.current_reading = currNum;
            payload.unit_rate = rate;
            payload.total_bill = Math.round(Math.max(0, currNum - prev) * rate * 100) / 100;
        }

        loading.classList.remove("hidden");
        errorBanner.classList.add("hidden");
        resultsPanel.classList.add("hidden");

        fetch("{{ route('sub_admin.electricity_split.calculate') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            loading.classList.add("hidden");
            if (data.status === "success" && data.data) {
                currentSplitData = data.data;
                renderSplitterResults(data.data);
            } else {
                errorMsg.innerText = data.message || "Failed to calculate split.";
                errorBanner.classList.remove("hidden");
                toastr.error(data.message || "Calculation failed.");
            }
        })
        .catch(function(err) {
            loading.classList.add("hidden");
            errorMsg.innerText = "Network error while connecting to server.";
            errorBanner.classList.remove("hidden");
            toastr.error("An error occurred during calculation.");
        });
    }

    function renderSplitterResults(d) {
        // High level metrics
        document.getElementById("sp-metric-room").innerText = "Room " + d.room.room_number + " (" + (d.room.sharing_type || 'Sharing') + ")";
        document.getElementById("sp-metric-month").innerText = d.billing_month + " (" + d.days_in_month + " Days)";
        document.getElementById("sp-metric-daily").innerText = "₹" + Number(d.daily_cost).toFixed(2) + " / day";
        document.getElementById("sp-metric-total").innerText = "₹" + Number(d.total_bill).toLocaleString();

        // Periods container
        var periodsContainer = document.getElementById("sp-periods-container");
        periodsContainer.innerHTML = "";

        if (d.periods && d.periods.length > 0) {
            d.periods.forEach(function(p) {
                var card = document.createElement("div");
                card.className = "bg-white dark:bg-slate-800/90 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs";
                card.innerHTML = 
                    '<div class="flex items-center justify-between mb-2">' +
                        '<span class="font-bold text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md">' +
                            p.label +
                        '</span>' +
                        '<span class="text-[11px] font-semibold text-slate-500">' + p.count + ' Occupant' + (p.count > 1 ? 's' : '') + '</span>' +
                    '</div>' +
                    '<div class="text-[11px] text-slate-600 dark:text-slate-300 space-y-1 mb-2.5">' +
                        '<div class="flex justify-between"><span>Period Cost:</span> <strong>₹' + Number(p.period_cost).toLocaleString() + '</strong></div>' +
                        '<div class="flex justify-between text-amber-600 dark:text-amber-400"><span>Share per person:</span> <strong class="text-sm">₹' + Number(p.cost_per_person).toLocaleString() + '</strong></div>' +
                    '</div>' +
                    '<div class="text-[10px] text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-700/50 truncate" title="' + p.occupant_names.join(', ') + '">' +
                        '<i class="fa-solid fa-users mr-1"></i> ' + (p.occupant_names.join(', ') || 'No active occupants') +
                    '</div>';
                periodsContainer.appendChild(card);
            });
        }

        // Residents table
        var tbody = document.getElementById("sp-residents-tbody");
        tbody.innerHTML = "";

        if (d.occupant_shares && d.occupant_shares.length > 0) {
            d.occupant_shares.forEach(function(s) {
                var row = document.createElement("tr");
                row.className = "hover:bg-slate-50/60 dark:hover:bg-slate-750 transition-colors";

                // Period pills
                var periodPills = "";
                if (s.period_shares && s.period_shares.length > 0) {
                    s.period_shares.forEach(function(ps) {
                        periodPills += '<span class="inline-block bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-[10px] font-medium px-2 py-0.5 rounded mr-1 mb-1">' +
                            'P' + ps.period_num + ' (' + ps.days + 'd): ₹' + ps.share +
                        '</span>';
                    });
                } else {
                    periodPills = '<span class="text-slate-400 italic">None</span>';
                }

                row.innerHTML = 
                    '<td class="py-3 px-4 font-bold text-slate-900 dark:text-white">' +
                        '<div>' + s.name + '</div>' +
                        '<div class="text-[11px] font-normal text-slate-400">' + s.phone + '</div>' +
                    '</td>' +
                    '<td class="py-3 px-3">' +
                        '<span class="bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 font-semibold px-2 py-0.5 rounded text-[11px]">' +
                            s.bed_code +
                        '</span>' +
                    '</td>' +
                    '<td class="py-3 px-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">' +
                        s.joining_date +
                    '</td>' +
                    '<td class="py-3 px-3 font-semibold text-slate-700 dark:text-slate-200">' +
                        s.days_active + ' / ' + d.days_in_month + ' days' +
                    '</td>' +
                    '<td class="py-3 px-4">' +
                        periodPills +
                    '</td>' +
                    '<td class="py-3 px-4 text-right">' +
                        '<div class="inline-flex items-center gap-1 justify-end">' +
                            '<span class="text-slate-400 font-bold">₹</span>' +
                            '<input type="number" step="1" value="' + s.calculated_share + '" ' +
                                   'data-student-id="' + s.student_id + '" ' +
                                   'data-days-active="' + s.days_active + '" ' +
                                   'oninput="recalcSplitterSum()" ' +
                                   'class="sp-student-share-input w-24 text-right px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">' +
                        '</div>' +
                    '</td>';

                tbody.appendChild(row);
            });
        }

        recalcSplitterSum();
        document.getElementById("splitter-results-panel").classList.remove("hidden");
    }

    function recalcSplitterSum() {
        var inputs = document.querySelectorAll(".sp-student-share-input");
        var total = 0;
        inputs.forEach(function(inp) {
            total += parseFloat(inp.value) || 0;
        });

        var targetTotal = currentSplitData ? currentSplitData.total_bill : 0;
        document.getElementById("sp-sum-shares").innerText = Math.round(total).toLocaleString();
        document.getElementById("sp-target-bill").innerText = Math.round(targetTotal).toLocaleString();

        var badge = document.getElementById("sp-balance-badge");
        if (Math.abs(total - targetTotal) < 1.0) {
            badge.className = "px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 flex items-center gap-1.5";
            badge.innerHTML = '<i class="fa-solid fa-check-circle"></i> Balanced: ₹' + Math.round(total).toLocaleString() + ' / ₹' + Math.round(targetTotal).toLocaleString();
        } else {
            badge.className = "px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 flex items-center gap-1.5";
            badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Discrepancy: ₹' + Math.round(total).toLocaleString() + ' (Target ₹' + Math.round(targetTotal).toLocaleString() + ')';
        }
    }

    function pushRoomSplitDues() {
        if (!currentSplitData) return;

        var inputs = document.querySelectorAll(".sp-student-share-input");
        var splits = [];
        var total = 0;

        inputs.forEach(function(inp) {
            var val = parseFloat(inp.value) || 0;
            var sid = parseInt(inp.getAttribute("data-student-id"));
            var days = parseInt(inp.getAttribute("data-days-active")) || 30;
            if (sid && val > 0) {
                splits.push({
                    student_id: sid,
                    amount: val,
                    days_active: days
                });
                total += val;
            }
        });

        if (splits.length === 0) {
            toastr.error("No valid resident share amounts to push.");
            return;
        }

        var prev = document.getElementById("sp-prev-reading").value;
        var curr = document.getElementById("sp-curr-reading").value;
        var rate = document.getElementById("sp-unit-rate").value || "10.00";
        var units = (curr && prev) ? Math.max(0, Math.round((parseFloat(curr) - parseFloat(prev)) * 100) / 100) : 0;

        // Build per-resident breakdown preview for popup
        var residentRowsHtml = "";
        var occupantsMap = {};
        if (currentSplitData && currentSplitData.occupant_shares) {
            currentSplitData.occupant_shares.forEach(function(occ) {
                occupantsMap[occ.student_id] = occ;
            });
        }

        splits.forEach(function(s) {
            var occ = occupantsMap[s.student_id];
            var name = occ ? occ.name : ("Resident #" + s.student_id);
            var bedCode = occ ? occ.bed_code : "Bed";
            residentRowsHtml += 
                '<div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs">' +
                    '<div class="flex items-center gap-2.5">' +
                        '<span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 font-bold text-[10px]">' + bedCode + '</span>' +
                        '<div>' +
                            '<div class="font-bold text-slate-900 dark:text-slate-100 text-xs">' + name + '</div>' +
                            '<div class="text-[10px] text-slate-400">' + s.days_active + ' active days in month</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">' +
                        '₹' + Number(s.amount).toLocaleString() +
                    '</div>' +
                '</div>';
        });

        var modalHtml = 
            '<div class="text-left font-sans">' +
                '<!-- Header -->' +
                '<div class="flex items-center gap-3 mb-4 p-3 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border border-amber-200/60 dark:border-amber-800/60">' +
                    '<div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-amber-500/20 shrink-0">' +
                        '<i class="fa-solid fa-bolt"></i>' +
                    '</div>' +
                    '<div>' +
                        '<h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Confirm & Push Light Bills</h4>' +
                        '<p class="text-xs text-slate-500 dark:text-slate-400">Post fair electricity dues directly to roommates\' accounts</p>' +
                    '</div>' +
                '</div>' +

                '<!-- Key Metrics -->' +
                '<div class="grid grid-cols-2 gap-2.5 mb-4 p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 text-xs">' +
                    '<div>' +
                        '<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Target Room</span>' +
                        '<p class="font-extrabold text-slate-800 dark:text-slate-200 text-sm mt-0.5">Room ' + currentSplitData.room.room_number + ' (' + splits.length + ' Roommates)</p>' +
                    '</div>' +
                    '<div>' +
                        '<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Billing Period</span>' +
                        '<p class="font-extrabold text-slate-800 dark:text-slate-200 text-sm mt-0.5">' + currentSplitData.billing_month + '</p>' +
                    '</div>' +
                    '<div>' +
                        '<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Sub-Meter Units</span>' +
                        '<p class="font-bold text-amber-600 dark:text-amber-400 mt-0.5">' + units + ' kWh @ ₹' + rate + '/u</p>' +
                    '</div>' +
                    '<div>' +
                        '<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Room Bill</span>' +
                        '<p class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm mt-0.5">₹' + Number(total).toLocaleString() + '</p>' +
                    '</div>' +
                '</div>' +

                '<!-- Residents Allocation Preview -->' +
                '<div class="mb-3.5">' +
                    '<span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1.5">Residents\' Pro-Rata Bill Shares</span>' +
                    '<div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">' +
                        residentRowsHtml +
                    '</div>' +
                '</div>' +

                '<!-- Notice -->' +
                '<div class="p-2.5 bg-blue-50/70 dark:bg-blue-950/30 rounded-xl border border-blue-200/60 dark:border-blue-900/40 text-[11px] text-blue-800 dark:text-blue-300 flex items-start gap-2">' +
                    '<i class="fa-solid fa-circle-info text-blue-500 mt-0.5 shrink-0"></i>' +
                    '<span>This will record the room\'s official month-end meter reading and create pending dues on each student\'s portal.</span>' +
                '</div>' +
            '</div>';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                html: modalHtml,
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-paper-plane mr-1.5"></i> Yes, Push Bills to Students',
                cancelButtonText: 'Review Details',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                width: '490px',
                customClass: {
                    popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl dark:bg-slate-800 p-5',
                    confirmButton: 'rounded-xl px-5 py-2.5 font-bold text-xs shadow-sm hover:shadow',
                    cancelButton: 'rounded-xl px-4 py-2.5 font-semibold text-xs'
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    executePushBills(splits, total, prev, curr, rate);
                }
            });
        } else {
            if (confirm("Push light bills to all " + splits.length + " residents in Room " + currentSplitData.room.room_number + " (Total: ₹" + total.toLocaleString() + ")?")) {
                executePushBills(splits, total, prev, curr, rate);
            }
        }
    }

    function executePushBills(splits, total, prev, curr, rate) {
        var btn = document.getElementById("btn-push-split-dues");
        var oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Pushing Bills...';

        var formData = new FormData();
        formData.append("room_id", currentSplitData.room.id);
        formData.append("billing_month", currentSplitData.billing_month_code);
        formData.append("total_bill", total);

        var photoFile = document.getElementById("sp-meter-photo").files[0];

        if (prev) formData.append("previous_reading", prev);
        if (curr) formData.append("current_reading", curr);
        if (rate) formData.append("unit_rate", rate);
        if (curr && prev) formData.append("units_consumed", Math.max(0, parseFloat(curr) - parseFloat(prev)));
        if (photoFile) formData.append("meter_photo", photoFile);

        splits.forEach(function(s, idx) {
            formData.append("splits[" + idx + "][student_id]", s.student_id);
            formData.append("splits[" + idx + "][amount]", s.amount);
            formData.append("splits[" + idx + "][days_active]", s.days_active);
        });

        fetch("{{ route('sub_admin.electricity_split.push_dues') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;

            if (data.status === "success") {
                toastr.success(data.message || "Light bills pushed successfully!");
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<div class="text-emerald-600 dark:text-emerald-400 font-extrabold text-lg flex items-center justify-center gap-2"><i class="fa-solid fa-circle-check"></i> Bills Pushed Successfully!</div>',
                        html: 
                            '<div class="text-xs text-slate-600 dark:text-slate-300 mt-2 space-y-2.5 font-sans">' +
                                '<p>Electricity dues have been posted to the accounts of <strong>' + splits.length + ' residents</strong> in Room <strong>' + currentSplitData.room.room_number + '</strong>.</p>' +
                                '<div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl font-bold text-emerald-800 dark:text-emerald-200 text-sm">' +
                                    'Total Amount: ₹' + Number(total).toLocaleString() + ' (' + currentSplitData.billing_month + ')' +
                                '</div>' +
                                '<p class="text-[11px] text-slate-400">Residents can now log into their student app to view and pay their fair share.</p>' +
                            '</div>',
                        icon: 'success',
                        confirmButtonText: 'OK, Done',
                        confirmButtonColor: '#059669',
                        customClass: {
                            popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl dark:bg-slate-800 p-5'
                        }
                    });
                }
            } else {
                toastr.error(data.message || "Failed to push bills.");
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
            toastr.error("An error occurred while pushing electricity bills.");
        });
    }
</script>
