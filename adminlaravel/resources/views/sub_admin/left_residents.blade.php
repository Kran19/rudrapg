@extends('layouts.admin')

@section('title', 'Left / Exited Residents - Sub Admin Desk')
@section('page_title', ($activeBranch ? $activeBranch->name . ' • ' : '') . 'Left / Exited Residents')

@section('content')
<div x-data="{
        vacateModalOpen: false,
        dossierModalOpen: false,
        settlementModalOpen: false,
        lightboxOpen: false,
        lightboxSrc: '',
        lightboxTitle: '',
        dossierData: {},
        settlementForm: {
            db_id: '',
            name: '',
            deposit_refund_status: 'PENDING',
            deposit_refund_amount: 0,
            deposit_refund_reference: '',
            deposit_refund_date: '',
            exit_remarks: ''
        },
        vacateForm: {
            student_id: '',
            exit_date: '{{ date('Y-m-d') }}',
            exit_reason: 'Course Completed',
            deposit_refund_status: 'PENDING',
            deposit_refund_amount: 0,
            deposit_refund_reference: '',
            exit_remarks: ''
        }
    }"
    @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true"
    class="space-y-6">

    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300">
                    <i class="fa-solid fa-building mr-1"></i> {{ $activeBranch ? $activeBranch->name : 'Branch Desk' }}
                </span>
                <span class="text-xs text-slate-400">Branch Historical Archive</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-person-walking-dashed-line-arrow-right text-rose-500"></i>
                Left & Exited Residents Ledger
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Complete records of former residents who vacated this branch, including checkout dates, room & bed vacated, reasons, and security deposit settlements.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" @click="vacateModalOpen = true" 
                    class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 flex items-center gap-2 transition-all cursor-pointer">
                <i class="fa-solid fa-user-xmark"></i> Process Resident Exit / Vacate Bed
            </button>
            <button id="export-left-csv" 
                    class="px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl flex items-center gap-2 transition-all">
                <i class="fa-solid fa-file-csv text-emerald-500"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Exited</span>
                <span class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-person-walking-arrow-right"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $totalLeft }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Former branch residents</div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Deposits Refunded</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($totalRefunded) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Returned deposit balance</div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Refunds</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400">{{ $pendingRefunds }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Pending deposit return</div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Occupants</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-600 dark:text-blue-400">{{ count($activeStudents) }}</div>
            <div class="text-[11px] text-slate-500 mt-1 font-medium">Currently staying in rooms</div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 mb-4">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="left-search" 
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Search by Resident Name, Phone, Room, Bed, Reference ID, UTR...">
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select id="reason-filter" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none">
                    <option value="">All Exit Reasons</option>
                    <option value="Course Completed">Course Completed</option>
                    <option value="Job Relocation">Job Relocation</option>
                    <option value="Shifted to Flat / Home">Shifted to Flat / Home</option>
                    <option value="Personal Reasons">Personal Reasons</option>
                    <option value="Disciplinary Action / Eviction">Disciplinary Action / Eviction</option>
                    <option value="Financial Reasons">Financial Reasons</option>
                    <option value="Other">Other</option>
                </select>

                <select id="refund-filter" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-slate-100 focus:outline-none">
                    <option value="">All Refund Status</option>
                    <option value="REFUNDED">Refunded</option>
                    <option value="PENDING">Pending Refund</option>
                    <option value="ADJUSTED">Adjusted against dues</option>
                    <option value="FORFEITED">Forfeited</option>
                    <option value="PARTIAL">Partial Refund</option>
                </select>
            </div>
        </div>

        <!-- Tabulator Table Container -->
        <div id="left-residents-table"></div>
    </div>

    <!-- MODAL 1: Process Resident Exit / Vacate Bed -->
    <div x-show="vacateModalOpen" style="display: none;" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="vacateModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 relative overflow-hidden">
            
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-person-walking-arrow-right"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">Process Resident Exit & Vacate Bed</h4>
                        <p class="text-xs text-slate-500">Record check-out and automatically free the allocated bed back to available status.</p>
                    </div>
                </div>
                <button type="button" @click="vacateModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="vacate-resident-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Select Active Resident *</label>
                    <select name="student_id" x-model="vacateForm.student_id" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option value="">-- Choose Resident in {{ $activeBranch ? $activeBranch->name : 'Branch' }} --</option>
                        @foreach($activeStudents as $ast)
                            <option value="{{ $ast->id }}">
                                {{ $ast->full_name }} (Room {{ $ast->room?->room_number ?? 'N/A' }} • Bed {{ $ast->bed?->bed_code ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Exit / Vacate Date *</label>
                        <input type="date" name="exit_date" x-model="vacateForm.exit_date" required 
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Reason for Leaving *</label>
                        <select name="exit_reason" x-model="vacateForm.exit_reason" required 
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="Course Completed">Course Completed</option>
                            <option value="Job Relocation">Job Relocation</option>
                            <option value="Shifted to Flat / Home">Shifted to Flat / Home</option>
                            <option value="Personal Reasons">Personal Reasons</option>
                            <option value="Disciplinary Action / Eviction">Disciplinary Action / Eviction</option>
                            <option value="Financial Reasons">Financial Reasons</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit Settlement Status *</label>
                        <select name="deposit_refund_status" x-model="vacateForm.deposit_refund_status" required 
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="PENDING">Pending Refund</option>
                            <option value="REFUNDED">Refunded in Full</option>
                            <option value="ADJUSTED">Adjusted against Unpaid Rent/Light Bill</option>
                            <option value="PARTIAL">Partially Refunded</option>
                            <option value="FORFEITED">Forfeited (Notice Period Breach)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit Amount to Refund (₹)</label>
                        <input type="number" step="0.01" name="deposit_refund_amount" x-model="vacateForm.deposit_refund_amount" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100" 
                               placeholder="e.g. 8500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Refund Bank Reference / UTR (Optional)</label>
                    <input type="text" name="deposit_refund_reference" x-model="vacateForm.deposit_refund_reference" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100" 
                           placeholder="e.g. 12-digit UTR or Cheque No.">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Exit Remarks & Key Handover Notes</label>
                    <textarea name="exit_remarks" x-model="vacateForm.exit_remarks" rows="2" 
                              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100" 
                              placeholder="e.g. Keys handed over, room inspected, meter reading settled."></textarea>
                </div>

                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-xl text-xs text-rose-700 dark:text-rose-300 flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5 text-sm"></i>
                    <div>
                        <span class="font-bold">Bed Release Notice:</span> 
                        Confirming exit will instantly mark this bed as <strong>AVAILABLE</strong> in Bed Allocation Grid and deactivate active mobile access.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="vacateModalOpen = false" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                        <i class="fa-solid fa-check mr-1.5"></i> Confirm Exit & Vacate Bed
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Full Resident Exit Dossier Modal -->
    <div x-show="dossierModalOpen" style="display: none;" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="dossierModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 relative max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-start justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <img :src="dossierData.profile_photo" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-lg text-slate-900 dark:text-slate-100" x-text="dossierData.full_name"></h4>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">
                                EXITED
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">
                            <span class="font-mono text-blue-600 dark:text-blue-400 font-bold" x-text="dossierData.app_reference"></span> 
                            • <span x-text="dossierData.branch_name"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="dossierModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-5 pt-4">
                <!-- Stay & Spot Card -->
                <div class="bg-slate-50 dark:bg-slate-900/80 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Accommodated Bed & Stay Timeline</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-slate-500 block">Room Allocated</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100" x-text="'Room ' + dossierData.room_number"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Bed Code</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100" x-text="'Bed ' + dossierData.bed_code"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Joining Date</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100" x-text="dossierData.joining_date"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Vacated Date</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400" x-text="dossierData.exit_date"></span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Total Length of Stay:</span>
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 rounded-lg font-bold" x-text="dossierData.stay_duration"></span>
                    </div>
                </div>

                <!-- Financial Settlement Details -->
                <div class="bg-slate-50 dark:bg-slate-900/80 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Security Deposit Settlement</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-slate-500 block">Refund Status</span>
                            <span class="font-bold" :class="dossierData.deposit_refund_status === 'REFUNDED' ? 'text-emerald-600' : (dossierData.deposit_refund_status === 'PENDING' ? 'text-amber-600' : 'text-blue-600')" x-text="dossierData.deposit_refund_status"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Refund Amount</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100" x-text="dossierData.formatted_refund_amount"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Refund Reference / UTR</span>
                            <span class="font-mono text-slate-900 dark:text-slate-100" x-text="dossierData.deposit_refund_reference || 'N/A'"></span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 text-xs">
                        <span class="text-slate-500 block mb-1">Reason for Leaving:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="dossierData.exit_reason"></span>
                    </div>
                    <div class="mt-2 text-xs">
                        <span class="text-slate-500 block mb-1">Exit Remarks & Notes:</span>
                        <p class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium italic" x-text="dossierData.exit_remarks || 'No remarks recorded.'"></p>
                    </div>
                </div>

                <!-- Personal & Emergency Contacts -->
                <div class="bg-slate-50 dark:bg-slate-900/80 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Personal & Emergency Record</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-500 block">Resident Phone</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="dossierData.phone"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Email Address</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="dossierData.email"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Parent / Guardian Name</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="dossierData.parent_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Parent / Emergency Phone</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="dossierData.parent_phone || dossierData.emergency_contact"></span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-500 block">Permanent Address</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100" x-text="dossierData.current_address"></span>
                        </div>
                    </div>
                </div>

                <!-- KYC Documents Thumbnail Previews -->
                <div class="bg-slate-50 dark:bg-slate-900/80 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Archived Identity Documents</span>
                    <div class="grid grid-cols-3 gap-3">
                        <template x-if="dossierData.aadhaar_front">
                            <div @click="$dispatch('open-lightbox', { src: dossierData.aadhaar_front, title: 'Aadhaar Card Front - ' + dossierData.full_name })" 
                                 class="cursor-pointer group text-center">
                                <img :src="dossierData.aadhaar_front" class="w-full h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-700 group-hover:opacity-80 transition-opacity">
                                <span class="text-[10px] text-slate-500 mt-1 block">Aadhaar Front</span>
                            </div>
                        </template>
                        <template x-if="dossierData.aadhaar_back">
                            <div @click="$dispatch('open-lightbox', { src: dossierData.aadhaar_back, title: 'Aadhaar Card Back - ' + dossierData.full_name })" 
                                 class="cursor-pointer group text-center">
                                <img :src="dossierData.aadhaar_back" class="w-full h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-700 group-hover:opacity-80 transition-opacity">
                                <span class="text-[10px] text-slate-500 mt-1 block">Aadhaar Back</span>
                            </div>
                        </template>
                        <template x-if="dossierData.pan_card">
                            <div @click="$dispatch('open-lightbox', { src: dossierData.pan_card, title: 'PAN Card - ' + dossierData.full_name })" 
                                 class="cursor-pointer group text-center">
                                <img :src="dossierData.pan_card" class="w-full h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-700 group-hover:opacity-80 transition-opacity">
                                <span class="text-[10px] text-slate-500 mt-1 block">PAN Card</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-5 mt-4 border-t border-slate-200 dark:border-slate-700">
                <span class="text-xs text-slate-400">
                    Processed by: <strong class="text-slate-600 dark:text-slate-300" x-text="dossierData.vacated_by_name"></strong>
                </span>
                <button type="button" @click="dossierModalOpen = false" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl transition-all">
                    Close Dossier
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Update Settlement Details Modal -->
    <div x-show="settlementModalOpen" style="display: none;" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div @click.away="settlementModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-700 relative">
            
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">Update Deposit Settlement</h4>
                    <p class="text-xs text-slate-500">Resident: <strong class="text-blue-600" x-text="settlementForm.name"></strong></p>
                </div>
                <button type="button" @click="settlementModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="update-settlement-form" class="space-y-4">
                @csrf
                <input type="hidden" name="db_id" x-model="settlementForm.db_id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit Settlement Status *</label>
                    <select name="deposit_refund_status" x-model="settlementForm.deposit_refund_status" required 
                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option value="REFUNDED">Refunded in Full</option>
                        <option value="PENDING">Pending Refund</option>
                        <option value="ADJUSTED">Adjusted against Dues</option>
                        <option value="PARTIAL">Partially Refunded</option>
                        <option value="FORFEITED">Forfeited</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Refund Amount (₹) *</label>
                        <input type="number" step="0.01" name="deposit_refund_amount" x-model="settlementForm.deposit_refund_amount" required 
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Refund Date</label>
                        <input type="date" name="deposit_refund_date" x-model="settlementForm.deposit_refund_date" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Refund Reference / UTR Number</label>
                    <input type="text" name="deposit_refund_reference" x-model="settlementForm.deposit_refund_reference" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" 
                           placeholder="e.g. 12-digit UTR ref">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Remarks / Audit Note</label>
                    <textarea name="exit_remarks" x-model="settlementForm.exit_remarks" rows="2" 
                              class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="settlementModalOpen = false" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                        <i class="fa-solid fa-save mr-1.5"></i> Save Settlement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" style="display: none;" x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[60] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-3xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-plus text-blue-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <i class="fa-solid fa-xmark mr-1"></i> Close (Esc)
            </button>
        </div>
        <div class="relative max-w-3xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl">
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    var tableData = @json($leftResidents);

    var table = new Tabulator("#left-residents-table", {
        data: tableData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 15,
        placeholder: "No former or exited residents found in this branch.",
        columns: [
            {
                title: "Resident", 
                field: "full_name", 
                minWidth: 220,
                formatter: function(cell) {
                    var d = cell.getData();
                    return `
                        <div class="flex items-center gap-3 py-1">
                            <img src="${d.profile_photo}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                            <div class="overflow-hidden">
                                <div class="font-bold text-slate-900 dark:text-slate-100 text-xs truncate">${d.full_name}</div>
                                <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono text-blue-600 dark:text-blue-400 font-bold">${d.app_reference}</span>
                                    <span>•</span>
                                    <span>${d.phone}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                title: "Room / Bed", 
                field: "room_number", 
                minWidth: 120,
                formatter: function(cell) {
                    var d = cell.getData();
                    return `
                        <div class="text-xs">
                            <span class="font-bold text-slate-800 dark:text-slate-200">Room ${d.room_number}</span>
                            <span class="text-[11px] text-slate-500 block">Bed ${d.bed_code}</span>
                        </div>
                    `;
                }
            },
            {
                title: "Stay Period", 
                field: "exit_date", 
                minWidth: 150,
                formatter: function(cell) {
                    var d = cell.getData();
                    return `
                        <div class="text-xs">
                            <span class="font-bold text-slate-800 dark:text-slate-200">${d.exit_date}</span>
                            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                                <span>Joined: ${d.joining_date}</span>
                                <span class="px-1.5 py-0.2 bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded font-bold">${d.stay_duration}</span>
                            </div>
                        </div>
                    `;
                }
            },
            {
                title: "Exit Reason", 
                field: "exit_reason", 
                minWidth: 140,
                formatter: function(cell) {
                    return `<span class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">${cell.getValue()}</span>`;
                }
            },
            {
                title: "Deposit Settlement", 
                field: "deposit_refund_status", 
                minWidth: 160,
                formatter: function(cell) {
                    var d = cell.getData();
                    var status = d.deposit_refund_status;
                    var badgeClass = "bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300";
                    if (status === "REFUNDED") badgeClass = "bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300";
                    else if (status === "ADJUSTED") badgeClass = "bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300";
                    else if (status === "FORFEITED") badgeClass = "bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300";

                    return `
                        <div class="text-xs">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider ${badgeClass}">${status}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block mt-1">${d.formatted_refund_amount}</span>
                        </div>
                    `;
                }
            },
            {
                title: "Actions", 
                minWidth: 160,
                headerSort: false,
                formatter: function(cell) {
                    return `
                        <div class="flex items-center gap-1.5 py-1">
                            <button type="button" class="btn-dossier px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded-lg text-xs font-bold transition-all flex items-center gap-1" title="View Full Exit Dossier">
                                <i class="fa-solid fa-eye"></i> Dossier
                            </button>
                            <button type="button" class="btn-settle px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 rounded-lg text-xs font-bold transition-all flex items-center gap-1" title="Update Deposit Settlement">
                                <i class="fa-solid fa-hand-holding-dollar"></i> Settle
                            </button>
                            <button type="button" class="btn-reinstate px-2 py-1.5 text-slate-400 hover:text-amber-500 rounded-lg text-xs transition-all" title="Reinstate Active Status">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                    `;
                },
                cellClick: function(e, cell) {
                    var d = cell.getData();
                    var target = e.target.closest('button');
                    if (!target) return;

                    if (target.classList.contains('btn-dossier')) {
                        var alpine = Alpine.$data(document.querySelector('[x-data]'));
                        alpine.dossierData = d;
                        alpine.dossierModalOpen = true;
                    } else if (target.classList.contains('btn-settle')) {
                        var alpine = Alpine.$data(document.querySelector('[x-data]'));
                        alpine.settlementForm = {
                            db_id: d.db_id,
                            name: d.full_name,
                            deposit_refund_status: d.deposit_refund_status,
                            deposit_refund_amount: d.deposit_refund_amount,
                            deposit_refund_reference: d.deposit_refund_reference !== 'N/A' ? d.deposit_refund_reference : '',
                            deposit_refund_date: d.raw_exit_date || '',
                            exit_remarks: d.exit_remarks !== 'No remarks recorded' ? d.exit_remarks : ''
                        };
                        alpine.settlementModalOpen = true;
                    } else if (target.classList.contains('btn-reinstate')) {
                        reinstateResident(d.db_id, d.full_name);
                    }
                }
            }
        ]
    });

    // Real-time Search
    document.getElementById("left-search").addEventListener("input", function() {
        var query = this.value.toLowerCase().trim();
        if (!query) {
            table.clearFilter();
            return;
        }
        table.setFilter(function(data) {
            return (data.full_name && data.full_name.toLowerCase().includes(query)) ||
                   (data.phone && data.phone.toLowerCase().includes(query)) ||
                   (data.app_reference && data.app_reference.toLowerCase().includes(query)) ||
                   (data.room_number && data.room_number.toLowerCase().includes(query)) ||
                   (data.deposit_refund_reference && data.deposit_refund_reference.toLowerCase().includes(query));
        });
    });

    // Reason & Refund Filters
    document.getElementById("reason-filter").addEventListener("change", function() {
        if (this.value) table.setFilter("exit_reason", "=", this.value);
        else table.clearFilter();
    });

    document.getElementById("refund-filter").addEventListener("change", function() {
        if (this.value) table.setFilter("deposit_refund_status", "=", this.value);
        else table.clearFilter();
    });

    // Export CSV
    document.getElementById("export-left-csv").addEventListener("click", function() {
        table.download("csv", "left_residents_ledger.csv");
    });

    // Handle Vacate Form Submit
    document.getElementById("vacate-resident-form").addEventListener("submit", function(e) {
        e.preventDefault();
        var studentId = this.querySelector('[name="student_id"]').value;
        if (!studentId) {
            toastr.error("Please select a resident to vacate.");
            return;
        }

        var formData = new FormData(this);

        fetch(appUrl("sub-admin/students/" + studentId + "/vacate"), {
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
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to vacate resident.");
            }
        })
        .catch(err => {
            toastr.error("Server error while processing resident exit.");
        });
    });

    // Handle Settlement Form Submit
    document.getElementById("update-settlement-form").addEventListener("submit", function(e) {
        e.preventDefault();
        var studentId = this.querySelector('[name="db_id"]').value;
        var formData = new FormData(this);

        fetch(appUrl("sub-admin/left-residents/" + studentId + "/settlement"), {
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
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update settlement.");
            }
        })
        .catch(err => {
            toastr.error("Server error while updating settlement.");
        });
    });

    // Reinstate function
    function reinstateResident(id, name) {
        Swal.fire({
            title: 'Reinstate ' + name + '?',
            text: 'This will restore their status to APPROVED active resident and remove exit flags.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Yes, Reinstate Resident'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("sub-admin/left-residents/" + id + "/reinstate"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to reinstate resident.");
                    }
                })
                .catch(err => {
                    toastr.error("Error connecting to server.");
                });
            }
        });
    }

    // Escape closes lightbox
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            Alpine.$data(document.querySelector('[x-data]')).lightboxOpen = false;
        }
    });
</script>
@endsection
