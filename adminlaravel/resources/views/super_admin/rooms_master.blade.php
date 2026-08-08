@extends('layouts.admin')

@section('title', 'Master Room Matrix - Rudra Group PG')
@section('page_title', 'Master Room Matrix (40 Rooms)')

@section('content')
<div x-data="{ viewMode: 'grid', addRoomModalOpen: false }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Naroda Branch Master Room Matrix</h3>
            <p class="text-xs text-slate-500">Overview of all 40 rooms and 100 beds across 4 floors with real-time occupancy indicators.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="viewMode = viewMode === 'grid' ? 'table' : 'grid'" 
                    class="border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid" :class="viewMode === 'grid' ? 'fa-table-list' : 'fa-border-all'"></i>
                <span x-text="viewMode === 'grid' ? 'Switch to Tabulator Table' : 'Switch to 40-Room Visual Grid'"></span>
            </button>
            <button @click="addRoomModalOpen = true" 
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add New Room
            </button>
        </div>
    </div>

    <!-- 40-Room Visual Grid View (5 Rooms Per Row across 4 Floors) -->
    <div x-show="viewMode === 'grid'" class="space-y-6 mb-8">
        @for($floor = 1; $floor <= 4; $floor++)
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-slate-900 text-white font-bold text-xs px-3.5 py-1.5 rounded-full flex items-center gap-2">
                        🏢 FLOOR {{ $floor }} (Rooms {{ $floor }}01 - {{ $floor }}10)
                    </span>
                    <span class="text-xs text-slate-500 font-medium">10 Rooms • 25 Beds</span>
                </div>

                <!-- 5 Rooms Per Row Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                    @foreach(collect($rooms)->where('floor', $floor) as $room)
                        <div class="p-3.5 rounded-xl border {{ $room['status'] == 'Full' ? 'border-rose-200 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/40' }} transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-slate-900">Room {{ $room['room_number'] }}</span>
                                @if($room['is_ac'])
                                    <i class="fa-solid fa-snowflake text-cyan-500 text-xs" title="AC Room"></i>
                                @else
                                    <i class="fa-solid fa-wind text-slate-400 text-xs" title="Non-AC"></i>
                                @endif
                            </div>

                            <!-- People Symbols Row -->
                            <div class="flex items-center justify-center gap-1.5 my-2.5">
                                @for($i = 0; $i < $room['total_beds']; $i++)
                                    @if($i < $room['occupied_beds'])
                                        <i class="fa-solid fa-user text-rose-600 text-sm" title="Occupied Bed"></i>
                                    @else
                                        <i class="fa-regular fa-user text-emerald-600 text-sm" title="Available Bed"></i>
                                    @endif
                                @endfor
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 mt-2">
                                <span class="text-[10px] font-medium text-slate-500">{{ $room['sharing_type'] }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $room['status'] == 'Full' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $room['status'] == 'Full' ? 'FULL' : $room['available_beds'] . ' Left' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endfor
    </div>

    <!-- Tabulator Table View -->
    <div x-show="viewMode === 'table'" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs mb-8 overflow-x-auto">
        <h4 class="font-bold text-sm text-slate-900 mb-4">Tabulator Room Inventory Table</h4>
        <div id="rooms-table"></div>
    </div>

    <!-- Add Room Modal -->
    <div x-show="addRoomModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        
        <div @click.away="addRoomModalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">PG Branch</label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($allBranches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Room Number</label>
                        <input type="text" name="room_number" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g. 501">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Floor Number</label>
                        <select name="floor_number" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="1">Floor 1</option>
                            <option value="2">Floor 2</option>
                            <option value="3">Floor 3</option>
                            <option value="4">Floor 4</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sharing Type</label>
                        <select name="sharing_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="2 Sharing AC">2 Sharing AC</option>
                            <option value="3 Sharing AC">3 Sharing AC</option>
                            <option value="4 Sharing AC">4 Sharing AC</option>
                            <option value="Private Room">Private Room</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Total Beds</label>
                        <input type="number" name="max_beds" min="1" max="6" value="2" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monthly Rent (₹)</label>
                        <input type="number" name="rent" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter monthly rent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Security Deposit (₹)</label>
                        <input type="number" name="deposit" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter security deposit">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_ac" value="0">
                    <input type="checkbox" name="is_ac" value="1" checked id="acChk" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="acChk" class="text-xs font-semibold text-slate-700">Air Conditioned (AC Room)</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="addRoomModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var roomsData = @json($rooms);

    var table = new Tabulator("#rooms-table", {
        data: roomsData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No Rooms Found",
        columns: [
            {title: "Room No", field: "room_number", width: 100, formatter: function(cell){
                return "<strong class='text-slate-900'>Room " + cell.getValue() + "</strong>";
            }},
            {title: "Floor", field: "floor", width: 90},
            {title: "Sharing Type", field: "sharing_type"},
            {title: "AC Status", field: "is_ac", formatter: function(cell){
                return cell.getValue() ? '<span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-2.5 py-1 rounded-full">AC</span>' : '<span class="bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1 rounded-full">Non-AC</span>';
            }},
            {title: "Monthly Rent", field: "rent", formatter: function(cell){ return "₹" + cell.getValue(); }},
            {title: "Deposit", field: "deposit", formatter: function(cell){ return "₹" + cell.getValue(); }},
            {title: "Occupancy", field: "occupied_beds", formatter: function(cell, row){
                var total = cell.getRow().getData().total_beds;
                return cell.getValue() + " / " + total + " beds";
            }},
            {title: "Status", field: "status", formatter: function(cell){
                return cell.getValue() == "Full" ? '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">FULL</span>' : '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Available</span>';
            }},
        ]
    });

    document.getElementById("create-room-form").addEventListener("submit", function(e){
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
                toastr.success(data.message);
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to create Room.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });
</script>
@endsection
