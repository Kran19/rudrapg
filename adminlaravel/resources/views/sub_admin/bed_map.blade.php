@extends('layouts.admin')

@section('title', 'Bed Allocation Grid - ' . (isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Rudra Group PG'))
@section('page_title', (isset($activeBranch) && $activeBranch ? $activeBranch->name : 'Branch') . ' Bed Allocation Map')

@section('content')
<div x-data="{
    residentModalOpen: false,
    selectedBed: null,
    viewResident(bed, roomNum) {
        this.selectedBed = { ...bed, room_number: roomNum };
        this.residentModalOpen = true;
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
                            <i class="fa-solid fa-layer-group mr-1.5"></i> FLOOR {{ $floor }} ({{ count($floorRooms) }} Rooms)
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
                                    <span class="bg-slate-900 dark:bg-slate-800 text-white text-[10px] font-semibold px-2 py-0.5 rounded">{{ $room['sharing_type'] }}</span>
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
                    <div class="w-10 h-10 rounded-2xl bg-blue-600/30 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base leading-tight">Resident Profile Details</h4>
                        <p class="text-xs text-slate-400" x-text="'Room ' + (selectedBed ? selectedBed.room_number : '') + ' • ' + (selectedBed ? selectedBed.code : '')"></p>
                    </div>
                </div>
                <button @click="residentModalOpen = false" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-4" x-if="selectedBed">
                <template x-if="selectedBed && selectedBed.student_name">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shrink-0">
                                <span x-text="selectedBed.student_name ? selectedBed.student_name.charAt(0).toUpperCase() : 'R'"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white truncate" x-text="selectedBed.student_name"></h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="selectedBed.student_phone || 'No Phone'"></p>
                            </div>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                ACTIVE
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 block text-[10px] font-semibold uppercase">Email</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 truncate block mt-0.5" x-text="selectedBed.student_email || 'N/A'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 block text-[10px] font-semibold uppercase">KYC Status</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 block mt-0.5" x-text="selectedBed.student_kyc || 'VERIFIED'"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 block text-[10px] font-semibold uppercase">Monthly Rent</span>
                                <span class="font-bold text-slate-900 dark:text-white block mt-0.5" x-text="'₹' + Number(selectedBed.monthly_rent || 0).toLocaleString()"></span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400 block text-[10px] font-semibold uppercase">Security Deposit</span>
                                <span class="font-bold text-slate-900 dark:text-white block mt-0.5" x-text="'₹' + Number(selectedBed.security_deposit || 0).toLocaleString()"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="selectedBed && !selectedBed.student_name">
                    <div class="p-6 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-center">
                        <i class="fa-solid fa-user-lock text-3xl text-amber-500 mb-2"></i>
                        <h4 class="font-bold text-sm text-amber-900 dark:text-amber-200" x-text="'Occupied Bed: ' + selectedBed.code"></h4>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">This bed is marked as OCCUPIED in the inventory.</p>
                        <div class="mt-3 inline-block px-3 py-1 bg-amber-100 dark:bg-amber-800/40 text-amber-900 dark:text-amber-100 text-xs font-semibold rounded-lg">
                            Monthly Rent: ₹<span x-text="Number(selectedBed.monthly_rent || 0).toLocaleString()"></span> • Deposit: ₹<span x-text="Number(selectedBed.security_deposit || 0).toLocaleString()"></span>
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
</div>
@endsection
