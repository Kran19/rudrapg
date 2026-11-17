@extends('layouts.admin')

@section('title', 'Master Room Matrix - Rudra Group PG')
@section('page_title', 'Master Room Matrix')

@section('content')
<div x-data="roomsMatrix()" x-init="init()"
     @open-edit-room-modal.window="openEditModal($event.detail)"
     @close-edit-room-modal.window="editRoomModalOpen = false">

    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100" x-text="currentBranchName() + ' Master Room Matrix'"></h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300" x-text="filteredRooms().length + ' Rooms'"></span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Overview of <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="filteredRooms().length"></span> rooms and 
                <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="totalBranchBeds()"></span> beds 
                (<span class="text-rose-600 font-semibold" x-text="totalOccupiedBeds() + ' Occupied'"></span>, 
                <span class="text-emerald-600 font-semibold" x-text="totalAvailableBeds() + ' Vacant'"></span>).
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="viewMode = viewMode === 'grid' ? 'table' : 'grid'" 
                    class="border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                <i class="fa-solid" :class="viewMode === 'grid' ? 'fa-table-list' : 'fa-border-all'"></i>
                <span x-text="viewMode === 'grid' ? 'Switch to Tabulator Table' : 'Switch to Visual Room Grid'"></span>
            </button>
            <button @click="openAddModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add New Room
            </button>
            <button type="button"
                    @click="openDeleteSelectionModal()" 
                    class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer"
                    title="Delete one or multiple rooms">
                <i class="fa-solid fa-trash-can text-rose-500"></i> Delete Room(s)
            </button>
        </div>
    </div>

    <!-- Branch Filter Tabs (Dynamically Switch Across All PG Branches) -->
    <div class="bg-white dark:bg-slate-800 p-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-6">
        <div class="flex items-center justify-between px-2 pb-2 mb-2 border-b border-slate-100 dark:border-slate-700/60">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-blue-500"></i> Select Branch Filter
            </span>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Click on any branch to view rooms</span>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-thin">
            <template x-for="branch in allBranches" :key="branch.id">
                <button type="button" 
                        @click="setBranch(branch.id)"
                        :class="selectedBranchId == branch.id 
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20 ring-2 ring-blue-600' 
                            : 'bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700'"
                        class="px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2.5 transition-all whitespace-nowrap cursor-pointer">
                    <i class="fa-solid fa-building-user text-sm" :class="selectedBranchId == branch.id ? 'text-blue-200' : 'text-slate-400'"></i>
                    <span x-text="branch.name"></span>
                    <span :class="selectedBranchId == branch.id ? 'bg-blue-700/80 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                          class="text-[10px] px-2 py-0.5 rounded-full font-semibold"
                          x-text="roomsCountForBranch(branch.id) + ' Rooms'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Visual Grid View (Organized by Floor with Real-time Occupancy) -->
    <div x-show="viewMode === 'grid'" class="space-y-6 mb-8">
        <template x-if="filteredRooms().length === 0">
            <div class="bg-white dark:bg-slate-800 p-12 text-center rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                <div class="w-16 h-16 bg-blue-50 dark:bg-slate-700 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">No Rooms in This Branch</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Click "Add New Room" to set up rooms and beds for this branch.</p>
                <button @click="openAddModal()" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-md hover:bg-blue-700">
                    <i class="fa-solid fa-plus mr-1"></i> Add First Room
                </button>
            </div>
        </template>

        <template x-for="floor in availableFloors()" :key="floor">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                <!-- Floor Header Banner -->
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <span class="bg-slate-900 dark:bg-slate-950 text-white font-bold text-xs px-3.5 py-1.5 rounded-full flex items-center gap-2">
                        🏢 <span x-text="parseInt(floor) === 0 ? 'GROUND FLOOR' : 'FLOOR ' + floor"></span> 
                        <span class="text-slate-300 font-normal text-[11px]" x-text="'(Rooms ' + floorRoomsSummary(floor) + ')'"></span>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        <span class="font-bold text-slate-700 dark:text-slate-200" x-text="roomsOnFloor(floor).length"></span> Rooms • 
                        <span class="font-bold text-slate-700 dark:text-slate-200" x-text="totalBedsOnFloor(floor)"></span> Beds
                    </span>
                </div>

                <!-- Rooms Grid (Responsive Cards) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                    <template x-for="room in roomsOnFloor(floor)" :key="room.id">
                        <div class="p-3.5 rounded-xl border transition-all hover:shadow-md"
                             :class="room.status === 'Full' 
                                ? 'border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-900/10' 
                                : 'border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-900/10'">
                            
                            <!-- Card Header: Room Number, AC status, and Edit Button -->
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-sm text-slate-900 dark:text-slate-100" x-text="'Room ' + room.room_number"></span>
                                    <template x-if="room.is_ac">
                                        <i class="fa-solid fa-snowflake text-cyan-500 dark:text-cyan-400 text-xs" title="Air Conditioned (AC)"></i>
                                    </template>
                                    <template x-if="!room.is_ac">
                                        <i class="fa-solid fa-wind text-slate-400 dark:text-slate-500 text-xs" title="Non-AC"></i>
                                    </template>
                                </div>

                                <!-- Room Actions: Edit & Delete -->
                                <div class="flex items-center gap-1">
                                    <button type="button" 
                                            @click.stop="openEditModal(room)"
                                            class="px-2 py-0.5 text-[11px] font-semibold bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 rounded-md transition-colors flex items-center gap-1 cursor-pointer"
                                            title="Edit Room Capacity & Beds">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                    </button>
                                    <button type="button" 
                                            @click.stop="confirmSingleRoomDelete(room)"
                                            class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-md transition-colors cursor-pointer"
                                            title="Delete Room">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- User / Bed Icons Row (Interactive: Click to View Resident Details) -->
                            <div class="flex items-center justify-center flex-wrap gap-1.5 my-2.5 py-1.5 bg-white/60 dark:bg-slate-900/40 rounded-lg">
                                <template x-for="(bed, bIdx) in room.beds" :key="bed.id || bIdx">
                                    <button type="button" 
                                            @click.stop="openResidentModal(bed, room)"
                                            :title="bed.is_occupied 
                                                ? (bed.student ? ('Resident: ' + bed.student.full_name + ' (' + bed.bed_code + ') - Click for details') : ('Occupied: ' + bed.bed_code + ' - Click for details')) 
                                                : ('Available Bed: ' + bed.bed_code + ' - Click to view')"
                                            class="p-1 rounded-md transition-transform hover:scale-130 active:scale-95 focus:outline-none cursor-pointer group relative">
                                        <template x-if="bed.is_occupied">
                                            <i class="fa-solid fa-user text-rose-600 text-base drop-shadow-sm group-hover:text-rose-700 transition-colors"></i>
                                        </template>
                                        <template x-if="!bed.is_occupied">
                                            <i class="fa-regular fa-user text-emerald-600 text-base group-hover:text-emerald-700 transition-colors"></i>
                                        </template>
                                    </button>
                                </template>
                            </div>

                            <!-- Card Footer: Sharing Type and Vacancy Badge -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/60 mt-1">
                                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 truncate max-w-[90px]" x-text="room.sharing_type"></span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md"
                                      :class="room.status === 'Full' 
                                        ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' 
                                        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'"
                                      x-text="room.status === 'Full' ? 'FULL' : room.available_beds + ' Left'">
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Tabulator Table View -->
    <div x-show="viewMode === 'table'" class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100">Room Inventory Table</h4>
            <span class="text-xs text-slate-500 dark:text-slate-400" x-text="'Showing rooms for ' + currentBranchName()"></span>
        </div>
        <div id="rooms-table"></div>
    </div>

    <!-- RESIDENT & BED DETAILS MODAL (Opens when clicking user icon) -->
    <div x-show="residentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="residentModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-blue-300">
                        <i class="fa-solid" :class="activeBed && activeBed.is_occupied ? 'fa-id-card' : 'fa-bed'"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base" x-text="activeBed && activeBed.is_occupied ? 'Resident Profile Details' : 'Available Bed Details'"></h4>
                        <p class="text-xs text-slate-300">
                            <span x-text="activeRoom ? 'Room ' + activeRoom.room_number : ''"></span> • 
                            <span x-text="activeBed ? activeBed.bed_code : ''"></span> • 
                            <span x-text="currentBranchName()"></span>
                        </p>
                    </div>
                </div>
                <button @click="residentModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body: OCCUPIED CASE (Full Real Customer Profile) -->
            <template x-if="activeResident">
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Resident Profile Banner -->
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                        <template x-if="activeResident.photo_url">
                            <img :src="activeResident.photo_url" class="w-16 h-16 rounded-2xl object-cover border-2 border-blue-500 shadow-sm" alt="Photo">
                        </template>
                        <template x-if="!activeResident.photo_url">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-xl flex items-center justify-center shadow-sm">
                                <span x-text="activeResident.full_name ? activeResident.full_name.substring(0, 2).toUpperCase() : 'PG'"></span>
                            </div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h5 class="font-bold text-base text-slate-900 dark:text-slate-100 truncate" x-text="activeResident.full_name"></h5>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300" x-text="activeResident.app_reference"></span>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="px-2 py-0.5 rounded-md font-bold text-[10px]"
                                      :class="activeResident.kyc_status === 'VERIFIED' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                                      x-text="'KYC: ' + activeResident.kyc_status"></span>
                                <span class="px-2 py-0.5 rounded-md font-bold text-[10px]"
                                      :class="activeResident.rent_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : (activeResident.rent_status === 'PARTIALLY_PAID' || activeResident.rent_balance_due > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')"
                                      x-text="activeResident.rent_status === 'PAID' ? 'Rent: PAID' : (activeResident.rent_status === 'PARTIALLY_PAID' || activeResident.rent_balance_due > 0 ? 'Rent: PARTIAL (₹' + Number(activeResident.rent_balance_due || 0).toLocaleString() + ' Left)' : 'Rent: ' + activeResident.rent_status)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Contact Links -->
                    <div class="grid grid-cols-2 gap-3">
                        <a :href="'tel:' + activeResident.phone" class="p-3 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-2.5 text-blue-800 dark:text-blue-300 transition-colors">
                            <i class="fa-solid fa-phone text-blue-600"></i>
                            <div class="min-w-0">
                                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Mobile Call</span>
                                <span class="text-xs font-bold truncate block" x-text="activeResident.phone"></span>
                            </div>
                        </a>
                        <a :href="'https://wa.me/' + (activeResident.phone ? activeResident.phone.replace(/[^0-9]/g, '') : '')" target="_blank" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-2.5 text-emerald-800 dark:text-emerald-300 transition-colors">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i>
                            <div class="min-w-0">
                                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">WhatsApp</span>
                                <span class="text-xs font-bold truncate block" x-text="activeResident.phone"></span>
                            </div>
                        </a>
                    </div>

                    <!-- Details Table -->
                    <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/40 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Email Address:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="activeResident.email"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Joining / Admission:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="activeResident.joining_date"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Monthly Rent:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="'₹' + Number(activeResident.monthly_rent).toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Security Deposit:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400" x-text="'₹' + Number(activeResident.security_deposit).toLocaleString()"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Parent / Guardian:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="activeResident.parent_name + ' (' + activeResident.parent_phone + ')'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Emergency Contact:</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="activeResident.emergency_contact"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-medium">Aadhaar Number:</span>
                            <span class="font-mono text-slate-900 dark:text-slate-100" x-text="activeResident.aadhaar_number"></span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500 font-medium">Address:</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100 text-right max-w-[250px] truncate" :title="activeResident.current_address" x-text="activeResident.current_address"></span>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="residentModalOpen = false" class="px-5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </template>

            <!-- Modal Body: VACANT / AVAILABLE BED CASE (No Dummy Data) -->
            <template x-if="!activeResident">
                <div class="p-6 space-y-4">
                    <div class="p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center">
                        <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                        <h5 class="font-bold text-base text-slate-900 dark:text-slate-100">
                            Bed <span x-text="activeBed ? activeBed.bed_code : ''"></span> is Vacant & Available
                        </h5>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            This bed in <span class="font-bold" x-text="'Room ' + (activeRoom ? activeRoom.room_number : '')"></span> (<span x-text="currentBranchName()"></span>) is ready for student allocation.
                        </p>
                        
                        <div class="grid grid-cols-2 gap-3 mt-4 text-left">
                            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Monthly Rent</span>
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="'₹' + Number(activeBed ? activeBed.monthly_rent : 0).toLocaleString()"></span>
                            </div>
                            <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-emerald-100 dark:border-emerald-800/40">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Security Deposit</span>
                                <span class="text-sm font-bold text-blue-600 dark:text-blue-400" x-text="'₹' + Number(activeBed ? activeBed.security_deposit : 0).toLocaleString()"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('super_admin.students') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-user-plus"></i> Allocate in Student Directory
                        </a>
                        <button type="button" @click="residentModalOpen = false" class="px-5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- PURE TAILWIND MODAL: EDIT ROOM (Update Capacity, Beds & Occupancy) -->
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
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-door-open text-amber-400"></i> Edit Room: Room <span x-text="editForm.room_number"></span>
                </h4>
                <button @click="editRoomModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="edit-room-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="editForm.id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">PG Branch</label>
                        <select name="branch_id" required x-model="editForm.branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Room Number</label>
                        <input type="text" name="room_number" required x-model="editForm.room_number" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
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
                            <template x-if="editForm.floor > 10">
                                <option :value="editForm.floor" x-text="'Floor ' + editForm.floor" selected></option>
                            </template>
                        </select>
                    </div>
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
                    <span>Changing <b>Total Beds</b> will automatically adjust bed inventory for this room. Increasing capacity creates new available beds.</span>
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Security Deposit (₹)</label>
                        <input type="number" name="deposit" required x-model.number="editForm.deposit" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="deleteRoom(editForm.id)" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-trash"></i> Delete Room
                    </button>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="editRoomModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md cursor-pointer">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PURE TAILWIND MODAL: ADD NEW ROOM -->
    <div x-show="addRoomModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="addRoomModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-slate-900 to-blue-900 text-white p-5 flex items-center justify-between">
                <h4 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-door-open text-blue-400"></i> Add New Room
                </h4>
                <button @click="addRoomModalOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form id="create-room-form" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">PG Branch</label>
                    <select name="branch_id" x-model="addFormBranchId" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        @foreach($allBranches as $b)
                            <option value="{{ $b->id }}" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Room Number</label>
                        <input type="text" name="room_number" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="e.g. G01, 101, 401">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Floor</label>
                        <select name="floor_number" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="0">Ground Floor (Floor 0)</option>
                            <option value="1" selected>Floor 1</option>
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
                        <select name="sharing_type" required x-model="addSharingType" @change="onAddSharingTypeChange($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Total Beds (Capacity)</label>
                        <input type="number" name="max_beds" min="1" max="20" x-model.number="addMaxBeds" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Monthly Rent (₹)</label>
                        <input type="number" name="rent" value="6000" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="Monthly rent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Security Deposit (₹)</label>
                        <input type="number" name="deposit" value="10000" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="Security deposit">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="hidden" name="is_ac" value="0">
                    <input type="checkbox" name="is_ac" value="1" checked id="addAcChk" class="rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-blue-600 focus:ring-blue-500">
                    <label for="addAcChk" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Air Conditioned (AC Room)</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="addRoomModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 1: Select Room(s) for Deletion -->
    <div x-show="deleteSelectionModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="deleteSelectionModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden transform transition-all flex flex-col max-h-[88vh]">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-rose-900 to-slate-900 text-white p-5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base leading-tight">Delete Room(s)</h4>
                        <p class="text-xs text-rose-200/80">
                            Select room(s) to remove from <span class="font-bold text-white" x-text="currentBranchName()"></span>
                        </p>
                    </div>
                </div>
                <button @click="deleteSelectionModalOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Filter & Search Bar -->
            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 space-y-3 shrink-0">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px]">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
                        <input type="text" 
                               x-model="deleteSearchQuery" 
                               placeholder="Search room number (e.g. 401)..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>

                    <!-- Quick Select Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="selectAllVacantForDelete()" 
                                class="px-3 py-1.5 text-xs font-semibold bg-rose-100 hover:bg-rose-200 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 rounded-xl transition-colors cursor-pointer">
                            Select All Vacant
                        </button>
                        <button type="button" 
                                @click="deselectAllDelete()" 
                                class="px-3 py-1.5 text-xs font-semibold bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl transition-colors cursor-pointer">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Floor Filter Tabs inside Delete Modal -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button type="button" 
                            @click="deleteFloorFilter = 'all'"
                            :class="deleteFloorFilter === 'all' ? 'bg-rose-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                        All Floors
                    </button>
                    <template x-for="floor in availableFloors()" :key="'del-floor-' + floor">
                        <button type="button" 
                                @click="deleteFloorFilter = floor"
                                :class="deleteFloorFilter === floor ? 'bg-rose-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer"
                                x-text="parseInt(floor) === 0 ? 'Ground Floor' : 'Floor ' + floor">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Room Selection Grid (Scrollable) -->
            <div class="p-4 overflow-y-auto flex-1 space-y-2">
                <template x-if="deleteCandidateRooms().length === 0">
                    <div class="text-center py-8 text-slate-400 text-xs">
                        No rooms match the search or filter in this branch.
                    </div>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <template x-for="room in deleteCandidateRooms()" :key="'del-room-' + room.id">
                        <div @click="toggleRoomDeleteSelection(room)"
                             :class="{
                                 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-2 ring-rose-500/50': isRoomSelectedForDelete(room.id),
                                 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-slate-300': !isRoomSelectedForDelete(room.id) && room.occupied_beds === 0,
                                 'opacity-60 bg-slate-100 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 cursor-not-allowed': room.occupied_beds > 0
                             }"
                             class="p-3 rounded-2xl border transition-all cursor-pointer flex items-start gap-2.5 select-none relative">
                            
                            <!-- Checkbox -->
                            <div class="pt-0.5">
                                <input type="checkbox" 
                                       :checked="isRoomSelectedForDelete(room.id)"
                                       :disabled="room.occupied_beds > 0"
                                       @click.stop="toggleRoomDeleteSelection(room)"
                                       class="rounded text-rose-600 focus:ring-rose-500 border-slate-300 dark:border-slate-600 cursor-pointer">
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-slate-900 dark:text-slate-100" x-text="'Room ' + room.room_number"></span>
                                    <span class="text-[10px] font-semibold text-slate-400" x-text="parseInt(room.floor) === 0 ? 'GF' : 'F' + room.floor"></span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate" x-text="room.sharing_type"></div>
                                
                                <div class="mt-1.5 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500" x-text="(room.total_beds || room.max_beds) + ' beds'"></span>
                                    <template x-if="room.occupied_beds > 0">
                                        <span class="px-1.5 py-0.5 rounded font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                            Occupied
                                        </span>
                                    </template>
                                    <template x-if="room.occupied_beds === 0">
                                        <span class="px-1.5 py-0.5 rounded font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            Vacant
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between shrink-0">
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Selected for deletion: <span class="font-bold text-rose-600 dark:text-rose-400 text-sm" x-text="selectedDeleteRoomIds.length"></span> room(s)
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="deleteSelectionModalOpen = false" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="promptConfirmDelete()" 
                            :disabled="selectedDeleteRoomIds.length === 0"
                            :class="selectedDeleteRoomIds.length === 0 ? 'opacity-50 cursor-not-allowed bg-rose-400' : 'bg-rose-600 hover:bg-rose-700 cursor-pointer'"
                            class="px-5 py-2 text-xs font-semibold text-white rounded-xl shadow-md transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Delete Selected (<span x-text="selectedDeleteRoomIds.length"></span>)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: "Are You Sure" Confirmation Popup -->
    <div x-show="deleteConfirmModalOpen" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="deleteConfirmModalOpen = false" 
             class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-rose-200 dark:border-rose-900/60 w-full max-w-md overflow-hidden p-6 text-center">
            
            <div class="w-16 h-16 rounded-3xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center justify-center text-2xl mx-auto mb-4 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
                Are you sure you want to delete?
            </h3>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                You are about to delete <span class="font-bold text-rose-600 dark:text-rose-400" x-text="selectedDeleteRoomIds.length + ' room(s)'"></span>:
                <br>
                <span class="font-semibold text-slate-800 dark:text-slate-200 block my-1" x-text="selectedDeleteRoomNumbers()"></span>
                from <span class="font-bold text-slate-800 dark:text-slate-200" x-text="currentBranchName()"></span>.
            </p>

            <div class="p-3 bg-rose-50 dark:bg-rose-950/30 rounded-2xl border border-rose-200/60 dark:border-rose-900/40 text-[11px] text-rose-700 dark:text-rose-300 text-left mb-6 flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 shrink-0"></i>
                <span>This action will permanently remove the selected room(s) and their associated beds from the database. This action cannot be undone.</span>
            </div>

            <div class="flex items-center justify-center gap-3">
                <button type="button" 
                        @click="deleteConfirmModalOpen = false" 
                        :disabled="isDeleting"
                        class="px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-xl transition-colors cursor-pointer">
                    Cancel, Keep Rooms
                </button>
                <button type="button" 
                        @click="executeDeleteRooms()" 
                        :disabled="isDeleting"
                        class="px-5 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
                    <template x-if="isDeleting">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </template>
                    <template x-if="!isDeleting">
                        <i class="fa-solid fa-trash-can"></i>
                    </template>
                    <span x-text="isDeleting ? 'Deleting...' : 'Yes, Delete Room(s)'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function roomsMatrix() {
    return {
        allBranches: @json($allBranches),
        allRooms: @json($rooms),
        selectedBranchId: {{ $selectedBranchId ?? ($allBranches->first()?->id ?? 'null') }},
        viewMode: 'grid',
        addRoomModalOpen: false,
        editRoomModalOpen: false,
        residentModalOpen: false,
        deleteSelectionModalOpen: false,
        deleteConfirmModalOpen: false,
        selectedDeleteRoomIds: [],
        deleteFloorFilter: 'all',
        deleteSearchQuery: '',
        isDeleting: false,
        activeBed: null,
        activeRoom: null,
        activeResident: null,
        addFormBranchId: {{ $selectedBranchId ?? ($allBranches->first()?->id ?? 'null') }},
        addSharingType: '3 Sharing AC',
        addMaxBeds: 3,
        editForm: {
            id: '',
            branch_id: '',
            room_number: '',
            floor: 1,
            sharing_type: '',
            max_beds: 2,
            is_ac: 1,
            rent: 0,
            deposit: 0
        },
        tabulatorTable: null,

        onSharingTypeChange(val) {
            const numMatch = val.match(/^(\d+)/);
            if (numMatch) {
                this.editForm.max_beds = parseInt(numMatch[1]);
            } else if (val.includes('Single') || val.includes('Private')) {
                this.editForm.max_beds = 1;
            }
        },

        onAddSharingTypeChange(val) {
            const numMatch = val.match(/^(\d+)/);
            if (numMatch) {
                this.addMaxBeds = parseInt(numMatch[1]);
            } else if (val.includes('Single') || val.includes('Private')) {
                this.addMaxBeds = 1;
            }
        },

        init() {
            this.initTabulator();
        },

        filteredRooms() {
            if (!this.selectedBranchId) return this.allRooms;
            return this.allRooms.filter(r => parseInt(r.branch_id) === parseInt(this.selectedBranchId));
        },

        currentBranch() {
            return this.allBranches.find(b => parseInt(b.id) === parseInt(this.selectedBranchId)) || (this.allBranches.length > 0 ? this.allBranches[0] : null);
        },

        currentBranchName() {
            const b = this.currentBranch();
            return b ? b.name : 'All Branches';
        },

        roomsCountForBranch(branchId) {
            return this.allRooms.filter(r => parseInt(r.branch_id) === parseInt(branchId)).length;
        },

        availableFloors() {
            const rooms = this.filteredRooms();
            const floors = [...new Set(rooms.map(r => parseInt(r.floor)))];
            return floors.sort((a, b) => a - b);
        },

        roomsOnFloor(floor) {
            return this.filteredRooms().filter(r => parseInt(r.floor) === parseInt(floor));
        },

        floorRoomsSummary(floor) {
            const rooms = this.roomsOnFloor(floor);
            if (!rooms.length) return '';
            const nums = rooms.map(r => r.room_number);
            return nums[0] + ' - ' + nums[nums.length - 1];
        },

        totalBedsOnFloor(floor) {
            return this.roomsOnFloor(floor).reduce((acc, r) => acc + (r.total_beds || r.max_beds || 0), 0);
        },

        totalBranchBeds() {
            return this.filteredRooms().reduce((acc, r) => acc + (r.total_beds || r.max_beds || 0), 0);
        },

        totalOccupiedBeds() {
            return this.filteredRooms().reduce((acc, r) => acc + (r.occupied_beds || 0), 0);
        },

        totalAvailableBeds() {
            return this.filteredRooms().reduce((acc, r) => acc + (r.available_beds || 0), 0);
        },

        setBranch(branchId) {
            this.selectedBranchId = branchId;
            this.addFormBranchId = branchId;
            if (this.tabulatorTable) {
                this.tabulatorTable.setData(this.filteredRooms());
            }
        },

        openAddModal() {
            this.addFormBranchId = this.selectedBranchId || (this.allBranches[0] ? this.allBranches[0].id : '');
            this.addRoomModalOpen = true;
        },

        openEditModal(room) {
            this.editForm = {
                id: room.id,
                branch_id: room.branch_id,
                room_number: room.room_number,
                floor: room.floor,
                sharing_type: room.sharing_type,
                max_beds: room.max_beds || room.total_beds || 2,
                is_ac: room.is_ac ? 1 : 0,
                rent: room.rent || 0,
                deposit: room.deposit || 0
            };
            this.editRoomModalOpen = true;
        },

        openResidentModal(bed, room) {
            this.activeBed = bed;
            this.activeRoom = room;
            this.activeResident = bed.student || null;
            this.residentModalOpen = true;
        },

        openDeleteSelectionModal() {
            this.selectedDeleteRoomIds = [];
            this.deleteFloorFilter = 'all';
            this.deleteSearchQuery = '';
            this.deleteSelectionModalOpen = true;
        },

        deleteCandidateRooms() {
            let rooms = this.filteredRooms();
            if (this.deleteFloorFilter !== 'all') {
                rooms = rooms.filter(r => parseInt(r.floor) === parseInt(this.deleteFloorFilter));
            }
            if (this.deleteSearchQuery.trim()) {
                const q = this.deleteSearchQuery.trim().toLowerCase();
                rooms = rooms.filter(r => String(r.room_number).toLowerCase().includes(q));
            }
            return rooms;
        },

        isRoomSelectedForDelete(roomId) {
            return this.selectedDeleteRoomIds.includes(roomId);
        },

        toggleRoomDeleteSelection(room) {
            if (room.occupied_beds > 0) {
                toastr.warning('Room ' + room.room_number + ' cannot be deleted because it has active occupied residents.');
                return;
            }
            const idx = this.selectedDeleteRoomIds.indexOf(room.id);
            if (idx > -1) {
                this.selectedDeleteRoomIds.splice(idx, 1);
            } else {
                this.selectedDeleteRoomIds.push(room.id);
            }
        },

        selectAllVacantForDelete() {
            const vacantRooms = this.deleteCandidateRooms().filter(r => (r.occupied_beds || 0) === 0);
            this.selectedDeleteRoomIds = vacantRooms.map(r => r.id);
        },

        deselectAllDelete() {
            this.selectedDeleteRoomIds = [];
        },

        selectedDeleteRoomNumbers() {
            const selected = this.allRooms.filter(r => this.selectedDeleteRoomIds.includes(r.id));
            if (selected.length <= 5) {
                return selected.map(r => 'Room ' + r.room_number).join(', ');
            }
            return selected.slice(0, 5).map(r => 'Room ' + r.room_number).join(', ') + ' +' + (selected.length - 5) + ' more';
        },

        promptConfirmDelete() {
            if (this.selectedDeleteRoomIds.length === 0) {
                toastr.warning('Please select at least one room to delete.');
                return;
            }
            this.deleteConfirmModalOpen = true;
        },

        confirmSingleRoomDelete(room) {
            if (room.occupied_beds > 0) {
                toastr.warning('Room ' + room.room_number + ' cannot be deleted because it has active occupied residents.');
                return;
            }
            this.selectedDeleteRoomIds = [room.id];
            this.deleteConfirmModalOpen = true;
        },

        executeDeleteRooms() {
            if (this.selectedDeleteRoomIds.length === 0 || this.isDeleting) return;
            this.isDeleting = true;

            const isBulk = this.selectedDeleteRoomIds.length > 1;
            const url = isBulk 
                ? appUrl("super-admin/rooms-master/bulk-delete")
                : appUrl("super-admin/rooms-master/" + this.selectedDeleteRoomIds[0]);

            const method = isBulk ? "POST" : "DELETE";
            const headers = {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            };
            let body = null;
            if (isBulk) {
                headers["Content-Type"] = "application/json";
                body = JSON.stringify({ room_ids: this.selectedDeleteRoomIds });
            }

            fetch(url, { method, headers, body })
                .then(async res => {
                    let data = {};
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = { message: "Server returned status " + res.status };
                    }
                    if (!res.ok) {
                        throw new Error(data.message || 'Failed to delete room(s).');
                    }
                    return data;
                })
                .then(data => {
                    toastr.success(data.message || 'Room(s) deleted successfully.');
                    
                    const deleted = data.deleted_ids || (data.room_id ? [data.room_id] : this.selectedDeleteRoomIds);
                    this.allRooms = this.allRooms.filter(r => !deleted.includes(r.id));
                    
                    if (this.tabulatorTable) {
                        this.tabulatorTable.setData(this.filteredRooms());
                    }

                    this.deleteConfirmModalOpen = false;
                    this.deleteSelectionModalOpen = false;
                    this.selectedDeleteRoomIds = [];
                })
                .catch(err => {
                    toastr.error(err.message || 'Failed to delete room(s).');
                })
                .finally(() => {
                    this.isDeleting = false;
                });
        },

        initTabulator() {
            const self = this;
            this.tabulatorTable = new Tabulator("#rooms-table", {
                data: this.filteredRooms(),
                layout: "fitColumns",
                pagination: "local",
                paginationSize: 15,
                paginationSizeSelector: [15, 30, 50, 100],
                paginationCounter: "rows",
                placeholder: "No Rooms Found",
                columns: [
                    {title: "Room No", field: "room_number", minWidth: 110, formatter: function(cell){
                        return "<strong class='text-slate-900 dark:text-slate-100'>Room " + cell.getValue() + "</strong>";
                    }},
                    {title: "Branch", field: "branch_name", minWidth: 130},
                    {title: "Floor", field: "floor", minWidth: 110, formatter: function(cell){
                        return parseInt(cell.getValue()) === 0 
                            ? '<span class="font-bold text-blue-600 dark:text-blue-400">Ground Floor (0)</span>' 
                            : 'Floor ' + cell.getValue();
                    }},
                    {title: "Sharing Type", field: "sharing_type", minWidth: 130},
                    {title: "AC Status", field: "is_ac", minWidth: 100, formatter: function(cell){
                        return cell.getValue() 
                            ? '<span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-2.5 py-1 rounded-full">AC</span>' 
                            : '<span class="bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1 rounded-full">Non-AC</span>';
                    }},
                    {title: "Monthly Rent", field: "rent", minWidth: 110, formatter: function(cell){ return "₹" + Number(cell.getValue()).toLocaleString(); }},
                    {title: "Deposit", field: "deposit", minWidth: 110, formatter: function(cell){ return "₹" + Number(cell.getValue()).toLocaleString(); }},
                    {title: "Capacity / Occupancy", field: "occupied_beds", minWidth: 150, formatter: function(cell){
                        var total = cell.getRow().getData().total_beds;
                        return "<span class='font-bold'>" + cell.getValue() + " / " + total + "</span> beds";
                    }},
                    {title: "Status", field: "status", minWidth: 110, formatter: function(cell){
                        return cell.getValue() === "Full" 
                            ? '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">FULL</span>' 
                            : '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Available</span>';
                    }},
                    {title: "Actions", field: "id", minWidth: 160, formatter: function(cell){
                        return '<div class="flex items-center gap-1.5">' +
                               '<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-xs flex items-center gap-1 edit-room-btn cursor-pointer">' +
                               '<i class="fa-solid fa-pen-to-square"></i> Edit</button>' +
                               '<button class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-xs flex items-center gap-1 delete-room-btn cursor-pointer">' +
                               '<i class="fa-solid fa-trash-can"></i> Delete</button>' +
                               '</div>';
                    }, cellClick: function(e, cell){
                        var data = cell.getRow().getData();
                        if (e.target.classList.contains('edit-room-btn') || e.target.closest('.edit-room-btn')) {
                            self.openEditModal(data);
                        } else if (e.target.classList.contains('delete-room-btn') || e.target.closest('.delete-room-btn')) {
                            self.confirmSingleRoomDelete(data);
                        }
                    }},
                ]
            });
        }
    };
}

