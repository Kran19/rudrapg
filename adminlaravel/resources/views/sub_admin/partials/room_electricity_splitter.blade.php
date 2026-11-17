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
                        Fair period-wise light bill distribution among room occupants based on joining date and duration of stay (e.g., 15-day pro-rata calculation).
                    </p>
                </div>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
            Pro-Rata Segmentation Active
        </span>
    </div>

    <!-- Filter & Configuration Form -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Room Selector -->
        <div>
            <label for="splitter-room-id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                1. Select Room <span class="text-rose-500">*</span>
            </label>
            <select id="splitter-room-id" 
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
            <input type="month" id="splitter-billing-month" value="{{ date('Y-m') }}" 
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
        </div>

        <!-- Total Room Electricity Bill -->
        <div>
            <label for="splitter-total-bill" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                3. Total Room Bill (₹)
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">₹</span>
                <input type="number" id="splitter-total-bill" placeholder="e.g. 3000" min="0" step="1"
                       class="w-full pl-8 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Leave empty to auto-pull from meter reading</p>
        </div>

        <!-- Calculate Trigger Button -->
        <div class="flex items-end">
            <button type="button" id="btn-calculate-splitter" onclick="calculateRoomSplitter()"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm py-2.5 px-4 rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-calculator"></i>
                <span>Calculate Fair Split</span>
            </button>
        </div>
    </div>

    <!-- Loading State Indicator -->
    <div id="splitter-loading" class="hidden py-8 text-center">
        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-xs font-semibold">
            <i class="fa-solid fa-circle-notch fa-spin text-amber-600 text-sm"></i>
            <span>Analyzing occupant move-in dates & segmenting periods...</span>
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
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider">Room Total Bill</span>
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

    function calculateRoomSplitter() {
        var roomId = document.getElementById("splitter-room-id").value;
        var month = document.getElementById("splitter-billing-month").value;
        var totalBill = document.getElementById("splitter-total-bill").value;
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
            body: JSON.stringify({
                room_id: roomId,
                billing_month: month,
                total_bill: totalBill ? parseFloat(totalBill) : null
            })
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

        // If total_bill field was empty, fill it with auto-pulled value
        var totalInput = document.getElementById("splitter-total-bill");
        if (!totalInput.value) {
            totalInput.value = d.total_bill;
        }

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
            d.occupant_shares.forEach(function(s, idx) {
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

        if (!confirm("Are you sure you want to push these light bills to all " + splits.length + " residents in Room " + currentSplitData.room.room_number + "?\n\nTotal Sum: ₹" + total.toLocaleString() + "\nThis will create official pending dues on each student's portal.")) {
            return;
        }

        var btn = document.getElementById("btn-push-split-dues");
        var oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Pushing Bills...';

        fetch("{{ route('sub_admin.electricity_split.push_dues') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                room_id: currentSplitData.room.id,
                billing_month: currentSplitData.billing_month_code,
                total_bill: total,
                splits: splits
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;

            if (data.status === "success") {
                toastr.success(data.message || "Light bills pushed successfully!");
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Bills Pushed Successfully!",
                        text: "Electricity dues have been posted to the accounts of " + splits.length + " residents in Room " + currentSplitData.room.room_number + ". They can now pay from their student app.",
                        icon: "success",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#059669"
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
