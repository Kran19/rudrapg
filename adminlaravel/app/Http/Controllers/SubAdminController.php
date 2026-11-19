<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\ElectricityReading;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\RegistrationRequest;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SubAdminController extends Controller
{
    protected function getActiveBranch(?Request $request = null): ?Branch
    {
        $user = Auth::user();
        if (!$user) {
            return Branch::first();
        }

        // If Super Admin, allow query parameter branch_id or default to first branch
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $reqBranchId = request()->query('branch_id');
            if ($reqBranchId) {
                $b = Branch::find($reqBranchId);
                if ($b) {
                    return $b;
                }
            }
            return Branch::first();
        }

        // For Sub Admin: prioritize their assigned branch from relationship
        if (method_exists($user, 'branches') && $user->branches()->exists()) {
            return $user->branches()->first();
        }

        // Direct DB query on pivot
        $pivot = DB::table('sub_admin_branch')->where('user_id', $user->id)->first();
        if ($pivot) {
            $branch = Branch::find($pivot->branch_id);
            if ($branch) {
                return $branch;
            }
        }

        // Heuristic match: match user email or name with branch name/code
        if ($user->email || $user->name) {
            $prefix = explode('@', $user->email)[0];
            $matched = Branch::where('name', 'LIKE', "%{$prefix}%")
                ->orWhere('code', 'LIKE', "%{$prefix}%")
                ->orWhere('name', 'LIKE', "%{$user->name}%")
                ->first();
            if ($matched) {
                DB::table('sub_admin_branch')->insertOrIgnore([
                    'user_id' => $user->id,
                    'branch_id' => $matched->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return $matched;
            }
        }

        return Branch::first();
    }

    protected function getAuthorizedBranchIds(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return Branch::pluck('id')->toArray();
        }

        $activeBranch = $this->getActiveBranch();
        return $activeBranch ? [$activeBranch->id] : [];
    }

    protected function formatStorageUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }
        return null;
    }

    public function dashboard()
    {
        $branch = $this->getActiveBranch();
        $branchId = $branch ? $branch->id : null;
        $branchIds = $branchId ? [$branchId] : $this->getAuthorizedBranchIds();

        $branchInfo = [
            'name' => $branch ? $branch->name : 'Branch Operations',
            'code' => $branch ? $branch->code : 'PG-01',
            'manager' => $branch ? ($branch->manager_name ?: 'N/A') : 'N/A',
            'total_rooms' => $branchId ? Room::where('branch_id', $branchId)->count() : Room::whereIn('branch_id', $branchIds)->count(),
            'total_beds' => Bed::whereHas('room', function($q) use ($branchId, $branchIds) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    $q->whereIn('branch_id', $branchIds);
                }
            })->count(),
            'occupied_beds' => Bed::where('status', 'OCCUPIED')->whereHas('room', function($q) use ($branchId, $branchIds) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    $q->whereIn('branch_id', $branchIds);
                }
            })->count(),
            'available_beds' => Bed::where('status', 'AVAILABLE')->whereHas('room', function($q) use ($branchId, $branchIds) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    $q->whereIn('branch_id', $branchIds);
                }
            })->count(),
            'pending_verifications' => RegistrationRequest::whereIn('status', ['PENDING', 'pending'])
                ->where(function($q) use ($branchId, $branchIds) {
                    if ($branchId) $q->where('branch_id', $branchId);
                    else $q->whereIn('branch_id', $branchIds);
                })->count()
                + PaymentProof::whereIn('status', ['PENDING', 'pending'])->whereHas('payment', function($q) use ($branchId, $branchIds) {
                    if ($branchId) $q->where('branch_id', $branchId);
                    else $q->whereIn('branch_id', $branchIds);
                })->count(),
            'overdue_rents' => Student::where('rent_status', 'DUE')
                ->where(function($q) use ($branchId, $branchIds) {
                    if ($branchId) $q->where('branch_id', $branchId);
                    else $q->whereIn('branch_id', $branchIds);
                })->count(),
            'open_complaints' => Complaint::whereIn('status', ['PENDING', 'IN_PROGRESS'])
                ->where(function($q) use ($branchId, $branchIds) {
                    if ($branchId) $q->where('branch_id', $branchId);
                    else $q->whereIn('branch_id', $branchIds);
                })->count(),
        ];

        // Add calculated stats
        $totalBeds = $branchInfo['total_beds'];
        $branchInfo['occupancy_rate'] = $totalBeds > 0 ? round(($branchInfo['occupied_beds'] / $totalBeds) * 100, 1) . '%' : '0%';
        
        $monthlyRevenue = Payment::where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else $q->whereIn('branch_id', $branchIds);
            })
            ->whereIn('status', ['PAID', 'VERIFIED'])
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        
        $branchInfo['monthly_revenue'] = '₹' . number_format($monthlyRevenue);

        // Historical collections performance (Last 6 Months)
        $collectionsTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $monthVal = $date->month;
            $yearVal = $date->year;

            $collected = Payment::where(function($q) use ($branchId, $branchIds) {
                    if ($branchId) $q->where('branch_id', $branchId);
                    else $q->whereIn('branch_id', $branchIds);
                })
                ->whereIn('status', ['PAID', 'VERIFIED'])
                ->whereMonth('payment_date', $monthVal)
                ->whereYear('payment_date', $yearVal)
                ->sum('amount');
            
            // Set a benchmark target (beds capacity * average rent)
            $target = $totalBeds > 0 ? $totalBeds * 6000 * 0.75 : 0;

            $collectionsTrend[] = [
                'month' => $monthName,
                'target' => round($target),
                'collected' => round($collected)
            ];
        }

        $pendingVerifications = RegistrationRequest::with(['student.documents', 'student.room', 'student.bed'])
            ->where('status', 'PENDING')
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $student = $req->student;
                return [
                    'id' => $req->app_reference,
                    'student_name' => $student ? $student->full_name : 'Applicant',
                    'phone' => $student ? $student->phone : 'N/A',
                    'room' => ($student && $student->room ? 'Room '.$student->room->room_number : 'Unassigned').($student && $student->bed ? ' ('.$student->bed->bed_code.')' : ''),
                    'rent' => $student && $student->bed ? '₹'.number_format($student->bed->monthly_rent) : 'N/A',
                    'deposit' => $student && $student->bed ? '₹'.number_format($student->bed->security_deposit) : 'N/A',
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                ];
            });

        // Rooms for Room Electricity Splitter & Operations
        $rooms = Room::with(['branch'])
            ->withCount(['beds', 'students' => function($q) {
                $q->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED']);
            }])
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->orderBy('room_number')
            ->get();

        return view('sub_admin.dashboard', compact('branchInfo', 'pendingVerifications', 'collectionsTrend', 'branch', 'rooms'));
    }

    public function verifications()
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $queue = RegistrationRequest::with(['student.documents', 'student.payments.proof', 'student.room', 'student.bed', 'branch'])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest()
            ->get()
            ->map(function ($req) {
                $student = $req->student;

                $profilePhotoDoc = $student ? $student->documents->firstWhere('doc_type', 'PROFILE_PHOTO') : null;
                $aadhaarFrontDoc = $student ? $student->documents->firstWhere('doc_type', 'AADHAAR_FRONT') : null;
                $aadhaarBackDoc = $student ? $student->documents->firstWhere('doc_type', 'AADHAAR_BACK') : null;
                $panCardDoc = $student ? $student->documents->firstWhere('doc_type', 'PAN_CARD') : null;
                $meterPhotoDoc = $student ? $student->documents->firstWhere('doc_type', 'INITIAL_METER_PHOTO') : null;
                
                $latestPayment = $student ? $student->payments->first() : null;
                $paymentProof = $latestPayment ? $latestPayment->proof : null;

                $formatUrl = function (?string $path) {
                    if (!$path) return null;
                    if (str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) return null;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
                    $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                        return asset('storage/' . $cleanPath);
                    }
                    return null;
                };

                $isKycApproved = $student && ($student->kyc_status === 'APPROVED' || $student->status === 'KYC_APPROVED' || $student->status === 'BED_ALLOCATED' || $student->status === 'APPROVED');
                $isBedAssigned = $student && !is_null($student->bed_id);
                $isPaymentSubmitted = ($paymentProof !== null) || ($latestPayment && $latestPayment->status === 'PENDING') || ($student && $student->rent_status === 'UNDER_VERIFICATION');
                $isPaymentDone = ($latestPayment && ($latestPayment->status === 'VERIFIED' || $latestPayment->status === 'PAID')) || ($student && $student->rent_status === 'PAID');

                return [
                    'id' => $req->app_reference,
                    'db_id' => $req->id,
                    'student_id' => $student ? $student->id : null,
                    'student_name' => $student ? $student->full_name : 'Applicant',
                    'phone' => $student ? $student->phone : 'N/A',
                    'email' => $student ? ($student->email ?? 'N/A') : 'N/A',
                    'aadhaar' => $student ? $student->aadhaar_number : 'N/A',
                    'pan' => $student ? ($student->pan_number ?? 'N/A') : 'N/A',
                    'parent_name' => $student ? ($student->parent_name ?? 'N/A') : 'N/A',
                    'parent_phone' => $student ? ($student->parent_phone ?? 'N/A') : 'N/A',
                    'address' => $student ? ($student->current_address ?? 'N/A') : 'N/A',
                    'room_number' => $student && $student->room ? $student->room->room_number : 'Pending',
                    'bed_code' => $student && $student->bed ? $student->bed->bed_code : 'Pending',
                    'sharing_type' => $student && $student->room ? $student->room->sharing_type : 'Standard',
                    'rent' => $student && $student->bed ? '₹'.number_format($student->bed->monthly_rent) : 'Pending Room Allocation',
                    'deposit' => $student && $student->bed ? '₹'.number_format($student->bed->security_deposit) : 'Pending Room Allocation',
                    'numeric_rent' => $student && $student->bed ? (float)($student->bed->monthly_rent ?? 0) : 0,
                    'numeric_deposit' => $student && $student->bed ? (float)($student->bed->security_deposit ?? 0) : 0,
                    'date' => $req->created_at ? $req->created_at->format('d M Y') : 'N/A',
                    'status' => $req->status == 'PENDING' ? 'Pending Verification' : $req->status,
                    'kyc_status' => $student ? $student->kyc_status : 'PENDING',
                    'rent_status' => $student ? $student->rent_status : 'NOT_APPLICABLE',
                    'deposit_status' => $student ? $student->deposit_status : 'NOT_APPLICABLE',
                    'student_status' => $student ? $student->status : 'PENDING_APPROVAL',
                    'is_kyc_approved' => $isKycApproved,
                    'is_bed_assigned' => $isBedAssigned,
                    'is_payment_submitted' => $isPaymentSubmitted,
                    'is_payment_done' => $isPaymentDone,
                    'profile_photo' => $formatUrl($profilePhotoDoc?->file_path),
                    'aadhaar_front' => $formatUrl($aadhaarFrontDoc?->file_path),
                    'aadhaar_back' => $formatUrl($aadhaarBackDoc?->file_path),
                    'pan_card' => $formatUrl($panCardDoc?->file_path),
                    'initial_meter_photo' => $formatUrl($meterPhotoDoc?->file_path ?? $student?->initial_meter_photo),
                    'initial_meter_reading' => $student ? (float)($student->initial_meter_reading ?? 0) : 0,
                    'payment_proof' => $formatUrl($paymentProof?->screenshot_path),
                    'payment_utr' => $paymentProof?->utr_number,
                    'payment_status' => $latestPayment ? $latestPayment->status : ($paymentProof ? 'UPLOADED' : 'PENDING_UPLOAD'),
                ];
            });

        $availableBeds = Bed::with('room')
            ->where('status', 'AVAILABLE')
            ->whereHas('room', function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'label' => 'Room ' . ($b->room ? $b->room->room_number : 'N/A') . ' (' . $b->bed_code . ') - ₹' . number_format($b->monthly_rent) . '/mo',
                    'monthly_rent' => (float)$b->monthly_rent,
                    'security_deposit' => (float)$b->security_deposit,
                ];
            });

        return view('sub_admin.verifications', [
            'queue' => $queue,
            'availableBeds' => $availableBeds,
            'activeBranch' => $branch,
        ]);
    }

    public function approveKycOnly($id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();
        if (!in_array($requestRecord->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage records for this branch.'], 403);
        }

        DB::transaction(function () use ($requestRecord) {
            $requestRecord->update(['status' => 'KYC_APPROVED', 'processed_by' => Auth::id()]);
            if ($student = $requestRecord->student) {
                $student->update(['kyc_status' => 'APPROVED', 'status' => 'KYC_APPROVED']);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Approved Student KYC Profile: '.$id,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 1 Complete: Student KYC Profile Approved! Step 2 (Room & Bed Allocation) is now unlocked.',
        ]);
    }

    public function assignBedOnly(Request $request, $id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();
        if (!in_array($requestRecord->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage records for this branch.'], 403);
        }
        
        $student = $requestRecord->student;
        if (!$student || ($student->kyc_status !== 'APPROVED' && $student->status !== 'KYC_APPROVED' && $student->status !== 'BED_ALLOCATED' && $student->status !== 'APPROVED')) {
            return response()->json(['status' => 'error', 'message' => 'Cannot assign Bed. Complete Step 1 (KYC Document Verification) first.'], 422);
        }

        $selectedBedId = $request->input('bed_id');

        if (!$selectedBedId) {
            return response()->json(['status' => 'error', 'message' => 'Please select an available Room & Bed from the dropdown.'], 422);
        }

        $bed = Bed::find($selectedBedId);
        if (!$bed || ($bed->status !== 'AVAILABLE' && $bed->id !== $student->bed_id)) {
            return response()->json(['status' => 'error', 'message' => 'Selected bed is no longer available.'], 422);
        }

        $monthlyRent = $request->filled('monthly_rent') ? (float)$request->input('monthly_rent') : (float)$bed->monthly_rent;
        $securityDeposit = $request->filled('security_deposit') ? (float)$request->input('security_deposit') : (float)$bed->security_deposit;

        DB::transaction(function () use ($requestRecord, $bed, $student, $monthlyRent, $securityDeposit) {
            $requestRecord->update(['status' => 'BED_ALLOCATED', 'processed_by' => Auth::id()]);
            
            $student->update([
                'room_id' => $bed->room_id,
                'bed_id' => $bed->id,
                'rent_status' => 'DUE',
                'deposit_status' => 'DUE',
                'status' => 'BED_ALLOCATED',
                'rent_balance_due' => $monthlyRent,
                'deposit_balance_due' => $securityDeposit,
            ]);
            $bed->update([
                'status' => 'RESERVED',
                'monthly_rent' => $monthlyRent,
                'security_deposit' => $securityDeposit,
            ]);

            $alloc = RoomAllocation::where('student_id', $student->id)->where('status', 'ACTIVE')->first();
            if ($alloc) {
                $alloc->update([
                    'room_id' => $bed->room_id,
                    'bed_id' => $bed->id,
                    'monthly_rent' => $monthlyRent,
                    'security_deposit' => $securityDeposit,
                ]);
            } else {
                RoomAllocation::create([
                    'branch_id' => $student->branch_id,
                    'student_id' => $student->id,
                    'room_id' => $bed->room_id,
                    'bed_id' => $bed->id,
                    'start_date' => now()->toDateString(),
                    'monthly_rent' => $monthlyRent,
                    'security_deposit' => $securityDeposit,
                    'status' => 'ACTIVE',
                    'allocated_by' => Auth::id(),
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Allocated Room & Bed for: '.$id.' (Rent: ₹'.$monthlyRent.', Deposit: ₹'.$securityDeposit.')',
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 2 Complete: Bed '.$bed->bed_code.' allocated! Rent & Deposit payment unlocked in resident app.',
            'monthly_rent' => $monthlyRent,
            'security_deposit' => $securityDeposit,
            'formatted_rent' => '₹'.number_format($monthlyRent),
            'formatted_deposit' => '₹'.number_format($securityDeposit),
            'room_number' => $bed->room ? $bed->room->room_number : 'N/A',
            'bed_code' => $bed->bed_code,
        ]);
    }

    public function updateVerificationAmounts(Request $request, $id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();
        if (!in_array($requestRecord->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage records for this branch.'], 403);
        }

        $student = $requestRecord->student;
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Student record not found for this request.'], 404);
        }

        $validated = $request->validate([
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['required', 'numeric', 'min:0'],
        ]);

        $monthlyRent = (float)$validated['monthly_rent'];
        $securityDeposit = (float)$validated['security_deposit'];

        DB::transaction(function () use ($student, $requestRecord, $monthlyRent, $securityDeposit) {
            // Update Bed if assigned
            if ($student->bed) {
                $student->bed->update([
                    'monthly_rent' => $monthlyRent,
                    'security_deposit' => $securityDeposit,
                ]);
            }

            // Update student dues if unpaid/due
            $studentUpdate = [];
            if ($student->rent_status !== 'PAID') {
                $studentUpdate['rent_balance_due'] = $monthlyRent;
            }
            if ($student->deposit_status !== 'PAID') {
                $studentUpdate['deposit_balance_due'] = $securityDeposit;
            }
            if (!empty($studentUpdate)) {
                $student->update($studentUpdate);
            }

            // Update active room allocations
            $allocations = RoomAllocation::where('student_id', $student->id)->where('status', 'ACTIVE')->get();
            foreach ($allocations as $alloc) {
                $alloc->update([
                    'monthly_rent' => $monthlyRent,
                    'security_deposit' => $securityDeposit,
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated rent (₹'.$monthlyRent.') and deposit (₹'.$securityDeposit.') for applicant '.$requestRecord->app_reference,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rent and Security Deposit amounts updated successfully.',
            'monthly_rent' => $monthlyRent,
            'security_deposit' => $securityDeposit,
            'formatted_rent' => '₹'.number_format($monthlyRent),
            'formatted_deposit' => '₹'.number_format($securityDeposit),
        ]);
    }

    public function approveVerification(Request $request, $id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        if (!in_array($requestRecord->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage records for this branch.'], 403);
        }

        $student = $requestRecord->student;
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Applicant profile not found.'], 404);
        }

        // Auto-assign first available bed if not yet allocated (scoped strictly to student's branch)
        $selectedBedId = $request->input('bed_id') ?? $student->bed_id;
        if (!$selectedBedId) {
            $availableBed = Bed::where('status', 'AVAILABLE')
                ->whereHas('room', function ($q) use ($student) {
                    $q->where('branch_id', $student->branch_id);
                })
                ->first();
            if ($availableBed) {
                $selectedBedId = $availableBed->id;
            } else {
                return response()->json(['status' => 'error', 'message' => 'No available beds in this branch. Please add or free a bed first.'], 422);
            }
        }

        $bed = Bed::find($selectedBedId);

        DB::transaction(function () use ($requestRecord, $student, $bed) {
            $requestRecord->update([
                'status' => 'APPROVED',
                'processed_by' => Auth::id(),
            ]);

            if ($bed) {
                $bed->update(['status' => 'OCCUPIED']);
                $student->update([
                    'room_id' => $bed->room_id,
                    'bed_id' => $bed->id,
                ]);

                RoomAllocation::firstOrCreate(
                    ['student_id' => $student->id, 'bed_id' => $bed->id],
                    [
                        'branch_id' => $student->branch_id,
                        'room_id' => $bed->room_id,
                        'start_date' => now()->toDateString(),
                        'monthly_rent' => $bed->monthly_rent,
                        'security_deposit' => $bed->security_deposit,
                        'status' => 'ACTIVE',
                        'allocated_by' => Auth::id(),
                    ]
                );
            }

            $student->update([
                'status' => 'APPROVED',
                'kyc_status' => 'APPROVED',
                'rent_status' => 'PAID',
                'deposit_status' => 'PAID',
            ]);

            // If pending payment proof exists, mark it verified
            $latestPayment = Payment::where('student_id', $student->id)->latest()->first();
            if ($latestPayment) {
                $latestPayment->update(['status' => 'VERIFIED', 'paid_at' => now()]);
                if ($latestPayment->proof) {
                    $latestPayment->proof->update(['status' => 'VERIFIED', 'verified_by' => Auth::id()]);
                }
            } else {
                // Generate initial admission receipt
                $initRent = (float)$student->effective_rent_due;
                $initDeposit = (float)$student->effective_deposit_due;
                $initAmount = $initRent + $initDeposit;
                if ($initAmount <= 0 && $bed) {
                    $initAmount = (float)$bed->monthly_rent;
                }

                $payment = Payment::create([
                    'student_id' => $student->id,
                    'branch_id' => $student->branch_id,
                    'tx_reference' => 'PAY-'.date('Y').'-'.rand(1000, 9999),
                    'payment_type' => 'RENT_AND_DEPOSIT',
                    'amount' => $initAmount,
                    'total_due' => $initAmount,
                    'remaining_amount' => 0,
                    'payment_mode' => 'UPI',
                    'payment_date' => now()->toDateString(),
                    'status' => 'VERIFIED',
                    'paid_at' => now(),
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Completed Step 3 Admission Approval & Key Handover for: '.$id,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 3 Complete: Resident Admission Approved & Key Handover Confirmed!',
        ]);
    }

    public function rejectVerification($id)
    {
        $requestRecord = RegistrationRequest::where('app_reference', $id)->orWhere('id', $id)->firstOrFail();
        if (!in_array($requestRecord->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage records for this branch.'], 403);
        }

        DB::transaction(function () use ($requestRecord) {
            $requestRecord->update([
                'status' => 'REJECTED',
                'processed_by' => Auth::id(),
            ]);

            if ($student = $requestRecord->student) {
                $student->update(['status' => 'REJECTED']);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rejected Registration Request: '.$id,
            'module' => 'VERIFICATION',
            'record_id' => $requestRecord->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Application rejected successfully.',
        ]);
    }

    public function bedMap(Request $request)
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $roomsPaginated = Room::with([
                'beds.student.documents',
                'beds.activeAllocation.student.documents',
                'students.documents',
                'branch'
            ])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->orderBy('floor_number')
            ->orderBy('room_number')
            ->paginate(100);

        $roomsData = collect($roomsPaginated->items())->map(function ($room) {
            $totalBeds = $room->beds->count() > 0 ? $room->beds->count() : $room->max_beds;

            $bedsData = $room->beds->map(function ($bed) use ($room) {
                // 1. Check direct bed student or active allocation relation
                $student = $bed->student ?? $bed->activeAllocation?->student;

                // 2. Check room allocations table
                if (!$student) {
                    $alloc = RoomAllocation::where('bed_id', $bed->id)->where('status', 'ACTIVE')->latest()->first()
                        ?? RoomAllocation::where('bed_id', $bed->id)->latest()->first();
                    if ($alloc && $alloc->student) {
                        $student = $alloc->student;
                    }
                }

                // 3. Check room students
                if (!$student && $room->students && $room->students->isNotEmpty()) {
                    $student = $room->students->firstWhere('bed_id', $bed->id);
                    if (!$student && $room->students->count() === 1 && $room->beds->count() <= 2) {
                        $student = $room->students->first();
                    }
                }

                $isOccupied = ($student !== null);

                // Extract profile photo url
                $photoUrl = null;
                if ($student && $student->documents) {
                    $profileDoc = $student->documents->firstWhere('doc_type', 'PROFILE_PHOTO');
                    if ($profileDoc && $profileDoc->file_path) {
                        $path = $profileDoc->file_path;
                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            $photoUrl = $path;
                        } else {
                            $clean = ltrim(str_replace('storage/', '', $path), '/');
                            $photoUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($clean)
                                ? asset('storage/' . $clean)
                                : asset($path);
                        }
                    }
                }

                $joiningDateFormatted = 'N/A';
                if ($student && $student->joining_date) {
                    $joiningDateFormatted = is_string($student->joining_date)
                        ? date('d M Y', strtotime($student->joining_date))
                        : $student->joining_date->format('d M Y');
                }

                return [
                    'id' => $bed->id,
                    'code' => $bed->bed_code,
                    'status' => $isOccupied ? 'occupied' : 'available',
                    'is_occupied' => $isOccupied,
                    'student_name' => $student ? $student->full_name : null,
                    'student_phone' => $student ? $student->phone : null,
                    'student_email' => $student ? ($student->email ?? 'N/A') : null,
                    'student_app_ref' => $student ? ($student->app_reference ?? 'REG-'.str_pad($student->id, 4, '0', STR_PAD_LEFT)) : null,
                    'student_id' => $student ? $student->id : null,
                    'student_kyc' => $student ? ($student->kyc_status ?? 'VERIFIED') : 'VERIFIED',
                    'student_rent_status' => $student ? ($student->rent_status ?? 'PAID') : 'PAID',
                    'student_rent_balance' => $student ? (float)$student->effective_rent_due : 0,
                    'student_amount_left' => $student ? (float)$student->effective_rent_due : 0,
                    'student_admission_date' => $joiningDateFormatted,
                    'student_raw_joining_date' => $student && $student->joining_date ? (is_string($student->joining_date) ? date('Y-m-d', strtotime($student->joining_date)) : $student->joining_date->format('Y-m-d')) : '',
                    'student_pan' => $student ? ($student->pan_number ?? '') : '',
                    'student_parent_phone' => $student ? ($student->parent_phone ?? '') : '',
                    'student_initial_meter_reading' => $student ? (float)($student->initial_meter_reading ?? 0) : 0,
                    'student_deposit_balance' => $student ? (float)($student->deposit_balance_due ?? 0) : 0,
                    'branch_id' => $room->branch_id,
                    'student_emergency_contact' => $student ? ($student->emergency_contact ?? $student->parent_phone ?? 'N/A') : null,
                    'student_parent_name' => $student ? ($student->parent_name ?? 'N/A') : null,
                    'student_aadhaar' => $student ? ($student->aadhaar_number ?? 'N/A') : null,
                    'student_address' => $student ? ($student->current_address ?? 'N/A') : null,
                    'student_photo' => $photoUrl,
                    'monthly_rent' => (int) $bed->monthly_rent,
                    'security_deposit' => (int) $bed->security_deposit,
                    'room_number' => (string) $room->room_number,
                    'branch_name' => $room->branch?->name ?? 'PG Branch',
                ];
            })->toArray();

            $occupiedBeds = collect($bedsData)->where('is_occupied', true)->count();
            $availableBeds = max(0, $totalBeds - $occupiedBeds);

            return [
                'id' => $room->id,
                'room_number' => (string) $room->room_number,
                'floor' => (int) $room->floor_number,
                'sharing_type' => $room->sharing_type,
                'is_ac' => (bool) $room->is_ac,
                'rent' => $room->beds->first() ? (int) $room->beds->first()->monthly_rent : 0,
                'deposit' => $room->beds->first() ? (int) $room->beds->first()->security_deposit : 0,
                'total_beds' => $totalBeds,
                'max_beds' => (int) ($room->max_beds ?: $totalBeds),
                'available_beds' => $availableBeds,
                'beds' => $bedsData,
            ];
        })->toArray();

        return view('sub_admin.bed_map', [
            'rooms' => $roomsData,
            'paginator' => $roomsPaginated,
            'activeBranch' => $branch,
            'branch' => $branch,
        ]);
    }

    public function updateRoom(Request $request, $id)
    {
        $branchIds = $this->getAuthorizedBranchIds();
        $room = Room::findOrFail($id);

        if (!in_array($room->branch_id, $branchIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to edit rooms for this branch.'], 403);
        }

        $validated = $request->validate([
            'room_number' => ['required', 'string'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'sharing_type' => ['required', 'string'],
            'max_beds' => ['required', 'integer', 'min:1', 'max:20'],
            'is_ac' => ['required', 'boolean'],
            'rent' => ['required', 'numeric', 'min:0'],
            'deposit' => ['required', 'numeric', 'min:0'],
        ]);

        $occupiedBedsCount = Bed::where('room_id', $room->id)
            ->whereIn('status', ['OCCUPIED', 'RESERVED'])
            ->count();

        if ($validated['max_beds'] < $occupiedBedsCount) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot reduce room capacity to {$validated['max_beds']}. There are currently {$occupiedBedsCount} occupied/reserved beds in this room.",
            ], 422);
        }

        DB::transaction(function () use ($room, $validated) {
            $room->update([
                'room_number' => $validated['room_number'],
                'floor_number' => $validated['floor_number'],
                'sharing_type' => $validated['sharing_type'],
                'max_beds' => $validated['max_beds'],
                'is_ac' => $validated['is_ac'],
            ]);

            $existingBeds = Bed::where('room_id', $room->id)->orderBy('id')->get();
            $currentBedCount = $existingBeds->count();
            $targetBedCount = (int) $validated['max_beds'];

            if ($targetBedCount > $currentBedCount) {
                for ($b = $currentBedCount + 1; $b <= $targetBedCount; $b++) {
                    $suffix = chr(64 + $b);
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_code' => 'Bed ' . $validated['room_number'] . '-' . $suffix,
                        'monthly_rent' => $validated['rent'],
                        'security_deposit' => $validated['deposit'],
                        'status' => 'AVAILABLE',
                    ]);
                }
            } elseif ($targetBedCount < $currentBedCount) {
                $bedsToRemoveCount = $currentBedCount - $targetBedCount;
                $removableBeds = Bed::where('room_id', $room->id)
                    ->where('status', 'AVAILABLE')
                    ->orderByDesc('id')
                    ->take($bedsToRemoveCount)
                    ->get();

                foreach ($removableBeds as $rb) {
                    $rb->delete();
                }
            }

            Bed::where('room_id', $room->id)
                ->where('status', 'AVAILABLE')
                ->update([
                    'monthly_rent' => $validated['rent'],
                    'security_deposit' => $validated['deposit'],
                ]);
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Sub Admin updated Room ' . $room->room_number . ' capacity to ' . $validated['max_beds'] . ' beds',
            'module' => 'ROOMS',
            'record_id' => $room->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Room updated successfully!',
            'data' => $room->fresh()->load('beds'),
        ]);
    }

    public function rentLedger()
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $formatUrl = function (?string $path) {
            if (!$path) return null;
            if (str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
            $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
            return asset('storage/' . $cleanPath);
        };

        $duesData = Payment::with(['student.room', 'student.bed', 'branch', 'proof'])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest()
            ->get()
            ->map(function ($payment) use ($formatUrl) {
                $totalDue = $payment->total_due !== null ? (float)$payment->total_due : (float)$payment->amount;
                $amountPaid = (float)$payment->amount;
                $remainingAmount = (float)($payment->remaining_amount ?? 0);
                $isPartial = $payment->status === 'PARTIALLY_PAID' || ($remainingAmount > 0 && $payment->status !== 'REJECTED');

                $statusLabel = 'Pending Verification';
                if ($payment->status === 'VERIFIED' || $payment->status === 'PAID') {
                    $statusLabel = 'Paid';
                } elseif ($isPartial) {
                    $statusLabel = 'Partially Paid (₹' . number_format($remainingAmount) . ' Left)';
                } elseif ($payment->status === 'REJECTED') {
                    $statusLabel = 'Rejected';
                }

                return [
                    'id' => $payment->id,
                    'resident_id' => $payment->student ? $payment->student->app_reference : 'N/A',
                    'student_id' => $payment->student_id,
                    'student_name' => $payment->student ? $payment->student->full_name : 'Resident',
                    'room' => $payment->student && $payment->student->room ? $payment->student->room->room_number.' ('.($payment->student->bed ? $payment->student->bed->bed_code : 'Unassigned').')' : 'Unassigned',
                    'rent' => '₹'.number_format($amountPaid),
                    'amount' => $amountPaid,
                    'total_due' => $totalDue,
                    'formatted_total' => '₹'.number_format($totalDue),
                    'remaining_amount' => $remainingAmount,
                    'amount_left' => $remainingAmount,
                    'formatted_left' => '₹'.number_format($remainingAmount),
                    'is_partial' => $isPartial,
                    'due_date' => $payment->due_date ? $payment->due_date->format('d M Y') : 'N/A',
                    'raw_due_date' => $payment->due_date ? $payment->due_date->toDateString() : '',
                    'status' => $statusLabel,
                    'raw_status' => $payment->status,
                    'payment_mode' => $payment->payment_mode ?? 'UPI Transfer',
                    'utr' => $payment->proof ? $payment->proof->utr_number : 'N/A',
                    'proof_id' => $payment->proof?->id,
                    'proof_url' => $payment->proof ? $formatUrl($payment->proof->screenshot_path) : null,
                ];
            });

        $students = Student::with(['room', 'bed'])
            ->where('status', 'APPROVED')
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->get()
            ->map(function ($st) {
                $monthlyRent = (float)($st->bed?->monthly_rent ?? 0);
                $balanceDue = (float)$st->effective_rent_due;
                return [
                    'id' => $st->id,
                    'full_name' => $st->full_name,
                    'app_reference' => $st->app_reference,
                    'room_number' => $st->room ? $st->room->room_number : 'Unassigned',
                    'bed_code' => $st->bed ? $st->bed->bed_code : 'Unassigned',
                    'monthly_rent' => $monthlyRent,
                    'security_deposit' => (float)($st->bed?->security_deposit ?? 0),
                    'rent_status' => $st->rent_status ?? 'DUE',
                    'rent_balance_due' => $balanceDue,
                    'amount_left' => $balanceDue,
                ];
            });

        return view('sub_admin.rent_ledger', [
            'dues' => $duesData,
            'students' => $students,
            'activeBranch' => $branch,
            'branch' => $branch,
        ]);
    }

    public function recordCashPayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_type' => ['required', 'string', 'in:RENT,DEPOSIT,ELECTRICITY,OTHER'],
            'amount' => ['required', 'numeric', 'min:1'],
            'total_due' => ['nullable', 'numeric', 'min:0'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'string', 'in:CASH,UPI,BANK_TRANSFER,CHEQUE'],
            'payment_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $student = Student::with(['room', 'bed'])->findOrFail($validated['student_id']);
        $amountPaid = (float) $validated['amount'];

        // Determine total expected bill/due
        if (isset($validated['total_due']) && (float)$validated['total_due'] > 0) {
            $totalDue = (float) $validated['total_due'];
        } elseif ($validated['payment_type'] === 'RENT') {
            $totalDue = (float) ($student->bed?->monthly_rent ?? $amountPaid);
        } elseif ($validated['payment_type'] === 'DEPOSIT') {
            $totalDue = (float) ($student->bed?->security_deposit ?? $amountPaid);
        } else {
            $totalDue = $amountPaid;
        }

        // Determine remaining balance left
        if (isset($validated['remaining_amount']) && is_numeric($validated['remaining_amount'])) {
            $remainingAmount = max(0, (float)$validated['remaining_amount']);
        } else {
            $remainingAmount = max(0, $totalDue - $amountPaid);
        }

        $status = $remainingAmount <= 0 ? 'PAID' : 'PARTIALLY_PAID';
        $paymentMode = $validated['payment_mode'] ?? 'CASH';

        DB::transaction(function () use ($validated, $student, $amountPaid, $totalDue, $remainingAmount, $status, $paymentMode) {
            $payment = Payment::create([
                'tx_reference' => 'PAY-'.date('Y').'-'.rand(1000, 9999),
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'payment_type' => $validated['payment_type'],
                'amount' => $amountPaid,
                'total_due' => $totalDue,
                'remaining_amount' => $remainingAmount,
                'payment_mode' => $paymentMode,
                'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
                'status' => $status,
                'paid_at' => now(),
                'verification_remarks' => $validated['remarks'] ?? null,
            ]);

            PaymentProof::create([
                'payment_id' => $payment->id,
                'utr_number' => ($paymentMode === 'CASH' ? 'CASH/' : 'MANUAL/') . rand(100000000000, 999999999999),
                'screenshot_path' => 'uploads/proofs/cash_receipt.png',
                'status' => 'VERIFIED',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            if ($validated['payment_type'] === 'RENT') {
                $student->update([
                    'rent_status' => $status,
                    'rent_balance_due' => $remainingAmount,
                ]);
            } elseif ($validated['payment_type'] === 'DEPOSIT') {
                $student->update([
                    'deposit_status' => $remainingAmount <= 0 ? 'VERIFIED' : 'PARTIALLY_PAID',
                    'deposit_balance_due' => $remainingAmount,
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Recorded Manual Payment: Paid ₹'.$amountPaid.', Left ₹'.$remainingAmount.' of Total ₹'.$totalDue.' for '.$student->full_name,
            'module' => 'FINANCE',
            'record_id' => $student->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment receipt recorded successfully! Paid: ₹'.number_format($amountPaid).' • Remaining Left: ₹'.number_format($remainingAmount),
        ]);
    }

    public function verifyPayment($id)
    {
        $payment = Payment::where('id', $id)->orWhere('tx_reference', $id)->firstOrFail();
        if (!in_array($payment->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to verify payments for this branch.'], 403);
        }

        DB::transaction(function () use ($payment) {
            $isPartial = (float)($payment->remaining_amount ?? 0) > 0;
            $newStatus = $isPartial ? 'PARTIALLY_PAID' : 'VERIFIED';

            $payment->update([
                'status' => $newStatus,
                'paid_at' => now(),
            ]);

            if ($payment->proof) {
                $payment->proof->update([
                    'status' => 'VERIFIED',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                ]);
            }

            if ($student = $payment->student) {
                $student->update([
                    'rent_status' => $isPartial ? 'PARTIALLY_PAID' : 'PAID',
                    'rent_balance_due' => (float)($payment->remaining_amount ?? 0),
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Verified Payment ID: '.$payment->id.' (₹'.$payment->amount.')',
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment verified successfully!',
        ]);
    }

    public function rejectPayment($id)
    {
        $payment = Payment::where('id', $id)->orWhere('tx_reference', $id)->firstOrFail();
        if (!in_array($payment->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to verify payments for this branch.'], 403);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'REJECTED',
                'paid_at' => null,
            ]);

            if ($payment->proof) {
                $payment->proof->update([
                    'status' => 'REJECTED',
                    'verified_by' => null,
                ]);
            }

            if ($student = $payment->student) {
                $student->load('bed');
                $student->update([
                    'rent_status' => 'DUE',
                    'rent_balance_due' => (float)($student->bed?->monthly_rent ?? 0),
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rejected Payment ID: '.$payment->id.' (₹'.$payment->amount.')',
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment rejected successfully!',
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'total_due' => ['nullable', 'numeric', 'min:0'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'payment_mode' => ['required', 'string'],
            'utr' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:PENDING,PAID,VERIFIED,PARTIALLY_PAID,REJECTED'],
        ]);

        $amount = (float)$validated['amount'];
        $totalDue = isset($validated['total_due']) && (float)$validated['total_due'] > 0 
            ? (float)$validated['total_due'] 
            : (float)($payment->total_due ?? $amount);

        $remainingAmount = isset($validated['remaining_amount']) 
            ? max(0, (float)$validated['remaining_amount']) 
            : max(0, $totalDue - $amount);

        $finalStatus = $validated['status'];
        if (in_array($finalStatus, ['PAID', 'VERIFIED']) && $remainingAmount > 0) {
            $finalStatus = 'PARTIALLY_PAID';
        }

        DB::transaction(function () use ($payment, $validated, $amount, $totalDue, $remainingAmount, $finalStatus) {
            $payment->update([
                'amount' => $amount,
                'total_due' => $totalDue,
                'remaining_amount' => $remainingAmount,
                'due_date' => $validated['due_date'],
                'payment_mode' => $validated['payment_mode'],
                'status' => $finalStatus,
                'paid_at' => in_array($finalStatus, ['VERIFIED', 'PAID', 'PARTIALLY_PAID']) ? ($payment->paid_at ?? now()) : null,
            ]);

            if ($payment->proof) {
                $payment->proof->update([
                    'utr_number' => $validated['utr'],
                    'status' => in_array($finalStatus, ['VERIFIED', 'PAID', 'PARTIALLY_PAID']) ? 'VERIFIED' : 'PENDING',
                ]);
            } else if ($validated['utr']) {
                PaymentProof::create([
                    'payment_id' => $payment->id,
                    'utr_number' => $validated['utr'],
                    'screenshot_path' => 'uploads/proofs/cash_receipt.png',
                    'status' => in_array($finalStatus, ['VERIFIED', 'PAID', 'PARTIALLY_PAID']) ? 'VERIFIED' : 'PENDING',
                    'verified_by' => in_array($finalStatus, ['VERIFIED', 'PAID', 'PARTIALLY_PAID']) ? Auth::id() : null,
                ]);
            }

            if ($student = $payment->student) {
                $student->update([
                    'rent_status' => $finalStatus === 'PARTIALLY_PAID' ? 'PARTIALLY_PAID' : (in_array($finalStatus, ['PAID', 'VERIFIED']) ? 'PAID' : 'DUE'),
                    'rent_balance_due' => $remainingAmount,
                ]);
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Payment ID: '.$payment->id.' (₹'.$validated['amount'].')',
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment updated successfully!',
        ]);
    }

    public function electricityAudit()
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $formatUrl = function (?string $path) {
            if (!$path) return null;
            if (str_contains($path, 'Exception') || str_contains($path, 'Error') || str_contains($path, 'Failed') || str_contains($path, 'DioException')) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
            $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }
            return null;
        };

        $readingsData = ElectricityReading::with(['student.documents', 'room', 'branch'])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest('id')
            ->get()
            ->map(function ($reading) use ($formatUrl) {
                $photo = $formatUrl($reading->meter_photo_path);
                if (!$photo) {
                    $photo = 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80';
                }

                $initialDoc = $reading->student?->documents?->firstWhere('doc_type', 'INITIAL_METER_PHOTO');
                $initialPhoto = $formatUrl($initialDoc?->file_path ?? $reading->student?->initial_meter_photo);

                return [
                    'id' => $reading->id,
                    'code' => 'E-2026-'.str_pad($reading->id, 3, '0', STR_PAD_LEFT),
                    'student' => $reading->student ? $reading->student->full_name : 'Resident',
                    'student_id' => $reading->student_id,
                    'room' => $reading->room ? $reading->room->room_number : 'N/A',
                    'prev_reading' => (float)$reading->previous_reading,
                    'curr_reading' => (float)$reading->current_reading,
                    'units' => (float)$reading->units_consumed,
                    'rate' => '₹'.number_format($reading->unit_rate, 2),
                    'raw_rate' => (float)$reading->unit_rate,
                    'total' => '₹'.number_format($reading->total_amount),
                    'raw_total' => (float)$reading->total_amount,
                    'reading_month' => $reading->reading_month,
                    'photo_url' => $photo,
                    'initial_photo_url' => $initialPhoto,
                    'initial_reading' => (float)($reading->student?->initial_meter_reading ?? 0),
                    'date' => $reading->created_at ? $reading->created_at->format('d M Y') : 'N/A',
                    'status' => $reading->status == 'APPROVED' ? 'Approved' : ($reading->status == 'REJECTED' ? 'Rejected' : 'Pending Audit'),
                    'raw_status' => $reading->status,
                ];
            });

        $students = Student::with(['room', 'bed'])
            ->where('status', 'APPROVED')
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->get();

        $rooms = Room::with(['branch'])
            ->withCount(['beds', 'students' => function($q) {
                $q->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED']);
            }])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->orderBy('room_number')
            ->get();

        return view('sub_admin.electricity_audit', [
            'readings' => $readingsData,
            'students' => $students,
            'rooms' => $rooms,
            'activeBranch' => $branch,
            'branch' => $branch,
        ]);
    }

    public function approveElectricityReading($id)
    {
        $reading = ElectricityReading::findOrFail($id);
        if (!in_array($reading->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to audit readings for this branch.'], 403);
        }

        DB::transaction(function () use ($reading) {
            $reading->update([
                'status' => 'APPROVED',
                'audited_by' => Auth::id(),
            ]);

            if ((float)$reading->total_amount > 0) {
                Payment::firstOrCreate(
                    [
                        'student_id' => $reading->student_id,
                        'payment_type' => 'ELECTRICITY',
                        'tx_reference' => 'ELEC-' . $reading->id . '-' . date('Ym'),
                    ],
                    [
                        'branch_id' => $reading->branch_id,
                        'amount' => $reading->total_amount,
                        'total_due' => $reading->total_amount,
                        'remaining_amount' => $reading->total_amount,
                        'payment_mode' => 'UPI',
                        'payment_date' => now()->toDateString(),
                        'due_date' => now()->addDays(7)->toDateString(),
                        'status' => 'PENDING',
                        'verification_remarks' => 'Electricity Bill for ' . $reading->reading_month . ' (' . $reading->units_consumed . ' units @ ₹' . $reading->unit_rate . ')',
                    ]
                );
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Approved Electricity Bill ID: '.$id.' (₹'.$reading->total_amount.')',
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity bill of ₹'.number_format($reading->total_amount).' approved and added to dues ledger!',
        ]);
    }

    public function recordMonthlyReading(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'current_reading' => ['required', 'numeric', 'min:0'],
            'reading_month' => ['nullable', 'string'],
            'meter_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $student = Student::with(['room', 'branch'])->findOrFail($validated['student_id']);
        
        $meterPhotoPath = null;
        if ($request->hasFile('meter_photo')) {
            $meterPhotoPath = $request->file('meter_photo')->store('uploads/meter', 'public');
        }

        $lastReading = ElectricityReading::where(function ($q) use ($student) {
            $q->where('student_id', $student->id);
            if ($student->room_id) {
                $q->orWhere('room_id', $student->room_id);
            }
        })->latest('id')->first();

        $currReading = (float) $validated['current_reading'];
        $prevReading = $lastReading 
            ? (float) $lastReading->current_reading 
            : (float) ($student->initial_meter_reading ?? 0.0);

        $units = max(0, round($currReading - $prevReading, 2));
        $rate = $student->branch ? (float) $student->branch->electricity_unit_rate : 10.0;
        $total = round($units * $rate, 2);

        $roomId = $student->room_id;
        if (!$roomId && $student->branch_id) {
            $roomId = \App\Models\Room::where('branch_id', $student->branch_id)->first()?->id;
        }

        $reading = ElectricityReading::create([
            'branch_id' => $student->branch_id,
            'room_id' => $roomId ?? 1,
            'student_id' => $student->id,
            'reading_month' => $validated['reading_month'] ?? date('F Y'),
            'current_reading' => $currReading,
            'previous_reading' => $prevReading,
            'units_consumed' => $units,
            'unit_rate' => $rate,
            'total_amount' => $total,
            'meter_photo_path' => $meterPhotoPath ?? 'uploads/meter/reading.jpg',
            'status' => 'APPROVED',
            'audited_by' => Auth::id(),
        ]);

        if ($total > 0) {
            Payment::create([
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'tx_reference' => 'ELEC-' . $reading->id . '-' . date('Ym'),
                'payment_type' => 'ELECTRICITY',
                'amount' => $total,
                'total_due' => $total,
                'remaining_amount' => $total,
                'payment_mode' => 'UPI',
                'payment_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 'PENDING',
                'verification_remarks' => 'Electricity Bill for ' . $reading->reading_month . ' (' . $units . ' units @ ₹' . $rate . ')',
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Recorded Month-End Electricity Reading for ' . $student->full_name . ' (' . $units . ' units, ₹' . $total . ')',
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Monthly electricity reading recorded and bill of ₹' . number_format($total) . ' generated successfully!',
        ]);
    }

    public function rejectElectricityReading($id)
    {
        $reading = ElectricityReading::findOrFail($id);
        if (!in_array($reading->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to audit readings for this branch.'], 403);
        }

        $reading->update([
            'status' => 'REJECTED',
            'audited_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rejected Electricity Meter Reading ID: '.$id,
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity meter reading rejected.',
        ]);
    }

    public function updateElectricityReading(Request $request, $id)
    {
        $reading = ElectricityReading::findOrFail($id);

        $validated = $request->validate([
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => ['required', 'numeric', 'gte:previous_reading'],
            'unit_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:PENDING,APPROVED,REJECTED'],
        ]);

        $unitsConsumed = $validated['current_reading'] - $validated['previous_reading'];
        $totalAmount = $unitsConsumed * $validated['unit_rate'];

        $reading->update([
            'previous_reading' => $validated['previous_reading'],
            'current_reading' => $validated['current_reading'],
            'units_consumed' => $unitsConsumed,
            'unit_rate' => $validated['unit_rate'],
            'total_amount' => $totalAmount,
            'status' => $validated['status'],
            'audited_by' => ($validated['status'] !== 'PENDING') ? Auth::id() : null,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Electricity Reading ID: '.$reading->id.' ('.$unitsConsumed.' Units)',
            'module' => 'ELECTRICITY',
            'record_id' => $reading->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity reading updated successfully!',
        ]);
    }

    /**
     * Get Room Electricity Sub-Meter Reading Details (Baseline, Previous, and Active Residents).
     */
    public function getRoomElectricityInfo(Request $request, $roomId)
    {
        $room = Room::with(['branch', 'beds'])->findOrFail($roomId);
        if (!in_array($room->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action for this branch.'], 403);
        }

        $billingMonth = trim($request->query('billing_month', date('Y-m')));
        try {
            $monthDate = \Carbon\Carbon::parse($billingMonth . '-01');
        } catch (\Exception $e) {
            $monthDate = \Carbon\Carbon::now();
        }

        $unitRate = $room->branch && (float)$room->branch->electricity_unit_rate > 0 
            ? (float)$room->branch->electricity_unit_rate 
            : 10.0;

        // 1. Check if a reading for this exact billing month already exists for this room
        $currentMonthReading = ElectricityReading::where('room_id', $room->id)
            ->where(function ($q) use ($billingMonth, $monthDate) {
                $q->where('reading_month', $billingMonth)
                  ->orWhere('reading_month', 'like', '%' . $billingMonth . '%')
                  ->orWhere('reading_month', 'like', '%' . $monthDate->format('F Y') . '%');
            })
            ->latest('id')
            ->first();

        // 2. Find previous reading baseline
        $previousReading = null;
        $baselineSource = 'None';
        $baselinePhotoUrl = null;

        // Look for the most recent reading prior to this one
        $lastReading = ElectricityReading::where('room_id', $room->id)
            ->when($currentMonthReading, function($q) use ($currentMonthReading) {
                $q->where('id', '!=', $currentMonthReading->id);
            })
            ->latest('id')
            ->first();

        if ($lastReading) {
            $previousReading = (float)$lastReading->current_reading;
            $baselineSource = 'Previous Reading (' . $lastReading->reading_month . ')';
            $baselinePhotoUrl = $this->formatStorageUrl($lastReading->meter_photo_path);
        } else {
            // Check student initial move-in meter readings for this room
            $baselineStudent = Student::with(['documents'])
                ->where('room_id', $room->id)
                ->where(function($q) {
                    $q->whereNotNull('initial_meter_reading')
                      ->orWhereNotNull('initial_meter_photo');
                })
                ->latest('id')
                ->first();

            if ($baselineStudent) {
                $previousReading = (float)($baselineStudent->initial_meter_reading ?? 0);
                $doc = $baselineStudent->documents->firstWhere('doc_type', 'INITIAL_METER_PHOTO');
                $baselinePhotoUrl = $this->formatStorageUrl($doc?->file_path ?? $baselineStudent->initial_meter_photo);
                $baselineSource = 'Move-In Baseline by ' . ($baselineStudent->full_name ?: 'Resident');
            } else {
                $previousReading = 0.0;
                $baselineSource = 'Default Baseline (0.0 kWh)';
            }
        }

        // Active students in room
        $directStudents = Student::with(['bed'])
            ->where('room_id', $room->id)
            ->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED'])
            ->get();

        $bedStudents = Student::with(['bed'])
            ->whereHas('bed', function($q) use ($room) {
                $q->where('room_id', $room->id);
            })
            ->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED'])
            ->get();

        $allocationStudents = Student::with(['bed'])
            ->whereHas('bed.activeAllocation', function($q) use ($room) {
                $q->where('room_id', $room->id);
            })
            ->get();

        $allStudents = $directStudents->merge($bedStudents)->merge($allocationStudents)->unique('id');

        return response()->json([
            'status' => 'success',
            'data' => [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'floor_number' => $room->floor_number,
                'sharing_type' => $room->sharing_type,
                'unit_rate' => $unitRate,
                'previous_reading' => $currentMonthReading ? (float)$currentMonthReading->previous_reading : (float)$previousReading,
                'current_reading' => $currentMonthReading ? (float)$currentMonthReading->current_reading : null,
                'units_consumed' => $currentMonthReading ? (float)$currentMonthReading->units_consumed : null,
                'total_bill' => $currentMonthReading ? (float)$currentMonthReading->total_amount : null,
                'meter_photo_url' => $currentMonthReading ? $this->formatStorageUrl($currentMonthReading->meter_photo_path) : null,
                'baseline_photo_url' => $baselinePhotoUrl,
                'baseline_source' => $baselineSource,
                'has_saved_reading' => $currentMonthReading !== null,
                'active_residents' => $allStudents->map(function($st) {
                    return [
                        'id' => $st->id,
                        'name' => $st->full_name,
                        'phone' => $st->phone,
                        'bed_code' => $st->bed?->bed_code ?? 'Bed Unassigned',
                        'joining_date' => $st->joining_date ? $st->joining_date->format('d M Y') : ($st->created_at ? $st->created_at->format('d M Y') : 'N/A'),
                    ];
                })->values(),
            ]
        ]);
    }

    /**
     * Calculate Pro-Rata Room Electricity Split based on resident move-in dates & days stayed.
     */
    public function calculateElectricitySplit(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'billing_month' => ['required', 'string'],
            'previous_reading' => ['nullable', 'numeric', 'min:0'],
            'current_reading' => ['nullable', 'numeric', 'min:0'],
            'unit_rate' => ['nullable', 'numeric', 'min:0'],
            'total_bill' => ['nullable', 'numeric', 'min:0'],
        ]);

        $room = Room::with(['branch', 'beds'])->findOrFail($validated['room_id']);
        if (!in_array($room->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission for this branch.'], 403);
        }

        $monthStr = trim($validated['billing_month']);
        try {
            $dateObj = \Carbon\Carbon::parse($monthStr . '-01');
        } catch (\Exception $e) {
            $dateObj = \Carbon\Carbon::now();
        }

        $startOfMonth = $dateObj->copy()->startOfMonth();
        $endOfMonth = $dateObj->copy()->endOfMonth();
        $daysInMonth = (int) $startOfMonth->daysInMonth;

        $previousReading = isset($validated['previous_reading']) ? (float)$validated['previous_reading'] : 0.0;
        $currentReading = isset($validated['current_reading']) ? (float)$validated['current_reading'] : null;
        $unitRate = isset($validated['unit_rate']) && (float)$validated['unit_rate'] > 0
            ? (float)$validated['unit_rate']
            : ($room->branch && (float)$room->branch->electricity_unit_rate > 0 ? (float)$room->branch->electricity_unit_rate : 10.0);

        if ($currentReading !== null) {
            $unitsConsumed = max(0, round($currentReading - $previousReading, 2));
            $totalBill = round($unitsConsumed * $unitRate, 2);
        } else {
            $totalBill = isset($validated['total_bill']) && (float)$validated['total_bill'] > 0
                ? (float)$validated['total_bill']
                : 0.0;
            $unitsConsumed = $unitRate > 0 ? round($totalBill / $unitRate, 2) : 0.0;
        }

        // Auto-pull from latest electricity reading for room if total_bill not passed and current_reading not passed
        if ($totalBill <= 0) {
            $latestReading = ElectricityReading::where('room_id', $room->id)
                ->where(function ($q) use ($startOfMonth, $monthStr) {
                    $q->where('reading_month', $monthStr)
                      ->orWhere('reading_month', 'like', '%' . $startOfMonth->format('F Y') . '%')
                      ->orWhere('reading_month', 'like', '%' . $monthStr . '%');
                })
                ->latest('id')
                ->first();

            if ($latestReading && (float)$latestReading->total_amount > 0) {
                $totalBill = (float)$latestReading->total_amount;
                $previousReading = (float)$latestReading->previous_reading;
                $currentReading = (float)$latestReading->current_reading;
                $unitsConsumed = (float)$latestReading->units_consumed;
                $unitRate = (float)$latestReading->unit_rate;
            }
        }

        if ($totalBill <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a Current Meter Reading (greater than Previous Reading) or a Total Room Bill amount greater than ₹0.'
            ], 422);
        }

        // Collect all active residents allocated to this room
        $directStudents = Student::with(['bed'])
            ->where('room_id', $room->id)
            ->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED'])
            ->get();

        $bedStudents = Student::with(['bed'])
            ->whereHas('bed', function($q) use ($room) {
                $q->where('room_id', $room->id);
            })
            ->whereIn('status', ['APPROVED', 'ACTIVE', 'BED_ALLOCATED', 'KYC_APPROVED'])
            ->get();

        $allocationStudents = Student::with(['bed'])
            ->whereHas('bed.activeAllocation', function($q) use ($room) {
                $q->where('room_id', $room->id);
            })
            ->get();

        $allStudents = $directStudents->merge($bedStudents)->merge($allocationStudents)->unique('id');

        if ($allStudents->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => "No active residents found in Room {$room->room_number}."
            ], 422);
        }

        // Build occupant stay intervals
        $occupants = [];
        foreach ($allStudents as $st) {
            $joinDate = $st->joining_date 
                ? \Carbon\Carbon::parse($st->joining_date) 
                : ($st->created_at ? \Carbon\Carbon::parse($st->created_at) : $startOfMonth->copy());

            if ($joinDate->gt($endOfMonth)) {
                $startDay = $daysInMonth + 1; // Not present this month
            } elseif ($joinDate->lt($startOfMonth)) {
                $startDay = 1;
            } else {
                $startDay = (int) $joinDate->day;
            }

            $endDay = $daysInMonth;

            $occupants[] = [
                'id' => $st->id,
                'name' => $st->full_name ?: 'Resident #' . $st->id,
                'phone' => $st->phone ?: 'N/A',
                'bed_code' => $st->bed ? $st->bed->bed_code : 'Bed N/A',
                'joining_date' => $joinDate->format('d M Y'),
                'raw_joining_date' => $joinDate->format('Y-m-d'),
                'start_day' => $startDay,
                'end_day' => $endDay,
            ];
        }

        $dailyCost = $totalBill / $daysInMonth;
        $studentDailyShares = [];
        $studentDaysActive = [];
        $activeSetsByDay = [];

        foreach ($occupants as $occ) {
            $studentDailyShares[$occ['id']] = 0.0;
            $studentDaysActive[$occ['id']] = 0;
        }

        // Day-by-Day Integration
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $activeOnDay = [];
            foreach ($occupants as $occ) {
                if ($day >= $occ['start_day'] && $day <= $occ['end_day']) {
                    $activeOnDay[] = $occ['id'];
                }
            }
            $activeSetsByDay[$day] = $activeOnDay;
            $count = count($activeOnDay);
            if ($count > 0) {
                $costPerPerson = $dailyCost / $count;
                foreach ($activeOnDay as $sid) {
                    $studentDailyShares[$sid] += $costPerPerson;
                    $studentDaysActive[$sid]++;
                }
            }
        }

        // Group consecutive days with identical occupant sets into Periods
        $periods = [];
        $currentPeriod = null;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $occupantSet = $activeSetsByDay[$day];
            sort($occupantSet);
            $setKey = implode(',', $occupantSet);

            if ($currentPeriod === null || $currentPeriod['key'] !== $setKey) {
                if ($currentPeriod !== null) {
                    $periods[] = $currentPeriod;
                }
                $currentPeriod = [
                    'key' => $setKey,
                    'from_day' => $day,
                    'to_day' => $day,
                    'occupant_ids' => $occupantSet,
                    'count' => count($occupantSet),
                ];
            } else {
                $currentPeriod['to_day'] = $day;
            }
        }
        if ($currentPeriod !== null) {
            $periods[] = $currentPeriod;
        }

        // Format periods with human-readable dates and labels
        $occupantsById = collect($occupants)->keyBy('id');
        $formattedPeriods = [];
        foreach ($periods as $idx => $p) {
            $pDays = ($p['to_day'] - $p['from_day']) + 1;
            $fromDateStr = $startOfMonth->copy()->addDays($p['from_day'] - 1)->format('d M');
            $toDateStr = $startOfMonth->copy()->addDays($p['to_day'] - 1)->format('d M');
            $pTotalCost = round($pDays * $dailyCost, 2);
            $pCostPerPerson = $p['count'] > 0 ? round($pTotalCost / $p['count'], 2) : 0;

            $occupantNames = [];
            foreach ($p['occupant_ids'] as $sid) {
                if (isset($occupantsById[$sid])) {
                    $occupantNames[] = $occupantsById[$sid]['name'] . ' (' . $occupantsById[$sid]['bed_code'] . ')';
                }
            }

            $formattedPeriods[] = [
                'period_num' => $idx + 1,
                'label' => "Period " . ($idx + 1) . ": {$fromDateStr} - {$toDateStr} ({$pDays} Days)",
                'range' => "{$fromDateStr} - {$toDateStr}",
                'from_day' => $p['from_day'],
                'to_day' => $p['to_day'],
                'days' => $pDays,
                'count' => $p['count'],
                'occupant_names' => $occupantNames,
                'period_cost' => $pTotalCost,
                'cost_per_person' => $pCostPerPerson,
            ];
        }

        // Per-occupant calculations
        $shares = [];
        $totalRounded = 0;
        foreach ($occupants as $occ) {
            $sid = $occ['id'];
            $exact = $studentDailyShares[$sid];
            $rounded = (int) round($exact);
            $totalRounded += $rounded;

            $periodShares = [];
            foreach ($formattedPeriods as $fp) {
                $pObj = $periods[$fp['period_num'] - 1];
                if (in_array($sid, $pObj['occupant_ids'])) {
                    $periodShares[] = [
                        'period_num' => $fp['period_num'],
                        'period_label' => $fp['label'],
                        'days' => $fp['days'],
                        'occupants' => $fp['count'],
                        'share' => $fp['cost_per_person'],
                    ];
                }
            }

            $shares[$sid] = [
                'student_id' => $sid,
                'name' => $occ['name'],
                'phone' => $occ['phone'],
                'bed_code' => $occ['bed_code'],
                'joining_date' => $occ['joining_date'],
                'days_active' => $studentDaysActive[$sid],
                'exact_share' => round($exact, 2),
                'calculated_share' => $rounded,
                'period_shares' => $periodShares,
            ];
        }

        // Balance round-off difference with occupant who stayed the most days
        $diff = (int) round($totalBill) - $totalRounded;
        if ($diff !== 0 && !empty($shares)) {
            $highestKey = array_keys($shares)[0];
            $maxDays = -1;
            foreach ($shares as $k => $s) {
                if ($s['days_active'] > $maxDays) {
                    $maxDays = $s['days_active'];
                    $highestKey = $k;
                }
            }
            $shares[$highestKey]['calculated_share'] += $diff;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'room' => [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'floor_number' => $room->floor_number,
                    'sharing_type' => $room->sharing_type,
                ],
                'billing_month' => $startOfMonth->format('F Y'),
                'billing_month_code' => $monthStr,
                'previous_reading' => $previousReading,
                'current_reading' => $currentReading,
                'units_consumed' => $unitsConsumed,
                'unit_rate' => $unitRate,
                'total_bill' => $totalBill,
                'days_in_month' => $daysInMonth,
                'daily_cost' => round($dailyCost, 2),
                'periods' => $formattedPeriods,
                'occupant_shares' => array_values($shares),
                'sum_shares' => array_sum(array_column($shares, 'calculated_share')),
            ]
        ]);
    }

    /**
     * Push Calculated Pro-Rata Electricity Bills to Residents' Dues Ledger.
     */
    public function pushElectricitySplitDues(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'billing_month' => ['required', 'string'],
            'total_bill' => ['required', 'numeric', 'min:0'],
            'previous_reading' => ['nullable', 'numeric', 'min:0'],
            'current_reading' => ['nullable', 'numeric', 'min:0'],
            'unit_rate' => ['nullable', 'numeric', 'min:0'],
            'units_consumed' => ['nullable', 'numeric', 'min:0'],
            'meter_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'splits' => ['required', 'array', 'min:1'],
            'splits.*.student_id' => ['required', 'exists:students,id'],
            'splits.*.amount' => ['required', 'numeric', 'min:0'],
            'splits.*.days_active' => ['nullable', 'numeric'],
        ]);

        $room = Room::findOrFail($validated['room_id']);
        if (!in_array($room->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action for this branch.'], 403);
        }

        $createdPayments = [];

        DB::transaction(function () use ($room, $request, $validated, &$createdPayments) {
            $meterPhotoPath = null;
            if ($request->hasFile('meter_photo')) {
                $meterPhotoPath = $request->file('meter_photo')->store('uploads/meter', 'public');
            }

            $primaryStudentId = !empty($validated['splits']) ? $validated['splits'][0]['student_id'] : null;

            $prevReading = (float)($request->previous_reading ?? 0);
            $currReading = $request->filled('current_reading') ? (float)$request->current_reading : $prevReading;
            $units = $request->filled('units_consumed') 
                ? (float)$request->units_consumed 
                : max(0, round($currReading - $prevReading, 2));
            $rate = $request->filled('unit_rate') ? (float)$request->unit_rate : 10.0;

            // Save / Update official ElectricityReading for this room & month
            ElectricityReading::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'reading_month' => $validated['billing_month'],
                ],
                [
                    'branch_id' => $room->branch_id,
                    'student_id' => $primaryStudentId ?? ($room->students()->first()?->id ?? 1),
                    'previous_reading' => $prevReading,
                    'current_reading' => $currReading,
                    'units_consumed' => $units,
                    'unit_rate' => $rate,
                    'total_amount' => (float)$validated['total_bill'],
                    'meter_photo_path' => $meterPhotoPath ?? 'uploads/meter/room_reading.jpg',
                    'status' => 'APPROVED',
                    'audited_by' => Auth::id(),
                ]
            );

            foreach ($validated['splits'] as $split) {
                $amount = (float)$split['amount'];
                if ($amount <= 0) continue;

                $student = Student::find($split['student_id']);
                if (!$student) continue;

                $daysActive = $split['days_active'] ?? 30;
                $monthCode = preg_replace('/[^0-9]/', '', $validated['billing_month']);
                if (empty($monthCode)) {
                    $monthCode = date('Ym');
                }
                $txRef = 'ELEC-SPLIT-R' . $room->room_number . '-S' . $student->id . '-' . $monthCode;

                $payment = Payment::updateOrCreate(
                    [
                        'tx_reference' => $txRef,
                    ],
                    [
                        'student_id' => $student->id,
                        'branch_id' => $student->branch_id ?? $room->branch_id,
                        'payment_type' => 'ELECTRICITY',
                        'amount' => $amount,
                        'total_due' => $amount,
                        'remaining_amount' => $amount,
                        'payment_mode' => 'UPI',
                        'payment_date' => now()->toDateString(),
                        'due_date' => now()->addDays(7)->toDateString(),
                        'status' => 'PENDING',
                        'verification_remarks' => "Room {$room->room_number} Light Bill ({$validated['billing_month']}) - {$units} units @ ₹{$rate}/u, {$daysActive} days stay share: ₹" . number_format($amount),
                    ]
                );

                $createdPayments[] = $payment;
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Generated & Pushed Electricity Split Bills for Room ' . $room->room_number . ' (' . count($createdPayments) . ' residents, Total: ₹' . number_format($validated['total_bill']) . ')',
            'module' => 'ELECTRICITY',
            'record_id' => $room->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully generated and pushed light bills for ' . count($createdPayments) . ' residents in Room ' . $room->room_number . '!',
            'count' => count($createdPayments),
        ]);
    }

    public function complaints()
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $ticketsData = Complaint::with(['student', 'room', 'branch'])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest()
            ->get()
            ->map(function ($complaint) {
                return [
                    'ticket' => $complaint->ticket_number,
                    'student' => $complaint->student ? $complaint->student->full_name : 'Resident',
                    'room' => $complaint->room ? $complaint->room->room_number : 'N/A',
                    'category' => $complaint->category,
                    'title' => $complaint->subject,
                    'priority' => ucfirst(strtolower($complaint->priority)),
                    'date' => $complaint->created_at ? $complaint->created_at->format('d M Y') : 'N/A',
                    'status' => in_array($complaint->status, ['RESOLVED', 'Resolved', 'SOLVED', 'Solved']) ? 'Resolved' : (in_array($complaint->status, ['IN_PROGRESS', 'In Progress']) ? 'In Progress' : (in_array($complaint->status, ['OPEN', 'Open']) ? 'Open' : 'Pending')),
                    'db_id' => $complaint->id,
                    'raw_status' => $complaint->status,
                    'raw_priority' => $complaint->priority,
                    'description' => $complaint->description,
                    'resolution_remarks' => $complaint->resolution_remarks,
                ];
            });

        return view('sub_admin.complaints', [
            'tickets' => $ticketsData,
            'activeBranch' => $branch,
            'branch' => $branch,
        ]);
    }

    public function updateComplaint(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        if (!in_array($complaint->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to manage complaints for this branch.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:PENDING,OPEN,IN_PROGRESS,RESOLVED'],
            'priority' => ['required', 'string', 'in:LOW,MEDIUM,HIGH'],
            'resolution_remarks' => ['nullable', 'string'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'resolution_remarks' => $validated['resolution_remarks'] ?? null,
        ];

        if ($validated['status'] === 'RESOLVED') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = Auth::id();
        } else {
            $updateData['resolved_at'] = null;
            $updateData['resolved_by'] = null;
        }

        $complaint->update($updateData);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Complaint Status: '.$complaint->ticket_number.' to '.$validated['status'],
            'module' => 'COMPLAINT',
            'record_id' => $complaint->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket updated successfully!',
        ]);
    }

    public function destroyComplaint($id)
    {
        $complaint = Complaint::findOrFail($id);
        if (!in_array($complaint->branch_id, $this->getAuthorizedBranchIds())) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action: You do not have permission to delete complaints for this branch.'], 403);
        }

        $complaint->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted Complaint Ticket: '.$complaint->ticket_number,
            'module' => 'COMPLAINT',
            'record_id' => $complaint->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket deleted successfully.',
        ]);
    }

    public function broadcastNotice(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string'],
            'is_important' => ['nullable', 'boolean'],
        ]);

        $branch = $this->getActiveBranch();

        $announcement = Announcement::create([
            'branch_id' => $branch ? $branch->id : null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => strtoupper(str_replace(' ', '_', $validated['category'])),
            'is_important' => $request->boolean('is_important', true),
            'created_by' => Auth::id(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Broadcasted Branch Notice: '.$validated['title'],
            'module' => 'ANNOUNCEMENT',
            'record_id' => $announcement->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notice broadcasted to Flutter Student Mobile App!',
            'data' => $announcement,
        ]);
    }

    public function destroyPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted Payment ID: '.$payment->id,
            'module' => 'FINANCE',
            'record_id' => $payment->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully.',
        ]);
    }

    public function getSidebarCounts()
    {
        $branch = $this->getActiveBranch();
        $branchId = $branch ? $branch->id : null;
        $branchIds = $branchId ? [$branchId] : $this->getAuthorizedBranchIds();

        // Calculate counts strictly scoped to the active branch
        $totalBeds = Bed::whereHas('room', function($q) use ($branchId, $branchIds) {
            if ($branchId) $q->where('branch_id', $branchId);
            else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
        })->count();

        $occupiedBeds = Bed::where('status', 'OCCUPIED')->whereHas('room', function($q) use ($branchId, $branchIds) {
            if ($branchId) $q->where('branch_id', $branchId);
            else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
        })->count();

        $availableBeds = Bed::where('status', 'AVAILABLE')->whereHas('room', function($q) use ($branchId, $branchIds) {
            if ($branchId) $q->where('branch_id', $branchId);
            else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
        })->count();

        $pendingRegs = RegistrationRequest::whereIn('status', ['PENDING', 'pending'])
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })->count();

        $pendingProofs = PaymentProof::whereIn('status', ['PENDING', 'pending'])
            ->whereHas('payment', function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })->count();

        $overdueRents = Student::where('rent_status', 'DUE')
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })->count();

        $pendingPasswordResets = PasswordResetRequest::where('status', 'PENDING')
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })->count();

        $pendingComplaints = Complaint::whereNotIn('status', ['RESOLVED', 'CLOSED', 'Resolved', 'Solved'])
            ->where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })->count();

        $monthlyRevenue = Payment::where(function($q) use ($branchId, $branchIds) {
                if ($branchId) $q->where('branch_id', $branchId);
                else if (!empty($branchIds)) $q->whereIn('branch_id', $branchIds);
            })
            ->whereIn('status', ['PAID', 'VERIFIED'])
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        return response()->json([
            'pending_registrations' => $pendingRegs,
            'pending_payments' => $pendingProofs,
            'pending_complaints' => $pendingComplaints,
            'pending_password_resets' => $pendingPasswordResets,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'total_beds' => $totalBeds,
            'occupancy_rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) . '%' : '0%',
            'pending_verifications' => $pendingRegs + $pendingProofs,
            'overdue_rents' => $overdueRents,
            'monthly_revenue' => '₹' . number_format($monthlyRevenue),
        ]);
    }

    public function getComplaintsData()
    {
        $branch = $this->getActiveBranch();
        $branchIds = $this->getAuthorizedBranchIds();

        $ticketsData = Complaint::with(['student', 'room', 'branch'])
            ->where(function($q) use ($branch, $branchIds) {
                if ($branch) {
                    $q->where('branch_id', $branch->id);
                } else if (!empty($branchIds)) {
                    $q->whereIn('branch_id', $branchIds);
                }
            })
            ->latest()
            ->get()
            ->map(function ($complaint) {
                return [
                    'ticket' => $complaint->ticket_number,
                    'student' => $complaint->student ? $complaint->student->full_name : 'Resident',
                    'room' => $complaint->room ? $complaint->room->room_number : 'N/A',
                    'category' => $complaint->category,
                    'title' => $complaint->subject,
                    'priority' => ucfirst(strtolower($complaint->priority)),
                    'date' => $complaint->created_at ? $complaint->created_at->format('d M Y') : 'N/A',
                    'status' => in_array($complaint->status, ['RESOLVED', 'Resolved', 'SOLVED', 'Solved']) ? 'Resolved' : (in_array($complaint->status, ['IN_PROGRESS', 'In Progress']) ? 'In Progress' : (in_array($complaint->status, ['OPEN', 'Open']) ? 'Open' : 'Pending')),
                    'db_id' => $complaint->id,
                    'raw_status' => $complaint->status,
                    'raw_priority' => $complaint->priority,
                    'description' => $complaint->description,
                    'resolution_remarks' => $complaint->resolution_remarks,
                ];
            });

        return response()->json($ticketsData);
    }

    public function viewDocument($id)
    {
        $doc = StudentDocument::with('student')->findOrFail($id);
        $student = $doc->student;
        if (!$student || !in_array($student->branch_id, $this->getAuthorizedBranchIds())) {
            abort(403, 'Unauthorized access to document.');
        }

        $cleanPath = ltrim(str_replace('storage/', '', $doc->file_path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($cleanPath);
        }
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($cleanPath);
        }

        abort(404, 'Document file not found.');
    }

    public function viewPaymentProof($id)
    {
        $proof = PaymentProof::with('payment')->findOrFail($id);
        $payment = $proof->payment;
        if (!$payment || !in_array($payment->branch_id, $this->getAuthorizedBranchIds())) {
            abort(403, 'Unauthorized access to payment proof.');
        }

        $cleanPath = ltrim(str_replace('storage/', '', $proof->screenshot_path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($cleanPath);
        }
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($cleanPath);
        }
        if (file_exists(public_path('storage/' . $cleanPath))) {
            return response()->file(public_path('storage/' . $cleanPath));
        }
        if (file_exists(storage_path('app/public/' . $cleanPath))) {
            return response()->file(storage_path('app/public/' . $cleanPath));
        }

        abort(404, 'Payment proof file not found.');
    }

    public function passwordRequests(Request $request)
    {
        $branchIds = $this->getAuthorizedBranchIds();
        $branch = $this->getActiveBranch();

        $requests = PasswordResetRequest::with(['user', 'student.room', 'student.bed', 'branch', 'resolver'])
            ->where(function ($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds)
                  ->orWhereHas('student', function ($sq) use ($branchIds) {
                      $sq->whereIn('branch_id', $branchIds);
                  });
            })
            ->latest()
            ->get();

        $branchStudents = Student::with(['user', 'room', 'bed'])
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'APPROVED')
            ->orderBy('full_name')
            ->get();

        return view('sub_admin.password_requests', [
            'requests' => $requests,
            'branchStudents' => $branchStudents,
            'activeBranch' => $branch,
            'branch' => $branch,
        ]);
    }

    public function resetPasswordRequest($id, Request $request)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $resetReq = PasswordResetRequest::with(['user', 'student'])->findOrFail($id);
        $branchIds = $this->getAuthorizedBranchIds();

        $reqBranchId = $resetReq->branch_id ?? $resetReq->student?->branch_id;
        if (!$reqBranchId || !in_array($reqBranchId, $branchIds)) {
            abort(403, 'Unauthorized access: Cannot reset password for resident of another branch.');
        }

        $user = $resetReq->user;
        $user->password = Hash::make($validated['new_password']);
        $user->tokens()->delete();
        $user->save();

        $resetReq->update([
            'status' => 'RESOLVED',
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Reset student password via request ID: '.$resetReq->id.' for User: '.$user->name.' ('.$user->phone.')',
            'module' => 'RESIDENT_SECURITY',
            'record_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
                'new_password' => $validated['new_password'],
                'user_name' => $user->name,
                'phone' => $user->phone,
            ]);
        }

        return back()->with('success', "Password for {$user->name} ({$user->phone}) has been updated to: {$validated['new_password']}");
    }

    public function directResetPassword($studentId, Request $request)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $student = Student::with('user')->findOrFail($studentId);
        $branchIds = $this->getAuthorizedBranchIds();

        if (!in_array($student->branch_id, $branchIds)) {
            abort(403, 'Unauthorized access: Cannot reset password for resident of another branch.');
        }

        $last10 = $student->phone ? substr(preg_replace('/[^0-9]/', '', $student->phone), -10) : null;

        $matchingUsers = collect();
        if ($student->user) {
            $matchingUsers->push($student->user);
        }
        if ($last10) {
            $byPhone = User::whereRaw('SUBSTR(REPLACE(REPLACE(REPLACE(phone, "+", ""), " ", ""), "-", ""), -10) = ?', [$last10])->get();
            $matchingUsers = $matchingUsers->merge($byPhone);
        }
        if ($student->email) {
            $byEmail = User::where('email', $student->email)->get();
            $matchingUsers = $matchingUsers->merge($byEmail);
        }
        $matchingUsers = $matchingUsers->unique('id');

        if ($matchingUsers->isEmpty()) {
            $user = User::create([
                'name' => $student->full_name,
                'email' => $student->email ?: ($student->phone . '@rudrapg.com'),
                'phone' => $student->phone,
                'password' => Hash::make($validated['new_password']),
                'role' => 'STUDENT',
                'status' => 'ACTIVE',
            ]);
            $student->user_id = $user->id;
            $student->save();
        } else {
            $primaryUser = $matchingUsers->firstWhere('id', $student->user_id) 
                        ?? $matchingUsers->firstWhere('role', 'STUDENT') 
                        ?? $matchingUsers->first();

            if ($student->user_id !== $primaryUser->id) {
                $student->user_id = $primaryUser->id;
                $student->save();
            }

            if (str_contains(strtolower($primaryUser->name), 'demo') || str_contains(strtolower($primaryUser->email), 'demo')) {
                $primaryUser->name = $student->full_name;
                if ($student->email && !str_contains($student->email, 'demo')) {
                    $primaryUser->email = $student->email;
                }
            }

            foreach ($matchingUsers as $u) {
                $u->password = Hash::make($validated['new_password']);
                $u->status = 'ACTIVE';
                $u->tokens()->delete();
                $u->save();
            }
            $user = $primaryUser;
        }

        $pendingReq = PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingReq) {
            $pendingReq->update([
                'status' => 'RESOLVED',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
                'notes' => 'Resolved directly by ' . Auth::user()->name,
            ]);
        } else {
            PasswordResetRequest::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
                'branch_id' => $student->branch_id,
                'phone' => $user->phone ?? $student->phone,
                'status' => 'RESOLVED',
                'notes' => 'Direct password update by ' . Auth::user()->name,
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Direct reset student password for: '.$user->name.' ('.$user->phone.')',
            'module' => 'RESIDENT_SECURITY',
            'record_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
                'new_password' => $validated['new_password'],
                'user_name' => $user->name,
                'phone' => $user->phone,
            ]);
        }

        return back()->with('success', "Password for {$user->name} ({$user->phone}) has been updated to: {$validated['new_password']}");
    }

    public function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $branchIds = $this->getAuthorizedBranchIds();

        if (!in_array($student->branch_id, $branchIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access: Resident belongs to another branch.'], 403);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'aadhaar_number' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:30'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'current_address' => ['nullable', 'string'],
            'initial_meter_reading' => ['nullable', 'numeric', 'min:0'],
            'kyc_status' => ['nullable', 'string'],
            'rent_status' => ['nullable', 'string'],
            'rent_balance_due' => ['nullable', 'numeric', 'min:0'],
            'deposit_balance_due' => ['nullable', 'numeric', 'min:0'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $studentUpdateData = collect($validated)->except(['monthly_rent', 'security_deposit'])->toArray();
        $student->update($studentUpdateData);

        if ($request->filled('monthly_rent') || $request->filled('security_deposit')) {
            if ($student->bed) {
                $bedUpdate = [];
                if ($request->filled('monthly_rent')) {
                    $bedUpdate['monthly_rent'] = (float)$request->input('monthly_rent');
                }
                if ($request->filled('security_deposit')) {
                    $bedUpdate['security_deposit'] = (float)$request->input('security_deposit');
                }
                $student->bed->update($bedUpdate);
            }
            if ($alloc = $student->roomAllocations()->where('status', 'ACTIVE')->first()) {
                $allocUpdate = [];
                if ($request->filled('monthly_rent')) {
                    $allocUpdate['agreed_rent'] = (float)$request->input('monthly_rent');
                }
                if ($request->filled('security_deposit')) {
                    $allocUpdate['security_deposit'] = (float)$request->input('security_deposit');
                }
                $alloc->update($allocUpdate);
            }
        }

        if ($student->user) {
            $userUpdate = ['name' => $validated['full_name']];
            if (!empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            if (!empty($validated['phone'])) {
                $userUpdate['phone'] = $validated['phone'];
            }
            $student->user->update($userUpdate);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Sub Admin updated student: '.$student->full_name.' (ID: '.$student->id.')',
            'module' => 'STUDENT',
            'record_id' => $student->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Resident record updated successfully.',
            'student' => $student,
        ]);
    }
}