document.getElementById("create-room-form")?.addEventListener("submit", function(e){
    e.preventDefault();
    var formData = new FormData(this);

    fetch("{{ route('super_admin.rooms_master.store') }}", {
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
            toastr.success(data.message || "Room created successfully.");
            setTimeout(() => window.location.reload(), 800);
        } else {
            toastr.error(data.message || "Failed to create Room.");
        }
    })
    .catch(err => {
        toastr.error("An error occurred during submission.");
    });
});

document.getElementById("edit-room-form")?.addEventListener("submit", function(e){
    e.preventDefault();
    var formData = new FormData(this);
    var id = this.querySelector('input[name="id"]').value;

    fetch(appUrl("super-admin/rooms-master/" + id + "/update"), {
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
            toastr.success(data.message || "Room updated successfully.");
            setTimeout(() => window.location.reload(), 800);
        } else {
            toastr.error(data.message || "Failed to update room.");
        }
    })
    .catch(err => {
        toastr.error("An error occurred during submission.");
    });
});

function deleteRoom(id) {
    Swal.fire({
        title: 'Delete Room & Beds?',
        text: 'This will permanently delete this room and all associated beds.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        confirmButtonText: 'Yes, Delete Room'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(appUrl("super-admin/rooms-master/" + id), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    toastr.success(data.message || "Room deleted successfully.");
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    toastr.error(data.message || "Failed to delete room.");
                }
            })
            .catch(err => {
                toastr.error("An error occurred during deletion.");
            });
        }
    });
}
</script>
@endsection
