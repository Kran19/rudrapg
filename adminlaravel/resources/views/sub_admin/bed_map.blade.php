@extends('layouts.admin')

@section('title', 'Bed Allocation Grid - Rudra Group PG')
@section('page_title', 'Naroda Branch Bed Allocation Map')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-bold text-slate-900">Visual Bed Allocation & Room Control</h3>
        <p class="text-xs text-slate-500">Interactive 2D room matrix map for managing bed status, transfers, and maintenance.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-6">
    <div class="flex flex-wrap items-center justify-around text-center text-xs font-semibold gap-2">
        <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-emerald-500 rounded-md inline-block"></span> Available Bed</div>
        <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-rose-500 rounded-md inline-block"></span> Occupied Bed</div>
        <div class="flex items-center gap-2 text-slate-900 dark:text-slate-100"><span class="w-3.5 h-3.5 bg-amber-500 rounded-md inline-block"></span> Reserved Bed</div>
    </div>
</div>

<div class="space-y-6 mb-8">
    @for($floor = 1; $floor <= 4; $floor++)
        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-4">FLOOR {{ $floor }} ROOMS</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
                @foreach(collect($rooms)->where('floor', $floor) as $room)
                    <div class="p-3.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-sm text-slate-900 dark:text-slate-100">Room {{ $room['room_number'] }}</span>
                            <span class="bg-slate-900 text-white text-[10px] font-semibold px-2 py-0.5 rounded">{{ $room['sharing_type'] }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 my-2">
                            @foreach($room['beds'] as $bed)
                                @if($bed['status'] == 'occupied')
                                    <button onclick="toastr.info('Occupied by {{ $bed['student_name'] }}')" 
                                            class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold py-1 px-2.5 rounded-lg flex-1 text-left shadow-xs transition-colors">
                                        <i class="fa-solid fa-user me-1 text-[10px]"></i> {{ $bed['code'] }}
                                    </button>
                                @elseif($bed['status'] == 'reserved')
                                    <button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold py-1 px-2.5 rounded-lg flex-1 text-left shadow-xs transition-colors">
                                        <i class="fa-solid fa-clock me-1 text-[10px]"></i> {{ $bed['code'] }}
                                    </button>
                                @else
                                    <button onclick="toastr.success('Bed {{ $bed['code'] }} is available!')" 
                                            class="border border-emerald-600 text-emerald-600 hover:bg-emerald-50 text-xs font-semibold py-1 px-2.5 rounded-lg flex-1 text-left transition-colors">
                                        <i class="fa-regular fa-circle-check me-1 text-[10px]"></i> {{ $bed['code'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endfor
</div>
@endsection
