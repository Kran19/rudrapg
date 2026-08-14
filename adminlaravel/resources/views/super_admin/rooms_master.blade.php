@extends('layouts.admin')

@section('title', 'Master Room Matrix - Rudra Group PG')
@section('page_title', 'Master Room Matrix (40 Rooms)')

@section('content')
<div x-data="{ addRoomModalOpen: false, editRoomModalOpen: false, viewMode: 'table', editForm: { id: '', branch_id: '', room_number: '', floor: '', sharing_type: '', max_beds: '', is_ac: false, rent: 0, deposit: 0 } }"
     @open-edit-room-modal.window="editForm = { ...$event.detail }; editRoomModalOpen = true"
     @close-edit-room-modal.window="editRoomModalOpen = false">
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
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-slate-900 dark:bg-slate-950 text-white font-bold text-xs px-3.5 py-1.5 rounded-full flex items-center gap-2">
                        🏢 FLOOR {{ $floor }} (Rooms {{ $floor }}01 - {{ $floor }}10)
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">10 Rooms • 25 Beds</span>
                </div>

                <!-- 5 Rooms Per Row Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                    @foreach(collect($rooms)->where('floor', $floor) as $room)
                        <div class="p-3.5 rounded-xl border {{ $room['status'] == 'Full' ? 'border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-900/10' : 'border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-900/10' }} transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-slate-100">Room {{ $room['room_number'] }}</span>
                                @if($room['is_ac'])
                                    <i class="fa-solid fa-snowflake text-cyan-500 dark:text-cyan-400 text-xs" title="AC Room"></i>
                                @else
                                    <i class="fa-solid fa-wind text-slate-400 dark:text-slate-500 text-xs" title="Non-AC"></i>
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

                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/60 mt-2">
                                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ $room['sharing_type'] }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $room['status'] == 'Full' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' }}">
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
    <div x-show="viewMode === 'table'" class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs mb-8 overflow-x-auto">
        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 mb-4">Tabulator Room Inventory Table</h4>
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
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-md overflow-hidden transform transition-all">
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
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">PG Branch</label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        @foreach($allBranches as $b)
                            <option value="{{ $b->id }}" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Room Number</label>
                        <input type="text" name="room_number" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="e.g. 501">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Floor Number</label>
                        <select name="floor_number" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="1" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Floor 1</option>
                            <option value="2" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Floor 2</option>
                            <option value="3" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Floor 3</option>
                            <option value="4" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Floor 4</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Sharing Type</label>
                        <select name="sharing_type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="2 Sharing AC" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">2 Sharing AC</option>
                            <option value="3 Sharing AC" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">3 Sharing AC</option>
                            <option value="4 Sharing AC" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">4 Sharing AC</option>
                            <option value="Private Room" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">Private Room</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Total Beds</label>
                        <input type="number" name="max_beds" min="1" max="6" value="2" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Monthly Rent (₹)</label>
                        <input type="number" name="rent" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="Enter monthly rent">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Security Deposit (₹)</label>
                        <input type="number" name="deposit" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100" placeholder="Enter security deposit">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_ac" value="0">
                    <input type="checkbox" name="is_ac" value="1" checked id="acChk" class="rounded border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-blue-600 focus:ring-blue-500">
                    <label for="acChk" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Air Conditioned (AC Room)</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="addRoomModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Room</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pure Tailwind Modal: Edit Room -->
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
             class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
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
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Floor Number</label>
                        <input type="number" name="floor_number" required x-model="editForm.floor" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Sharing Type</label>
                        <select name="sharing_type" required x-model="editForm.sharing_type" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option value="Single Sharing">Single Sharing</option>
                            <option value="2-Sharing">2-Sharing</option>
                            <option value="3-Sharing">3-Sharing</option>
                            <option value="4-Sharing">4-Sharing</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Beds (Read Only)</label>
                        <input type="number" readonly x-model="editForm.max_beds" class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-xl text-sm cursor-not-allowed">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">AC Status</label>
                        <select name="is_ac" required x-model="editForm.is_ac" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                            <option :value="1">AC</option>
                            <option :value="0">Non-AC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Monthly Rent (₹)</label>
                        <input type="number" name="rent" required x-model="editForm.rent" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Security Deposit (₹)</label>
                        <input type="number" name="deposit" required x-model="editForm.deposit" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editRoomModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md">Save Changes</button>
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
        pagination: "local",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",

        placeholder: "No Rooms Found",
        columns: [
            {title: "Room No", field: "room_number", minWidth: 110, formatter: function(cell){
                return "<strong class='text-slate-900 dark:text-slate-100'>Room " + cell.getValue() + "</strong>";
            }},
            {title: "Floor", field: "floor", minWidth: 90},
            {title: "Sharing Type", field: "sharing_type", minWidth: 140},
            {title: "AC Status", field: "is_ac", minWidth: 110, formatter: function(cell){
                return cell.getValue() ? '<span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-2.5 py-1 rounded-full">AC</span>' : '<span class="bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-1 rounded-full">Non-AC</span>';
            }},
            {title: "Monthly Rent", field: "rent", minWidth: 120, formatter: function(cell){ return "₹" + cell.getValue(); }},
            {title: "Deposit", field: "deposit", minWidth: 120, formatter: function(cell){ return "₹" + cell.getValue(); }},
            {title: "Occupancy", field: "occupied_beds", minWidth: 130, formatter: function(cell, row){
                var total = cell.getRow().getData().total_beds;
                return cell.getValue() + " / " + total + " beds";
            }},
            {title: "Status", field: "status", minWidth: 110, formatter: function(cell){
                return cell.getValue() == "Full" ? '<span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">FULL</span>' : '<span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Available</span>';
            }},
            {title: "Actions", field: "id", minWidth: 120, formatter: function(cell){
                return '<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-sm flex items-center gap-1 edit-room-btn"><i class="fa-solid fa-pen-to-square"></i> Edit</button>';
            }, cellClick: function(e, cell){
                var data = cell.getRow().getData();
                if (e.target.classList.contains('edit-room-btn') || e.target.closest('.edit-room-btn')) {
                    window.dispatchEvent(new CustomEvent('open-edit-room-modal', { detail: data }));
                }
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

    document.getElementById("edit-room-form").addEventListener("submit", function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var id = this.querySelector('input[name="id"]').value;

        fetch("/super-admin/rooms-master/" + id + "/update", {
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
                window.dispatchEvent(new CustomEvent('close-edit-room-modal'));
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "Failed to update room.");
            }
        })
        .catch(err => {
            toastr.error("An error occurred during submission.");
        });
    });
</script>
@endsection
