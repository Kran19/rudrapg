@extends('layouts.admin')

@section('title', 'Student Directory - Rudra Group PG')
@section('page_title', 'Master Student Directory (All Branches)')

@section('content')
<div x-data="{ 
         lightboxOpen: false, 
         lightboxSrc: '', 
         lightboxTitle: '', 
         kycModalOpen: false,
         kycModalData: { name: '', profile_photo: '', aadhaar_front: '', aadhaar_back: '', pan_card: '' },
         editStudentModalOpen: false, 
         editForm: { 
             db_id: '', 
             full_name: '', 
             phone: '', 
             email: '', 
             branch_id: '', 
             raw_joining_date: '', 
             aadhaar_number: '', 
             pan_number: '', 
             parent_name: '', 
             parent_phone: '', 
             emergency_contact: '', 
             current_address: '', 
             initial_meter_reading: 0, 
             raw_kyc_status: 'PENDING', 
             raw_rent_status: 'PENDING', 
             rent_balance_due: 0, 
             deposit_balance_due: 0 
         } 
     }"
     @open-lightbox.window="lightboxSrc = $event.detail.src; lightboxTitle = $event.detail.title; lightboxOpen = true"
     @open-kyc-modal.window="kycModalData = { ...$event.detail }; kycModalOpen = true"
     @close-kyc-modal.window="kycModalOpen = false"
     @open-edit-student-modal.window="editForm = { ...$event.detail }; editStudentModalOpen = true"
     @close-edit-student-modal.window="editStudentModalOpen = false">

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Global Resident Student Directory</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">Search and filter active resident students across all Rudra Group PG branches.</p>
    </div>
    <button id="export-students-csv" class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i> Export Directory CSV
    </button>
</div>

<!-- Tabulator Student Directory Table -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
        <input type="text" id="student-search" 
               class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm w-full md:w-72 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
               placeholder="🔍 Search by Name, Phone, Aadhaar...">
        
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="branch-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All PG Branches</option>
                @foreach($allBranches as $b)
                    <option value="{{ $b->name }}" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">{{ $b->name }}</option>
                @endforeach
            </select>
            <select id="kyc-filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium focus:outline-none text-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">All KYC Status</option>
                <option value="VERIFIED" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Verified</option>
                <option value="PENDING" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Pending</option>
            </select>
        </div>
    </div>
    <div id="students-table"></div>
</div>

<!-- Fullscreen Image Lightbox Zoom Modal -->
<div x-show="lightboxOpen" style="display: none;"
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

