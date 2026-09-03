@extends('layouts.admin')

@section('title', 'Student Verification Desk - Rudra Group PG')
@section('page_title', 'Student Verification Desk (Naroda Branch)')

@section('content')
<div x-data="{ verifyModalOpen: false, activeStudent: {}, selectedBedId: '', availableBeds: (window.availableBedsData || []), lightboxOpen: false, lightboxSrc: '', lightboxTitle: '', isEditingRoom: false }"
     @open-verify-modal.window="activeStudent = $event.detail; selectedBedId = ''; isEditingRoom = false; verifyModalOpen = true"
     @close-verify-modal.window="verifyModalOpen = false"
     @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Student Booking Approval Queue</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Verify student Aadhaar/PAN documents and offline payment proofs before bed allocation.</p>
        </div>
    </div>

    <!-- Tabulator Verification Table -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <input type="text" id="verify-search" 
                   class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                   placeholder="🔍 Search applicant name, room...">
            <button id="export-verifications-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i> Export Queue CSV
            </button>
        </div>
        <div id="verifications-table"></div>
    </div>

    <!-- Pure Tailwind Modal: View Document Verification -->
    <div x-show="verifyModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="verifyModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-blue-400"></i> Applicant Audit Desk: <span class="text-blue-300" x-text="activeStudent.student_name"></span>
                </h4>
                <button @click="verifyModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Sequential Step Progression Stepper Bar -->
            <div class="px-6 py-2.5 bg-slate-100 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-semibold">
                <div class="flex items-center gap-1.5" :class="activeStudent.is_kyc_approved ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400'">
                    <i class="fa-solid" :class="activeStudent.is_kyc_approved ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-blue-500'"></i>
                    <span>Step 1: KYC Audit</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded" :class="activeStudent.is_kyc_approved ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'" x-text="activeStudent.is_kyc_approved ? 'Approved' : 'Pending'"></span>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
                <div class="flex items-center gap-1.5" :class="activeStudent.is_bed_assigned ? 'text-emerald-600 dark:text-emerald-400' : (activeStudent.is_kyc_approved ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400')">
                    <i class="fa-solid" :class="activeStudent.is_bed_assigned ? 'fa-circle-check text-emerald-500' : (activeStudent.is_kyc_approved ? 'fa-circle-dot text-amber-500' : 'fa-lock text-slate-400')"></i>
                    <span>Step 2: Room & Bed</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded" :class="activeStudent.is_bed_assigned ? 'bg-emerald-100 text-emerald-700' : (activeStudent.is_kyc_approved ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-500')" x-text="activeStudent.is_bed_assigned ? ('Room ' + activeStudent.room_number) : (activeStudent.is_kyc_approved ? 'Ready to Assign' : 'Locked')"></span>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-300 text-[10px]"></i>
                <div class="flex items-center gap-1.5" :class="activeStudent.is_payment_done ? 'text-emerald-600 dark:text-emerald-400' : (activeStudent.is_bed_assigned ? 'text-purple-600 dark:text-purple-400' : 'text-slate-400')">
                    <i class="fa-solid" :class="activeStudent.is_payment_done ? 'fa-circle-check text-emerald-500' : (activeStudent.is_bed_assigned ? 'fa-circle-dot text-purple-500' : 'fa-lock text-slate-400')"></i>
                    <span>Step 3: Handover & Active</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded" :class="activeStudent.is_payment_done ? 'bg-emerald-100 text-emerald-700' : (activeStudent.is_bed_assigned ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-500')" x-text="activeStudent.is_payment_done ? 'Complete' : (activeStudent.is_bed_assigned ? 'Ready for Handover' : 'Locked')"></span>
                </div>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Left: Student Details & Bed Assignment -->
                <div class="md:col-span-5 space-y-4 border-r border-slate-100 dark:border-slate-700 pr-4">
                    <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card"></i> Personal & Contact Info
                    </h5>
                    
                    <div class="bg-slate-50 dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Ref Code:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-slate-100" x-text="activeStudent.id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Phone:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.phone"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Email:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.email"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Aadhaar No:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.aadhaar"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">PAN No:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.pan"></span>
                        </div>
                    </div>

                    <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-people-roof"></i> Parent & Address Info
                    </h5>
                    <div class="bg-slate-50 dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Parent Name:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.parent_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Parent Phone:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="activeStudent.parent_phone"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 font-medium block mb-0.5">Permanent Address:</span>
                            <span class="font-normal text-slate-700 dark:text-slate-300 block leading-tight" x-text="activeStudent.address"></span>
                        </div>
                    </div>

                    <!-- Step 2 Room & Bed Allocation Section -->
                    <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-bed"></i> Step 2: Assign Room & Bed
                    </h5>

                    <!-- If KYC is not approved, show lock -->
                    <template x-if="!activeStudent.is_kyc_approved">
                        <div class="p-3.5 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 text-center text-xs space-y-1.5">
                            <i class="fa-solid fa-lock text-slate-400 text-lg"></i>
                            <div class="font-bold text-slate-700 dark:text-slate-300">Room & Bed Allocation Locked</div>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">Audit and approve Student KYC documents (Step 1) to unlock bed allocation.</p>
                        </div>
                    </template>

                    <!-- If KYC is approved, show allocation controls -->
                    <template x-if="activeStudent.is_kyc_approved">
                        <div class="bg-blue-50/60 dark:bg-blue-900/30 p-3.5 rounded-xl border border-blue-100 dark:border-blue-900 space-y-2 text-xs">
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300">Select Available Bed:</label>
                                <button type="button" @click="isEditingRoom = !isEditingRoom" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold hover:bg-blue-200 transition-colors">
                                    <i class="fa-solid fa-pen mr-1"></i> <span x-text="activeStudent.is_bed_assigned ? 'Change Bed' : 'Choose Bed'"></span>
                                </button>
                            </div>
                            <div x-show="isEditingRoom || !activeStudent.is_bed_assigned">
                                <select x-model="selectedBedId" class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-slate-100">
                                    <option value="">Select Bed from Available List</option>
                                    <template x-for="bed in availableBeds" :key="bed.id">
                                        <option :value="bed.id" x-text="bed.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-600 dark:text-slate-400">Allocated Spot:</span>
                                <span class="font-bold text-slate-900 dark:text-slate-100" x-text="(!activeStudent.room_number || activeStudent.room_number === 'Pending' || activeStudent.room_number === 'Unassigned') ? 'Room Not Assigned Yet' : 'Room ' + activeStudent.room_number + ' (' + activeStudent.bed_code + ')'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Monthly Rent:</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400" x-text="activeStudent.rent"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Security Deposit:</span>
                                <span class="font-bold text-slate-900 dark:text-slate-100" x-text="activeStudent.deposit"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Center/Right: Images Preview & Lightbox Triggers -->
                <div class="md:col-span-7 space-y-4">
                    <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-images"></i> Step 1: KYC Document Attachments</span>
                        <span class="text-[10px] text-slate-400 font-normal"><i class="fa-solid fa-magnifying-glass-plus mr-0.5"></i> Click image to zoom</span>
                    </h5>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Passport Profile Photo</label>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.profile_photo && $dispatch('open-lightbox', { src: activeStudent.profile_photo, title: 'Profile Photo - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.profile_photo">
                                    <div class="relative">
                                        <img :src="activeStudent.profile_photo" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.profile_photo">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-user-gear text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Aadhaar Front Image</label>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.aadhaar_front && $dispatch('open-lightbox', { src: activeStudent.aadhaar_front, title: 'Aadhaar Front - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.aadhaar_front">
                                    <div class="relative">
                                        <img :src="activeStudent.aadhaar_front" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.aadhaar_front">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-id-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Aadhaar Back Image</label>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.aadhaar_back && $dispatch('open-lightbox', { src: activeStudent.aadhaar_back, title: 'Aadhaar Back - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.aadhaar_back">
                                    <div class="relative">
                                        <img :src="activeStudent.aadhaar_back" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.aadhaar_back">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-id-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">PAN Card Attachment</label>
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 text-center relative group cursor-pointer overflow-hidden"
                                 @click="activeStudent.pan_card && $dispatch('open-lightbox', { src: activeStudent.pan_card, title: 'PAN Card - ' + activeStudent.student_name })">
                                <template x-if="activeStudent.pan_card">
                                    <div class="relative">
                                        <img :src="activeStudent.pan_card" class="w-full h-28 object-cover rounded-lg group-hover:scale-105 transition-all">
                                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold rounded-lg">
                                            <i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Zoom
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.pan_card">
                                    <div class="h-28 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-credit-card text-2xl mb-1"></i>
                                        <span>Not Uploaded</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 Payment Proof Section -->
                    <div class="pt-2">
                        <label class="block text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">
                            <i class="fa-solid fa-receipt mr-1"></i> Step 3: Payment Verification (Rent & Security Deposit)
                        </label>

                        <template x-if="!activeStudent.is_bed_assigned">
                            <div class="p-4 bg-slate-100 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 text-center text-xs space-y-1">
                                <i class="fa-solid fa-lock text-slate-400 text-lg"></i>
                                <div class="font-bold text-slate-700 dark:text-slate-300">Payment Audit Locked</div>
                                <p class="text-slate-500 dark:text-slate-400 text-[11px]">Rent and Deposit will be calculated automatically once Step 2 (Bed Allocation) is completed.</p>
                            </div>
                        </template>

                        <template x-if="activeStudent.is_bed_assigned">
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-center relative group overflow-hidden">
                                <template x-if="activeStudent.payment_proof">
                                    <div class="cursor-pointer" @click="$dispatch('open-lightbox', { src: activeStudent.payment_proof, title: 'Payment Receipt - ' + activeStudent.student_name })">
                                        <img :src="activeStudent.payment_proof" class="w-full h-36 object-contain rounded-lg group-hover:scale-105 transition-all">
                                        <div class="mt-1 text-[11px] text-blue-600 dark:text-blue-400 font-bold"><i class="fa-solid fa-magnifying-glass-plus mr-1"></i> Click to Zoom Payment Proof</div>
                                        <template x-if="activeStudent.payment_utr">
                                            <div class="mt-0.5 text-[11px] font-mono text-slate-700 dark:text-slate-300">UTR: <span class="font-bold" x-text="activeStudent.payment_utr"></span></div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!activeStudent.payment_proof">
                                    <div class="p-3 bg-amber-50/60 dark:bg-amber-900/30 rounded-lg border border-amber-100 dark:border-amber-900 text-center">
                                        <i class="fa-solid fa-clock text-amber-500 text-lg mb-1"></i>
                                        <div class="text-xs font-bold text-amber-800 dark:text-amber-200" x-text="activeStudent.payment_status === 'VERIFIED' ? 'Payment Verified & Confirmed' : (activeStudent.payment_utr ? 'Payment Submitted (UTR: ' + activeStudent.payment_utr + ')' : 'Payment Upload Pending from Resident App')"></div>
                                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5" x-text="activeStudent.payment_utr ? 'UTR Reference Attached' : 'Resident has been notified to submit rent & deposit payment proof in app.'"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer Actions with Sequential Step Triggers -->
            <div x-show="activeStudent" class="p-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <button type="button" @click="rejectBooking(activeStudent)" 
                        class="px-4 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 border border-rose-200 dark:border-rose-900 rounded-xl transition-colors">
                    <i class="fa-solid fa-xmark mr-1"></i> Reject Application
                </button>
                
                <div class="flex items-center gap-2">
                    <!-- Step 1 Trigger -->
                    <button type="button" @click="approveKycOnly(activeStudent)" 
                            :disabled="activeStudent.is_kyc_approved"
                            :class="activeStudent.is_kyc_approved ? 'bg-emerald-50 text-emerald-700 border-emerald-200 cursor-default dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-100 border-blue-200 cursor-pointer shadow-xs'"
                            class="px-4 py-2 text-xs font-bold border rounded-xl transition-all">
                        <i class="fa-solid" :class="activeStudent.is_kyc_approved ? 'fa-check text-emerald-600' : 'fa-user-check text-blue-600'"></i>
                        <span x-text="activeStudent.is_kyc_approved ? 'Step 1: KYC Approved ✓' : 'Step 1: Approve Profile KYC'"></span>
                    </button>

                    <!-- Step 2 Trigger -->
                    <button type="button" @click="assignBedOnly(activeStudent, selectedBedId)" 
                            :disabled="!activeStudent.is_kyc_approved || (!selectedBedId && !activeStudent.is_bed_assigned)"
                            :class="(!activeStudent.is_kyc_approved) ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed border border-slate-300 dark:border-slate-700' : (activeStudent.is_bed_assigned ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 cursor-pointer' : (selectedBedId ? 'bg-amber-500 hover:bg-amber-600 text-white cursor-pointer shadow-sm' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed'))"
                            class="px-4 py-2 text-xs font-bold rounded-xl transition-all">
                        <i class="fa-solid" :class="(!activeStudent.is_kyc_approved) ? 'fa-lock' : (activeStudent.is_bed_assigned ? 'fa-bed' : 'fa-bed')"></i>
                        <span x-text="(!activeStudent.is_kyc_approved) ? 'Step 2: Locked (KYC First)' : (activeStudent.is_bed_assigned ? ('Step 2: Assigned (Room ' + activeStudent.room_number + ')') : 'Step 2: Assign Room & Bed')"></span>
                    </button>

                    <!-- Step 3 Trigger -->
                    <button type="button" @click="approveBooking(activeStudent, selectedBedId)" 
                            :disabled="!activeStudent.is_bed_assigned"
                            :class="(!activeStudent.is_bed_assigned) ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed border border-slate-300 dark:border-slate-700' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md cursor-pointer'"
                            class="px-5 py-2 text-xs font-bold rounded-xl transition-all">
                        <i class="fa-solid" :class="(!activeStudent.is_bed_assigned) ? 'fa-lock' : 'fa-key'"></i>
                        <span x-text="(!activeStudent.is_bed_assigned) ? 'Step 3: Locked (Assign Bed First)' : 'Step 3: Approve & Key Handover'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Image Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" 
         x-transition
         class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[60] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-4xl flex justify-between items-center text-white mb-3">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass-plus text-blue-400"></i> <span x-text="lightboxTitle"></span>
            </h3>
            <button @click="lightboxOpen = false" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <i class="fa-solid fa-xmark mr-1"></i> Close Lightbox (Esc)
            </button>
        </div>
        <div class="relative max-w-4xl max-h-[85vh] flex items-center justify-center overflow-auto rounded-2xl border border-slate-800 bg-slate-900/50 p-2">
            <img :src="lightboxSrc" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-transform hover:scale-125 cursor-zoom-in">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.availableBedsData = @json($availableBeds);
    var queueData = @json($queue);

    var table = new Tabulator("#verifications-table", {
        data: queueData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Booking Verification Requests Pending",
        columns: [
            {title: "Booking ID", field: "id", minWidth: 150, formatter: function(cell){
                return "<code class='bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs font-mono border border-slate-200 dark:border-slate-700'>" + cell.getValue() + "</code>";
            }},
            {title: "Student Name", field: "student_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone", field: "phone", minWidth: 130},
            {title: "Requested Bed", field: "bed_code", minWidth: 160, formatter: function(cell){
                var room = cell.getRow().getData().room_number;
                var bed = cell.getValue();
                if (!room || room === "Pending" || room === "Unassigned") {
                    return "<span class='text-amber-600 font-semibold text-xs'><i class='fa-solid fa-bed mr-1'></i> Pending Assignment</span>";
                }
                return "Room " + room + " (" + bed + ")";
            }},
            {title: "Rent", field: "rent", minWidth: 140, formatter: function(cell){
                var val = cell.getValue();
                if (!val || val === "Pending Room Allocation" || val === "₹0") {
                    return "<span class='text-slate-400 italic text-xs'>Pending Allocation</span>";
                }
                return "<strong class='text-slate-900 dark:text-slate-100'>" + val + "</strong>";
            }},
            {title: "Deposit", field: "deposit", minWidth: 140, formatter: function(cell){
                var val = cell.getValue();
                if (!val || val === "Pending Room Allocation" || val === "₹0") {
                    return "<span class='text-slate-400 italic text-xs'>Pending Allocation</span>";
                }
                return "<strong class='text-slate-900 dark:text-slate-100'>" + val + "</strong>";
            }},
            {title: "Date", field: "date", minWidth: 120},
            {title: "Status", field: "status", minWidth: 180, formatter: function(cell){
                var val = cell.getValue();
                var row = cell.getRow().getData();
                if (row.is_payment_done || val === "Approved" || val === "APPROVED") {
                    return '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-circle-check"></i> Complete & Active</span>';
                } else if (row.is_payment_submitted) {
                    return '<span class="bg-purple-100 text-purple-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-receipt"></i> Step 3: Payment Auditing</span>';
                } else if (row.is_bed_assigned || val === "BED_ALLOCATED") {
                    return '<span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-bed"></i> Step 3: Bed Set • Pay Rent</span>';
                } else if (row.is_kyc_approved || val === "KYC_APPROVED") {
                    return '<span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-user-check"></i> Step 2: KYC Done • Pick Bed</span>';
                }
                return '<span class="bg-slate-100 text-slate-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fa-solid fa-clock"></i> Step 1: KYC Pending</span>';
            }},
            {title: "Actions", field: "id", minWidth: 130, formatter: function(cell){
                return '<button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg shadow-xs flex items-center gap-1 audit-btn"><i class="fa-solid fa-clipboard-check"></i> Audit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                window.dispatchEvent(new CustomEvent('open-verify-modal', { detail: data }));
            }},
        ]
    });

    document.getElementById("verify-search").addEventListener("keyup", function(){
        table.setFilter("student_name", "like", this.value);
    });

    document.getElementById("export-verifications-csv").addEventListener("click", function(){
        table.download("csv", "verification_queue.csv");
    });

    function approveKycOnly(student) {
        Swal.fire({
            title: 'Approve Student KYC Profile?',
            text: "This will verify the student's personal info & documents. Room can be assigned next.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Yes, Approve KYC Profile'
        }).then((result) => {
            if (result.isConfirmed) {
                var targetId = student.db_id || student.id;
                fetch(appUrl("sub-admin/verifications/" + targetId + "/approve-kyc"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        window.dispatchEvent(new CustomEvent('close-verify-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to approve KYC profile.");
                    }
                })
                .catch(err => {
                    toastr.error("Failed to approve KYC profile.");
                });
            }
        });
    }

    function assignBedOnly(student, selectedBedId) {
        if (!selectedBedId) {
            toastr.warning("Please select a Room & Bed from the dropdown.");
            return;
        }
        Swal.fire({
            title: 'Assign Selected Room & Bed?',
            text: "This will allocate the selected bed and send payment notice to the resident app.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#D97706',
            confirmButtonText: 'Yes, Assign Bed'
        }).then((result) => {
            if (result.isConfirmed) {
                var targetId = student.db_id || student.id;
                fetch(appUrl("sub-admin/verifications/" + targetId + "/assign-bed"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ 
                        bed_id: selectedBedId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        window.dispatchEvent(new CustomEvent('close-verify-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to assign bed.");
                    }
                })
                .catch(err => {
                    toastr.error("Failed to assign bed.");
                });
            }
        });
    }

    function approveBooking(student, selectedBedId) {
        Swal.fire({
            title: 'Approve Payment & Hand Over Key?',
            text: "This will confirm full resident onboarding and key handover.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16A34A',
            confirmButtonText: 'Yes, Complete Key Handover'
        }).then((result) => {
            if (result.isConfirmed) {
                var targetId = student.db_id || student.id;
                fetch(appUrl("sub-admin/verifications/" + targetId + "/approve"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ 
                        bed_id: selectedBedId || null
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success(data.message);
                        window.dispatchEvent(new CustomEvent('close-verify-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to approve verification.");
                    }
                })
                .catch(err => {
                    toastr.error("Failed to approve verification.");
                });
            }
        });
    }

    function rejectBooking(student) {
        Swal.fire({
            title: 'Reject Application?',
            text: "Please confirm application rejection.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Reject'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("sub-admin/verifications/" + student.id + "/reject"), {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        toastr.success("Application rejected.");
                        window.dispatchEvent(new CustomEvent('close-verify-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to reject application.");
                    }
                })
                .catch(err => {
                    toastr.error("Failed to reject application.");
                });
            }
        });
    }
</script>
@endsection
