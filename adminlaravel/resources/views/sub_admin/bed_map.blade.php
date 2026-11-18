@extends('layouts.admin')

@section('title', 'Bed Allocation Grid - ' . (isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Rudra Group PG'))
@section('page_title', (isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Branch') . ' Bed Allocation Map')

@section('content')
<div x-data="{
    residentModalOpen: false,
    editRoomModalOpen: false,
    editResidentModalOpen: false,
    selectedBed: null,
    editResidentForm: {
        db_id: '',
        full_name: '',
        phone: '',
        email: '',
        raw_joining_date: '',
        aadhaar_number: '',
        pan_number: '',
        parent_name: '',
        parent_phone: '',
        emergency_contact: '',
        current_address: '',
        initial_meter_reading: 0,
        kyc_status: 'APPROVED',
        rent_status: 'PAID',
        rent_balance_due: 0,
        deposit_balance_due: 0
    },
    openEditResidentModal(bed) {
        this.editResidentForm = {
            db_id: bed.student_id,
            full_name: bed.student_name || '',
            phone: bed.student_phone || '',
            email: (bed.student_email && bed.student_email !== 'N/A') ? bed.student_email : '',
            raw_joining_date: bed.student_raw_joining_date || '',
            aadhaar_number: (bed.student_aadhaar && bed.student_aadhaar !== 'N/A') ? bed.student_aadhaar : '',
            pan_number: bed.student_pan || '',
            parent_name: (bed.student_parent_name && bed.student_parent_name !== 'N/A') ? bed.student_parent_name : '',
            parent_phone: bed.student_parent_phone || '',
            emergency_contact: (bed.student_emergency_contact && bed.student_emergency_contact !== 'N/A') ? bed.student_emergency_contact : '',
            current_address: (bed.student_address && bed.student_address !== 'N/A') ? bed.student_address : '',
            initial_meter_reading: bed.student_initial_meter_reading || 0,
            kyc_status: bed.student_kyc || 'APPROVED',
            rent_status: bed.student_rent_status || 'PAID',
            rent_balance_due: bed.student_rent_balance || 0,
            deposit_balance_due: bed.student_deposit_balance || 0
        };
        this.residentModalOpen = false;
        this.editResidentModalOpen = true;
    },
    submitEditResidentForm(event) {
        const formData = new FormData(event.target);
        const studentId = this.editResidentForm.db_id;
        fetch(appUrl('sub-admin/students/' + studentId + '/update'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Resident Updated',
                        text: data.message || 'Resident record updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                }
                this.editResidentModalOpen = false;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: data.message || 'Could not update resident record.'
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Could not update resident record.');
                }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred during submission.'
                });
            }
        });
    },
    editForm: {
        id: '',
        room_number: '',
        floor: 1,
        sharing_type: '',
        max_beds: 2,
        is_ac: 1,
        rent: 0,
        deposit: 0
    },
    viewResident(bed, roomNum) {
        this.selectedBed = { ...bed, room_number: roomNum };
        this.residentModalOpen = true;
    },
    openEditModal(room) {
        this.editForm = {
            id: room.id,
            room_number: room.room_number,
            floor: parseInt(room.floor),
            sharing_type: room.sharing_type,
            max_beds: room.total_beds || room.max_beds || 2,
            is_ac: room.is_ac ? 1 : 0,
            rent: room.rent || 0,
            deposit: room.deposit || 0
        };
        this.editRoomModalOpen = true;
    },
    onSharingTypeChange(val) {
        const numMatch = val.match(/^(\d+)/);
        if (numMatch) {
            this.editForm.max_beds = parseInt(numMatch[1]);
        } else if (val.includes('Single') || val.includes('Private')) {
            this.editForm.max_beds = 1;
        }
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hotel text-blue-600"></i>
                <span>{{ isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Branch' }} — Bed Allocation Map</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Interactive 2D room matrix map for managing bed status, resident details, and occupancy.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold rounded-xl border border-blue-200 dark:border-blue-800">
                <i class="fa-solid fa-code-branch mr-1"></i> {{ isset($activeBranch) && $activeBranch ? $activeBranch->code : 'ACTIVE' }}
            </span>
        </div>
    </div>

    <!-- Status Legend -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-6">
        <div class="flex flex-wrap items-center justify-around text-center text-xs font-semibold gap-2">
            <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-emerald-500 rounded-md inline-block animate-pulse"></span> Available Bed</div>
            <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-rose-500 rounded-md inline-block"></span> Occupied Bed (Click for profile)</div>
            <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-amber-500 rounded-md inline-block"></span> Reserved Bed</div>
        </div>
    </div>

    @if(empty($rooms))
        <div class="bg-white dark:bg-slate-800 p-12 rounded-2xl border border-slate-200 dark:border-slate-700 text-center my-6">
            <i class="fa-solid fa-bed text-5xl text-slate-400 mb-3"></i>
            <h4 class="text-base font-bold text-slate-800 dark:text-slate-200">No rooms found</h4>
            <p class="text-xs text-slate-500 mt-1">There are no rooms registered under {{ isset($activeBranch) && $activeBranch ? $activeBranch->name : 'this branch' }}.</p>
        </div>
    @else
        <div class="space-y-6 mb-8">
            @foreach(collect($rooms)->groupBy('floor') as $floor => $floorRooms)
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider font-mono">
                            <i class="fa-solid fa-layer-group mr-1.5"></i>
                            <span>{{ (int) $floor === 0 ? 'GROUND FLOOR' : 'FLOOR ' . $floor }} ({{ count($floorRooms) }} Rooms)</span>
                        </h4>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                            {{ collect($floorRooms)->sum('available_beds') }} Beds Available
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
                        @foreach($floorRooms as $room)
                            <div class="p-3.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 hover:border-blue-400 dark:hover:border-blue-500 transition-all hover:shadow-xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                        <span>Room {{ $room['room_number'] }}</span>
                                        @if($room['is_ac'])
                                            <i class="fa-solid fa-snowflake text-[10px] text-cyan-500" title="Air Conditioned"></i>
                                        @endif
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="bg-slate-900 dark:bg-slate-800 text-white text-[10px] font-semibold px-2 py-0.5 rounded">{{ $room['sharing_type'] }}</span>
                                        <button type="button" 
                                                @click.stop="openEditModal({{ json_encode($room) }})"
                                                class="px-1.5 py-0.5 text-[10px] font-semibold bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 rounded transition-colors flex items-center gap-1 cursor-pointer"
                                                title="Edit Room Capacity & Beds">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5 my-2">
                                    @foreach($room['beds'] as $bed)
                                        @if($bed['status'] == 'occupied')
                                            <button @click="viewResident({{ json_encode($bed) }}, '{{ $room['room_number'] }}')" 
                                                    class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold py-1.5 px-2.5 rounded-lg flex-1 text-left shadow-xs transition-colors flex items-center justify-between group">
                                                <span class="truncate"><i class="fa-solid fa-user me-1 text-[10px]"></i> {{ $bed['code'] }}</span>
                                                <i class="fa-solid fa-circle-info text-[10px] opacity-70 group-hover:opacity-100"></i>
                                            </button>
                                        @elseif($bed['status'] == 'reserved')
                                            <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold py-1.5 px-2.5 rounded-lg flex-1 text-left shadow-xs transition-colors">
                                                <i class="fa-solid fa-clock me-1 text-[10px]"></i> {{ $bed['code'] }}
                                            </button>
                                        @else
                                            <button onclick="toastr.success('Bed {{ $bed['code'] }} in Room {{ $room['room_number'] }} is available!')" 
                                                    class="border border-emerald-600 text-emerald-600 dark:border-emerald-500 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 text-xs font-semibold py-1.5 px-2.5 rounded-lg flex-1 text-left transition-colors">
                                                <i class="fa-regular fa-circle-check me-1 text-[10px]"></i> {{ $bed['code'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500">
                                    <span>Rent: ₹{{ number_format($room['rent']) }}/mo</span>
                                    <span class="font-semibold {{ $room['available_beds'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $room['available_beds'] }} left
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Pagination Bar if rooms exceed page limit -->
    @if ($paginator->lastPage() > 1)
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between bg-white dark:bg-slate-800 px-6 py-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs gap-4">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Showing page <span class="font-bold text-slate-900 dark:text-white">{{ $paginator->currentPage() }}</span> of <span class="font-bold text-slate-900 dark:text-white">{{ $paginator->lastPage() }}</span> ({{ $paginator->total() }} total rooms)
            </div>
            <div class="flex items-center gap-1.5">
                @if ($paginator->onFirstPage())
                    <span class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-600 cursor-not-allowed text-xs font-semibold flex items-center">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition-colors flex items-center">
                        <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                    </a>
                @endif

                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    @if ($i == $paginator->currentPage())
                        <span class="z-10 bg-blue-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold border border-blue-600 shadow-sm shadow-blue-500/20">Page {{ $i }}</span>
                    @else
                        <a href="{{ $paginator->url($i) }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-700 px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors">Page {{ $i }}</a>
                    @endif
                @endfor

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition-colors flex items-center">
                        Next <i class="fa-solid fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <span class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-600 cursor-not-allowed text-xs font-semibold flex items-center">
                        Next <i class="fa-solid fa-chevron-right ml-1"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Resident Profile Details Modal -->
    <div x-show="residentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="residentModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-lg"
                         :class="selectedBed && selectedBed.student_name ? 'bg-blue-600/30 text-blue-400 border border-blue-500/30' : 'bg-emerald-600/30 text-emerald-400 border border-emerald-500/30'">
                        <i class="fa-solid" :class="selectedBed && selectedBed.student_name ? 'fa-id-card' : 'fa-bed'"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base leading-tight" x-text="selectedBed && selectedBed.student_name ? 'Resident Profile Details' : 'Available Bed Details'"></h4>
                        <p class="text-xs text-slate-400">
                            <span x-text="'Room ' + (selectedBed ? selectedBed.room_number : '')"></span> • 
                            <span x-text="selectedBed ? selectedBed.code : ''"></span> • 
                            <span x-text="selectedBed ? selectedBed.branch_name : '{{ $activeBranch->name ?? "PG Branch" }}'"></span>
                        </p>
                    </div>
                </div>
                <button @click="residentModalOpen = false" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto" x-if="selectedBed">
                <!-- REAL RESIDENT PROFILE DETAILS -->
                <template x-if="selectedBed && selectedBed.student_name">
                    <div class="space-y-4">
                        <!-- Profile Card Banner -->
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                            <template x-if="selectedBed.student_photo">
                                <img :src="selectedBed.student_photo" class="w-16 h-16 rounded-2xl object-cover border-2 border-blue-500 shadow-sm shrink-0" alt="Photo">
                            </template>
                            <template x-if="!selectedBed.student_photo">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-extrabold text-xl shadow-md shrink-0">
                                    <span x-text="selectedBed.student_name ? selectedBed.student_name.substring(0, 2).toUpperCase() : 'PG'"></span>
                                </div>
                            </template>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white truncate" x-text="selectedBed.student_name"></h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300" x-text="selectedBed.student_app_ref"></span>
                                </div>
                                <div class="flex items-center gap-2 mt-1.5 text-xs flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px]"
                                          :class="selectedBed.student_kyc === 'VERIFIED' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                                          x-text="'KYC: ' + (selectedBed.student_kyc || 'VERIFIED')"></span>
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px]"
                                          :class="selectedBed.student_rent_status === 'PAID' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : (selectedBed.student_rent_status === 'PARTIALLY_PAID' || selectedBed.student_rent_balance > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300')"
                                          x-text="selectedBed.student_rent_status === 'PAID' ? 'Rent: PAID' : (selectedBed.student_rent_status === 'PARTIALLY_PAID' || selectedBed.student_rent_balance > 0 ? 'Rent: PARTIAL (₹' + Number(selectedBed.student_rent_balance || 0).toLocaleString() + ' Left)' : 'Rent: DUE')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Contact Actions -->
                        <div class="grid grid-cols-2 gap-3">
                            <a :href="'tel:' + selectedBed.student_phone" class="p-3 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-2.5 text-blue-800 dark:text-blue-300 transition-colors">
                                <i class="fa-solid fa-phone text-blue-600"></i>
                                <div class="min-w-0">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Mobile Call</span>
                                    <span class="text-xs font-bold truncate block" x-text="selectedBed.student_phone || 'N/A'"></span>
                                </div>
                            </a>
                            <a :href="'https://wa.me/' + (selectedBed.student_phone ? selectedBed.student_phone.replace(/[^0-9]/g, '') : '')" target="_blank" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-2.5 text-emerald-800 dark:text-emerald-300 transition-colors">
                                <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i>
                                <div class="min-w-0">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">WhatsApp</span>
                                    <span class="text-xs font-bold truncate block" x-text="selectedBed.student_phone || 'N/A'"></span>
                                </div>
                            </a>
                        </div>

                        <!-- Comprehensive Resident Details -->
                        <div class="space-y-2 text-xs bg-slate-50 dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Email Address:</span>
                                <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="selectedBed.student_email || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Joining / Admission:</span>
                                <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="selectedBed.student_admission_date || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Monthly Rent:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'₹' + Number(selectedBed.monthly_rent || 0).toLocaleString()"></span>
                            </div>
                            <template x-if="selectedBed.student_rent_balance > 0">
                                <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60 bg-amber-50/70 dark:bg-amber-950/30 px-2 rounded-lg">
                                    <span class="text-amber-700 dark:text-amber-300 font-bold">Rent Left / Due:</span>
                                    <span class="font-black text-amber-600 dark:text-amber-400" x-text="'₹' + Number(selectedBed.student_rent_balance).toLocaleString()"></span>
                                </div>
                            </template>
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Security Deposit:</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400" x-text="'₹' + Number(selectedBed.security_deposit || 0).toLocaleString()"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Emergency / Parent:</span>
                                <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="(selectedBed.student_parent_name ? selectedBed.student_parent_name + ' - ' : '') + (selectedBed.student_emergency_contact || 'N/A')"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500 font-medium">Aadhaar Number:</span>
                                <span class="font-mono text-slate-900 dark:text-slate-100" x-text="selectedBed.student_aadhaar || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-500 font-medium">Address:</span>
                                <span class="font-medium text-slate-900 dark:text-slate-100 text-right max-w-[250px] truncate" :title="selectedBed.student_address" x-text="selectedBed.student_address || 'N/A'"></span>
                            </div>
                        </div>

                        <!-- Direct Record Payment Button -->
                        <div class="flex items-center justify-between gap-2 pt-1">
                            <button type="button" @click="openEditResidentModal(selectedBed)" 
                                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-bold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                                <i class="fa-solid fa-user-pen"></i> Edit Resident Details
                            </button>
                            <a :href="'{{ route('sub_admin.rent_ledger') }}?student_id=' + selectedBed.student_id" 
                               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-money-bill-wave"></i> Record Payment
                            </a>
                        </div>
                    </div>
                </template>

                <!-- AVAILABLE / VACANT BED STATE (Clean, No Dummy Data) -->
                <template x-if="selectedBed && !selectedBed.student_name">
                    <div class="space-y-4">
                        <div class="p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center">
                            <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <h4 class="font-bold text-base text-slate-900 dark:text-slate-100">
                                Bed <span x-text="selectedBed.code"></span> is Vacant & Available
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Ready for allocation in <span class="font-bold" x-text="'Room ' + selectedBed.room_number"></span> (<span x-text="selectedBed.branch_name"></span>).
                            </p>
                            
                            <div class="grid grid-cols-2 gap-3 mt-4 text-left">
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Monthly Rent</span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="'₹' + Number(selectedBed.monthly_rent || 0).toLocaleString()"></span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Security Deposit</span>
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400" x-text="'₹' + Number(selectedBed.security_deposit || 0).toLocaleString()"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <a href="{{ route('sub_admin.verifications') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-user-plus"></i> Allocate Resident
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <button @click="residentModalOpen = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold text-xs rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Resident Details Modal (Sub Admin Branch Scoped) -->
    <div x-show="editResidentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editResidentModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between shrink-0">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-amber-400"></i> Edit Resident Details: <span class="text-blue-400" x-text="editResidentForm.full_name"></span>
                </h4>
                <button @click="editResidentModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-resident-form" @submit.prevent="submitEditResidentForm($event)" class="p-6 space-y-5 overflow-y-auto flex-1">
                @csrf
                <input type="hidden" name="db_id" x-model="editResidentForm.db_id">

                <!-- Section 1: Basic & Admission -->
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-address-card"></i> 1. Basic & Admission Details
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Legal Name *</label>
                            <input type="text" name="full_name" required x-model="editResidentForm.full_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Mobile Number *</label>
                            <input type="text" name="phone" required x-model="editResidentForm.phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address</label>
                            <input type="email" name="email" x-model="editResidentForm.email" placeholder="resident@domain.com" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1 flex items-center justify-between">
                                <span>Joining Date *</span>
                                <span class="text-[10px] text-slate-400 font-normal">Admission date</span>
                            </label>
                            <input type="date" name="joining_date" x-model="editResidentForm.raw_joining_date" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
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
                            <input type="text" name="aadhaar_number" x-model="editResidentForm.aadhaar_number" placeholder="12-digit Aadhaar" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">PAN Card No.</label>
                            <input type="text" name="pan_number" x-model="editResidentForm.pan_number" placeholder="10-digit PAN" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 uppercase font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">KYC Status</label>
                            <select name="kyc_status" required x-model="editResidentForm.kyc_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
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
                            <input type="text" name="parent_name" x-model="editResidentForm.parent_name" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Parent Phone</label>
                            <input type="text" name="parent_phone" x-model="editResidentForm.parent_phone" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Emergency Contact</label>
                            <input type="text" name="emergency_contact" x-model="editResidentForm.emergency_contact" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Permanent Home Address</label>
                        <textarea name="current_address" rows="2" x-model="editResidentForm.current_address" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100"></textarea>
                    </div>
                </div>

                <!-- Section 4: Meter Baseline & Dues -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-receipt"></i> 4. Utility Baseline & Rent Balance
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Starting Meter (Units)</label>
                            <input type="number" step="0.01" name="initial_meter_reading" x-model="editResidentForm.initial_meter_reading" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Status</label>
                            <select name="rent_status" required x-model="editResidentForm.rent_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                                <option value="PENDING">PENDING</option>
                                <option value="PARTIALLY_PAID">PARTIALLY PAID</option>
                                <option value="PAID">PAID</option>
                                <option value="OVERDUE">OVERDUE</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Rent Left / Due (₹)</label>
                            <input type="number" step="0.01" name="rent_balance_due" x-model="editResidentForm.rent_balance_due" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit Balance (₹)</label>
                            <input type="number" step="0.01" name="deposit_balance_due" x-model="editResidentForm.deposit_balance_due" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editResidentModalOpen = false" class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-600/30 transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Save Resident Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Room Capacity & Bed Size Modal -->
    <div x-show="editRoomModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="editRoomModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-xl overflow-hidden transform transition-all">
            
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base leading-tight">Edit Room & Increase Bed Size</h4>
                        <p class="text-xs text-slate-400" x-text="'Room ' + editForm.room_number + ' • {{ isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Branch' }}'"></p>
                    </div>
                </div>
                <button @click="editRoomModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="subadmin-edit-room-form" @submit.prevent="
                const url = '{{ url('sub-admin/rooms') }}/' + editForm.id + '/update';
                const payload = {
                    room_number: editForm.room_number,
                    floor_number: parseInt(editForm.floor),
                    sharing_type: editForm.sharing_type,
                    max_beds: parseInt(editForm.max_beds),
                    is_ac: parseInt(editForm.is_ac),
                    rent: parseFloat(editForm.rent),
                    deposit: parseFloat(editForm.deposit)
                };

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json().then(data => ({ status: r.status, data })))
                .then(res => {
                    if (res.status === 200 && res.data.status === 'success') {
                        toastr.success(res.data.message || 'Room updated successfully!');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        toastr.error(res.data.message || 'Failed to update room.');
                    }
                })
                .catch(err => {
                    toastr.error('Error connecting to server.');
                });
            " class="p-6 space-y-4">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Room Number</label>
                        <input type="text" name="room_number" required x-model="editForm.room_number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Floor</label>
                        <select name="floor_number" required x-model.number="editForm.floor" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="0">Ground Floor (Floor 0)</option>
                            <option value="1">Floor 1</option>
                            <option value="2">Floor 2</option>
                            <option value="3">Floor 3</option>
                            <option value="4">Floor 4</option>
                            <option value="5">Floor 5</option>
                            <option value="6">Floor 6</option>
                            <option value="7">Floor 7</option>
                            <option value="8">Floor 8</option>
                            <option value="9">Floor 9</option>
                            <option value="10">Floor 10</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Sharing Type</label>
                        <select name="sharing_type" required x-model="editForm.sharing_type" @change="onSharingTypeChange($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="Single Sharing">Single Sharing (1 Person)</option>
                            <option value="2-Sharing">2-Sharing (2 Persons)</option>
                            <option value="2 Sharing AC">2 Sharing AC (2 Persons)</option>
                            <option value="3-Sharing">3-Sharing (3 Persons)</option>
                            <option value="3 Sharing AC">3 Sharing AC (3 Persons)</option>
                            <option value="4-Sharing">4-Sharing (4 Persons)</option>
                            <option value="4 Sharing AC">4 Sharing AC (4 Persons)</option>
                            <option value="5-Sharing">5-Sharing (5 Persons)</option>
                            <option value="5 Sharing AC">5 Sharing AC (5 Persons)</option>
                            <option value="6-Sharing">6-Sharing (6 Persons)</option>
                            <option value="6 Sharing AC">6 Sharing AC (6 Persons)</option>
                            <option value="7-Sharing">7-Sharing (7 Persons)</option>
                            <option value="8-Sharing">8-Sharing (8 Persons)</option>
                            <option value="Private Room">Private Room (1 Person)</option>
                            <option value="Dormitory">Dormitory (Custom)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-700 dark:text-blue-400 uppercase mb-1">Total Beds (Capacity)</label>
                        <input type="number" name="max_beds" min="1" max="20" required x-model.number="editForm.max_beds" 
                               class="w-full px-3.5 py-2.5 bg-blue-50/50 dark:bg-blue-950/30 border-2 border-blue-400 dark:border-blue-600 rounded-xl text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none text-blue-900 dark:text-blue-100">
                    </div>
                </div>

                <div class="p-3 bg-blue-50/70 dark:bg-blue-950/40 rounded-xl border border-blue-200/60 dark:border-blue-800 text-[11px] text-blue-800 dark:text-blue-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                    <span>Increasing <b>Total Beds</b> will automatically create new beds (e.g. Bed 401-D, Bed 401-E for 5-sharing).</span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">AC Status</label>
                        <select name="is_ac" required x-model="editForm.is_ac" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option :value="1">AC Room</option>
                            <option :value="0">Non-AC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Monthly Rent (₹)</label>
                        <input type="number" name="rent" required x-model.number="editForm.rent" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Deposit (₹)</label>
                        <input type="number" name="deposit" required x-model.number="editForm.deposit" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editRoomModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