<!-- Pure Tailwind Modal: Edit Student Record -->
<div x-show="editStudentModalOpen" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     style="display: none;">
    
    <div @click.away="editStudentModalOpen = false" 
         class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
        <div class="bg-slate-900 text-white p-5 flex items-center justify-between shrink-0">
            <h4 class="font-bold text-base flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-amber-400"></i> Edit Resident Details: <span class="text-blue-400" x-text="editForm.full_name"></span>
            </h4>
            <button @click="editStudentModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <form id="edit-student-form" class="p-6 space-y-5 overflow-y-auto flex-1">
            @csrf
            <input type="hidden" name="db_id" x-model="editForm.db_id">

            <!-- Section 1: Basic & Admission -->
            <div>
                <h5 class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-address-card"></i> 1. Basic & Admission Details
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name *</label>
                        <input type="text" name="full_name" required x-model="editForm.full_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Phone Number *</label>
                        <input type="text" name="phone" required x-model="editForm.phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editForm.email" placeholder="student@example.com" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Assigned PG Branch *</label>
                        <select name="branch_id" required x-model="editForm.branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 flex items-center justify-between">
                            <span>Joining Date *</span>
                            <span class="text-[10px] text-slate-400 font-normal">Date when resident moved in</span>
                        </label>
                        <input type="date" name="joining_date" x-model="editForm.raw_joining_date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
            </div>

            <!-- Section 2: Identity & Legal Verification -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                <h5 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-id-card"></i> 2. Identity & Legal Verification
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Aadhaar Card No.</label>
                        <input type="text" name="aadhaar_number" x-model="editForm.aadhaar_number" placeholder="12-digit Aadhaar" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">PAN Card No.</label>
                        <input type="text" name="pan_number" x-model="editForm.pan_number" placeholder="10-digit PAN" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 uppercase font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">KYC Status</label>
                        <select name="kyc_status" required x-model="editForm.raw_kyc_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="PENDING">PENDING</option>
                            <option value="APPROVED">APPROVED (VERIFIED)</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Parent & Emergency Contacts -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                <h5 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-family"></i> 3. Parent / Guardian & Emergency Contact
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Parent / Guardian Name</label>
                        <input type="text" name="parent_name" x-model="editForm.parent_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Parent Phone</label>
                        <input type="text" name="parent_phone" x-model="editForm.parent_phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Emergency Contact</label>
                        <input type="text" name="emergency_contact" x-model="editForm.emergency_contact" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Permanent Home Address</label>
                    <textarea name="current_address" rows="2" x-model="editForm.current_address" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100"></textarea>
                </div>
            </div>

            <!-- Section 4: Meter Baseline & Dues -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                <h5 class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-receipt"></i> 4. Utility Baseline & Rent Balance
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Starting Meter Reading</label>
                        <input type="number" step="0.01" name="initial_meter_reading" x-model="editForm.initial_meter_reading" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Status</label>
                        <select name="rent_status" required x-model="editForm.raw_rent_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="PENDING">PENDING</option>
                            <option value="PARTIALLY_PAID">PARTIALLY PAID</option>
                            <option value="PAID">PAID</option>
                            <option value="OVERDUE">OVERDUE</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Left / Due (₹)</label>
                        <input type="number" step="0.01" name="rent_balance_due" x-model="editForm.rent_balance_due" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit Balance (₹)</label>
                        <input type="number" step="0.01" name="deposit_balance_due" x-model="editForm.deposit_balance_due" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" @click="deleteStudent(editForm.db_id)" class="px-4 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-trash"></i> Delete Student
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" @click="editStudentModalOpen = false" class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-600/30 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js Modal: KYC Documents Viewer Grid -->
<div x-show="kycModalOpen" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     style="display: none;">
    
    <div @click.away="kycModalOpen = false" 
         class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-3xl overflow-hidden transform transition-all">
        
        <div class="bg-slate-900 text-white p-5 flex items-center justify-between border-b border-slate-800">
            <h4 class="font-bold text-base flex items-center gap-2">
                <i class="fa-solid fa-id-card text-blue-400"></i> KYC Documents Viewer: <span x-text="kycModalData.name"></span>
            </h4>
            <button @click="kycModalOpen = false" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <!-- 2x2 Grid of Documents -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Profile Photo -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 bg-slate-50 dark:bg-slate-900 flex flex-col justify-between h-56 shadow-xs">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-250">Passport Profile Photo</span>
                        <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded-full">Required</span>
                    </div>
                    <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 relative bg-white dark:bg-slate-950 flex items-center justify-center h-36">
                        <template x-if="kycModalData.profile_photo">
                            <img :src="kycModalData.profile_photo" class="w-full h-full object-contain cursor-zoom-in hover:scale-105 transition-transform" 
                                 @click="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: kycModalData.profile_photo, title: 'Profile Photo - ' + kycModalData.name } }))">
                        </template>
                        <template x-if="!kycModalData.profile_photo">
                            <div class="text-slate-400 text-xs italic">Not Uploaded</div>
                        </template>
                    </div>
                </div>

                <!-- Aadhaar Front -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 bg-slate-50 dark:bg-slate-900 flex flex-col justify-between h-56 shadow-xs">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-250">Aadhaar Front Image</span>
                        <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded-full">Required</span>
                    </div>
                    <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 relative bg-white dark:bg-slate-950 flex items-center justify-center h-36">
                        <template x-if="kycModalData.aadhaar_front">
                            <img :src="kycModalData.aadhaar_front" class="w-full h-full object-contain cursor-zoom-in hover:scale-105 transition-transform" 
                                 @click="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: kycModalData.aadhaar_front, title: 'Aadhaar Front - ' + kycModalData.name } }))">
                        </template>
                        <template x-if="!kycModalData.aadhaar_front">
                            <div class="text-slate-400 text-xs italic">Not Uploaded</div>
                        </template>
                    </div>
                </div>

                <!-- Aadhaar Back -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 bg-slate-50 dark:bg-slate-900 flex flex-col justify-between h-56 shadow-xs">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-250">Aadhaar Back Image</span>
                        <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded-full">Required</span>
                    </div>
                    <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 relative bg-white dark:bg-slate-950 flex items-center justify-center h-36">
                        <template x-if="kycModalData.aadhaar_back">
                            <img :src="kycModalData.aadhaar_back" class="w-full h-full object-contain cursor-zoom-in hover:scale-105 transition-transform" 
                                 @click="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: kycModalData.aadhaar_back, title: 'Aadhaar Back - ' + kycModalData.name } }))">
                        </template>
                        <template x-if="!kycModalData.aadhaar_back">
                            <div class="text-slate-400 text-xs italic">Not Uploaded</div>
                        </template>
                    </div>
                </div>

                <!-- PAN Card -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 bg-slate-50 dark:bg-slate-900 flex flex-col justify-between h-56 shadow-xs">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-250">PAN Card Attachment</span>
                        <span class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded-full">Required</span>
                    </div>
                    <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 relative bg-white dark:bg-slate-950 flex items-center justify-center h-36">
                        <template x-if="kycModalData.pan_card">
                            <img :src="kycModalData.pan_card" class="w-full h-full object-contain cursor-zoom-in hover:scale-105 transition-transform" 
                                 @click="window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { src: kycModalData.pan_card, title: 'PAN Card - ' + kycModalData.name } }))">
                        </template>
                        <template x-if="!kycModalData.pan_card">
                            <div class="text-slate-400 text-xs italic">Not Uploaded</div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer Close -->
            <div class="flex justify-end pt-3 border-t border-slate-150 dark:border-slate-700">
                <button type="button" @click="kycModalOpen = false" class="px-5 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-850 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-xl transition-all shadow-md">Close Viewer</button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    var studentsData = @json($students);

    var table = new Tabulator("#students-table", {
        data: studentsData,
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Student Records Found",
        columns: [
            {title: "Student ID", field: "id", minWidth: 140, formatter: function(cell){
                return "<code class='bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs font-mono'>" + cell.getValue() + "</code>";
            }},
            {title: "Full Name", field: "full_name", minWidth: 160, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>" + cell.getValue() + "</strong>";
            }},
            {title: "Phone Number", field: "phone", minWidth: 130},
            {title: "PG Branch", field: "branch_name", minWidth: 145, formatter: function(cell){
                return '<span class="bg-slate-900 dark:bg-slate-850 text-white text-[11px] font-extrabold px-2.5 py-1 rounded-md">' + cell.getValue() + '</span>';
            }},
            {title: "Room & Bed", field: "room_bed", minWidth: 140},
            {title: "Joining Date", field: "joining_date", minWidth: 120},
            {title: "KYC Status", field: "kyc_status", minWidth: 120, formatter: function(cell){
                var val = cell.getValue();
                if (val === "VERIFIED" || val === "APPROVED") {
                    return '<span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 text-xs font-bold px-2.5 py-1 rounded-full">Verified</span>';
                }
                return '<span class="bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 text-xs font-bold px-2.5 py-1 rounded-full">' + (val ? val.replace('_', ' ') : 'Pending') + '</span>';
            }},
            {title: "Rent Status", field: "rent_status", minWidth: 120, formatter: function(cell){
                var val = (cell.getValue() || '').toUpperCase();
                if (val === "PAID") return '<span class="bg-emerald-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Paid</span>';
                if (val === "PARTIALLY_PAID" || val.includes("PARTIAL")) return '<span class="bg-amber-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Partially Paid</span>';
                if (val === "PENDING" || val === "UNDER_VERIFICATION") return '<span class="bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">Pending</span>';
                return '<span class="bg-rose-600 text-white text-xs font-bold px-2.5 py-1 rounded-full">Overdue</span>';
            }},
            {title: "Actions", field: "id", minWidth: 220, formatter: function(cell){
                return '<div class="flex gap-1.5">' +
                       '  <button class="bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs font-bold px-2.5 py-1.5 rounded-lg transition-colors view-kyc-btn"><i class="fa-solid fa-id-card font-bold"></i> KYC</button>' +
                       '  <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm edit-student-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                       '  <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm reset-pw-student-btn"><i class="fa-solid fa-key"></i> Pass</button>' +
                       '</div>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('view-kyc-btn') || e.target.closest('.view-kyc-btn')) {
                    window.dispatchEvent(new CustomEvent('open-kyc-modal', { detail: {
                        name: data.full_name,
                        profile_photo: data.profile_photo,
                        aadhaar_front: data.aadhaar_front,
                        aadhaar_back: data.aadhaar_back,
                        pan_card: data.pan_card
                    } }));
                } else if (e.target.classList.contains('edit-student-btn') || e.target.closest('.edit-student-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-student-modal', { detail: data }));
                } else if (e.target.classList.contains('reset-pw-student-btn') || e.target.closest('.reset-pw-student-btn')) {
                    const generated = 'Rudra@' + Math.floor(1000 + Math.random() * 9000);
                    Swal.fire({
                        title: 'Set Student Password',
                        html: `<p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Set new login password for <b>${data.full_name}</b> (${data.phone}):</p>` +
                              `<input id="swal-new-pw" class="swal2-input font-mono" placeholder="Minimum 6 characters" value="${generated}">`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Save & Activate Password',
                        confirmButtonColor: '#2563eb',
                        preConfirm: () => {
                            const pw = document.getElementById('swal-new-pw').value;
                            if (!pw || pw.length < 6) {
                                Swal.showValidationMessage('Password must be at least 6 characters');
                            }
                            return pw;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ url('/super-admin/students') }}/" + data.db_id + "/reset-password", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ new_password: result.value })
                            })
                            .then(res => res.json())
                            .then(resp => {
                                if (resp.status === 'success') {
                                    Swal.fire({
                                        title: 'Password Activated!',
                                        html: `New password for <b>${data.full_name}</b> is:<br><div class="my-3 p-3 bg-slate-100 dark:bg-slate-700 rounded-xl font-mono text-xl text-blue-600 font-bold select-all">${result.value}</div><span class="text-xs text-slate-500">Please share this password with the student.</span>`,
                                        icon: 'success'
                                    });
                                } else {
                                    Swal.fire('Error', resp.message || 'Failed to update password', 'error');
                                }
                            })
                            .catch(err => Swal.fire('Error', err.message, 'error'));
                        }
                    });
                }
            }},
        ]
    });

    document.getElementById("student-search").addEventListener("keyup", function(){
        table.setFilter("full_name", "like", this.value);
    });

    document.getElementById("branch-filter").addEventListener("change", function(){
        if(this.value) {
            table.setFilter("branch_name", "=", this.value);
        } else {
            table.clearFilter();
        }
    });

    document.getElementById("kyc-filter").addEventListener("change", function(){
        if(this.value) {
            table.setFilter("kyc_status", "=", this.value);
        } else {
            table.clearFilter();
        }
    });

    document.getElementById("export-students-csv").addEventListener("click", function(){
        table.download("csv", "students_directory.csv");
    });

    document.getElementById("edit-student-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="db_id"]').value;

        fetch(appUrl("super-admin/students/" + id + "/update"), {
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
                window.dispatchEvent(new CustomEvent('close-edit-student-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update student record.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });

    function deleteStudent(id) {
        Swal.fire({
            title: 'Delete Student Record?',
            text: 'This will permanently remove this student and their login credentials.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, Delete Student'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl("super-admin/students/" + id), {
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
                        window.dispatchEvent(new CustomEvent('close-edit-student-modal'));
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        toastr.error(data.message || "Failed to delete student record.");
                    }
                })
                .catch(err => {
                    toastr.error("An error occurred during deletion.");
                });
            }
        });
    }

    // Escape key closes lightbox
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.dispatchEvent(new CustomEvent('close-kyc-modal'));
        }
    });
</script>
@endsection
