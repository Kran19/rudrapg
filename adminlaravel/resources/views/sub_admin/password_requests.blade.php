@extends('layouts.admin')

@section('title', 'Student Password Requests - Rudra Group PG')
@section('page_title', 'Student Password Requests & Reset Desk')

@section('content')
<div x-data="subAdminPasswordDesk()" 
     @open-reset-modal.window="selectedReq = $event.detail; newPassword = ''; confirmPassword = ''; resetModalOpen = true;"
     class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-user-lock text-blue-600"></i> Resident Password Reset Requests
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Residents without email access can request password assistance. Set secure credentials and share with residents directly.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="directModalOpen = true; newPassword = ''; confirmPassword = '';" 
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition-all flex items-center gap-2">
                <i class="fa-solid fa-key"></i> Direct Set Password
            </button>
        </div>
    </div>

    <!-- Quick Stat KPI Pills -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Pending Requests</p>
                <p class="text-xl font-bold text-amber-500 mt-1">{{ $requests->where('status', 'PENDING')->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-lg">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Resolved Requests</p>
                <p class="text-xl font-bold text-emerald-500 mt-1">{{ $requests->where('status', 'RESOLVED')->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-lg">
                <i class="fa-solid fa-check-circle"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-between shadow-xs">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Branch Active Residents</p>
                <p class="text-xl font-bold text-blue-600 mt-1">{{ $branchStudents->count() }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Tabulator Table Container -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-x-auto">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-600"></i> Password Assistance Queue
            </h4>
            <div class="flex items-center gap-2">
                <input type="text" id="pw-search" 
                       class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm w-48 sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100" 
                       placeholder="🔍 Search name / mobile...">
            </div>
        </div>
        <div id="password-requests-table"></div>
    </div>

    <!-- Modal 1: Reset Password via Request -->
    <div x-show="resetModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div @click.away="resetModalOpen = false" 
             x-show="resetModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 relative">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-key text-blue-600"></i> Set New Password
                </h3>
                <button @click="resetModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitRequestReset()" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3.5 rounded-xl border border-blue-200 dark:border-blue-800/40 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                    <p><span class="font-semibold text-slate-900 dark:text-slate-100">Resident:</span> <span x-text="selectedReq.student_name"></span></p>
                    <p><span class="font-semibold text-slate-900 dark:text-slate-100">Mobile:</span> <span x-text="selectedReq.phone"></span></p>
                    <p><span class="font-semibold text-slate-900 dark:text-slate-100">Room / Bed:</span> <span x-text="selectedReq.room"></span></p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">New Password</label>
                        <button type="button" @click="generatePassword()" class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="new_password" x-model="newPassword" required minlength="6"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono"
                               placeholder="Minimum 6 characters (e.g. Rudra@2026)">
                        <button type="button" x-show="newPassword" @click="copyToClipboard(newPassword)" 
                                class="absolute right-2.5 top-2.5 text-xs text-slate-400 hover:text-blue-600 px-1.5 py-0.5 rounded">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">This will immediately replace the resident's password and terminate their active sessions.</p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="resetModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="button" @click="submitRequestReset()" :disabled="isSubmitting"
                            class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-md flex items-center gap-1.5 transition-all">
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-check"></i> Save & Activate Password</span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-notch fa-spin"></i> Activating Password...</span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Direct Reset for Any Student -->
    <div x-show="directModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div @click.away="directModalOpen = false" 
             x-show="directModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 relative">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-blue-600"></i> Direct Student Password Reset
                </h3>
                <button @click="directModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitDirectReset()" id="direct-reset-form" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Select Resident</label>
                    <select id="direct-student-select" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100">
                        <option value="">-- Choose Resident --</option>
                        @foreach($branchStudents as $st)
                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->phone }}) - Room: {{ $st->room->room_number ?? 'N/A' }} / Bed: {{ $st->bed->bed_code ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">New Password</label>
                        <button type="button" @click="generatePassword()" class="text-xs text-blue-600 hover:underline font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="new_password" x-model="newPassword" required minlength="6"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-slate-900 dark:text-slate-100 font-mono"
                               placeholder="Enter new password (min 6 chars)">
                        <button type="button" x-show="newPassword" @click="copyToClipboard(newPassword)" 
                                class="absolute right-2.5 top-2.5 text-xs text-slate-400 hover:text-blue-600 px-1.5 py-0.5 rounded">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="directModalOpen = false" class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancel</button>
                    <button type="button" @click="submitDirectReset()" :disabled="isSubmitting"
                            class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-md flex items-center gap-1.5 transition-all">
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-key"></i> Update Password</span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-notch fa-spin"></i> Updating Password...</span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function subAdminPasswordDesk() {
        return {
            resetModalOpen: false, 
            directModalOpen: false,
            selectedReq: { id: '', student_name: '', phone: '', room: '', branch: '' },
            selectedStudentId: '',
            newPassword: '',
            confirmPassword: '',
            copied: false,
            isSubmitting: false,
            generatePassword() {
                const words = ['Rudra', 'Stay', 'Prime', 'Smart', 'Living'];
                const word = words[Math.floor(Math.random() * words.length)];
                const num = Math.floor(1000 + Math.random() * 9000);
                this.newPassword = `${word}@${num}`;
                this.confirmPassword = this.newPassword;
            },
            copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
                toastr.success('Password copied to clipboard!');
            },
            submitRequestReset() {
                if (!this.newPassword || this.newPassword.length < 6) {
                    toastr.warning('Password must be at least 6 characters.');
                    return;
                }
                this.isSubmitting = true;
                const targetUrl = '{{ url('/sub-admin/password-requests') }}/' + this.selectedReq.id + '/reset';
                const residentName = this.selectedReq.student_name;
                const residentPhone = this.selectedReq.phone;
                const pwd = this.newPassword;

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ new_password: pwd })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Failed to update password.');
                    return data;
                })
                .then(data => {
                    this.resetModalOpen = false;
                    toastr.success('Password updated successfully!');
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated!',
                        html: `
                            <div class="text-left bg-slate-50 dark:bg-slate-700/60 p-4 rounded-xl border border-slate-200 dark:border-slate-600 mt-3 space-y-2">
                                <div class="text-sm"><span class="font-bold text-slate-700 dark:text-slate-300">Resident:</span> <span class="font-semibold text-slate-900 dark:text-white">${residentName}</span></div>
                                <div class="text-sm"><span class="font-bold text-slate-700 dark:text-slate-300">Mobile:</span> <span class="font-semibold text-slate-900 dark:text-white">${residentPhone}</span></div>
                                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                                    <div class="text-xs font-bold text-slate-500 uppercase mb-1">New Active Password:</div>
                                    <div class="flex items-center justify-between bg-white dark:bg-slate-900 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 font-mono text-base font-bold text-blue-600 dark:text-blue-400">
                                        <span>${pwd}</span>
                                        <button type="button" onclick="window.copyPasswordText('${pwd}');" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-2.5 py-1 rounded-md font-sans font-semibold flex items-center gap-1">
                                            <i class="fa-regular fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-3">Share this password with the resident so they can sign in immediately.</p>
                        `,
                        confirmButtonText: 'Done',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    toastr.error(err.message || 'An error occurred.');
                    Swal.fire('Error', err.message || 'An error occurred.', 'error');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
            },
            submitDirectReset() {
                const select = document.getElementById('direct-student-select');
                const studentId = select ? select.value : '';
                if (!studentId) {
                    toastr.warning('Please choose a resident first.');
                    return;
                }
                if (!this.newPassword || this.newPassword.length < 6) {
                    toastr.warning('Password must be at least 6 characters.');
                    return;
                }
                this.isSubmitting = true;
                const residentLabel = select.options[select.selectedIndex]?.text || 'Resident';
                const targetUrl = '{{ url('/sub-admin/students') }}/' + studentId + '/reset-password';
                const pwd = this.newPassword;

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ new_password: pwd })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Failed to update password.');
                    return data;
                })
                .then(data => {
                    this.directModalOpen = false;
                    toastr.success('Password updated successfully!');
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated!',
                        html: `
                            <div class="text-left bg-slate-50 dark:bg-slate-700/60 p-4 rounded-xl border border-slate-200 dark:border-slate-600 mt-3 space-y-2">
                                <div class="text-sm"><span class="font-bold text-slate-700 dark:text-slate-300">Resident:</span> <span class="font-semibold text-slate-900 dark:text-white">${residentLabel}</span></div>
                                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                                    <div class="text-xs font-bold text-slate-500 uppercase mb-1">New Active Password:</div>
                                    <div class="flex items-center justify-between bg-white dark:bg-slate-900 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 font-mono text-base font-bold text-blue-600 dark:text-blue-400">
                                        <span>${pwd}</span>
                                        <button type="button" onclick="window.copyPasswordText('${pwd}');" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-2.5 py-1 rounded-md font-sans font-semibold flex items-center gap-1">
                                            <i class="fa-regular fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-3">Share this password with the resident so they can sign in immediately.</p>
                        `,
                        confirmButtonText: 'Done',
                        confirmButtonColor: '#2563eb'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(err => {
                    toastr.error(err.message || 'An error occurred.');
                    Swal.fire('Error', err.message || 'An error occurred.', 'error');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
            }
        };
    }

    const requestsData = [
        @foreach($requests as $req)
        {
            id: {{ $req->id }},
            student_name: "{{ addslashes($req->student ? $req->student->full_name : ($req->user ? $req->user->name : 'Resident')) }}",
            phone: "{{ $req->phone }}",
            room: "{{ addslashes(($req->student && $req->student->room ? $req->student->room->room_number : 'N/A') . ' (' . ($req->student && $req->student->bed ? $req->student->bed->bed_code : 'N/A') . ')') }}",
            branch: "{{ addslashes($req->branch ? $req->branch->name : 'N/A') }}",
            notes: "{{ addslashes($req->notes ?? 'Password reset assistance requested') }}",
            status: "{{ $req->status }}",
            requested_at: "{{ $req->created_at ? $req->created_at->format('d M Y, h:i A') : 'N/A' }}",
            resolved_at: "{{ $req->resolved_at ? $req->resolved_at->format('d M Y, h:i A') : '—' }}",
            resolver: "{{ addslashes($req->resolver ? $req->resolver->name : '—') }}"
        },
        @endforeach
    ];

    const table = new Tabulator("#password-requests-table", {
        data: requestsData,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        pagination: "local",
        paginationSize: 10,
        placeholder: "No password reset requests logged for this branch.",
        columns: [
            { title: "ID", field: "id", width: 60, headerHozAlign: "center", hozAlign: "center" },
            { 
                title: "Resident", 
                field: "student_name", 
                formatter: function(cell) {
                    const row = cell.getRow().getData();
                    return `<div>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">${cell.getValue()}</div>
                                <div class="text-xs text-slate-500 font-mono">${row.phone}</div>
                            </div>`;
                }
            },
            { title: "Room & Bed", field: "room", width: 140 },
            { title: "Requested At", field: "requested_at", width: 160 },
            { 
                title: "Status", 
                field: "status", 
                width: 120,
                formatter: function(cell) {
                    const val = cell.getValue();
                    if (val === 'PENDING') {
                        return '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/30">PENDING</span>';
                    }
                    return '<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/30">RESOLVED</span>';
                }
            },
            { title: "Resolved By", field: "resolver", width: 130 },
            {
                title: "Actions",
                headerHozAlign: "center",
                hozAlign: "center",
                width: 140,
                formatter: function(cell) {
                    const row = cell.getRow().getData();
                    const isPending = row.status === 'PENDING';
                    const btnClass = isPending 
                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs' 
                        : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200';
                    const label = isPending ? 'Set Password' : 'Change Again';
                    const icon = isPending ? 'fa-key' : 'fa-rotate-right';

                    return `<button type="button" class="btn-open-reset px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all ${btnClass}">
                                <i class="fa-solid ${icon}"></i> ${label}
                            </button>`;
                },
                cellClick: function(e, cell) {
                    if (e.target.closest('.btn-open-reset')) {
                        const data = cell.getRow().getData();
                        window.dispatchEvent(new CustomEvent('open-reset-modal', { detail: data }));
                    }
                }
            }
        ]
    });

    document.getElementById('pw-search')?.addEventListener('input', function(e) {
        const val = e.target.value.toLowerCase();
        table.setFilter(function(data) {
            return (data.student_name && data.student_name.toLowerCase().includes(val)) ||
                   (data.phone && data.phone.includes(val)) ||
                   (data.room && data.room.toLowerCase().includes(val));
        });
    });
</script>
@endsection
