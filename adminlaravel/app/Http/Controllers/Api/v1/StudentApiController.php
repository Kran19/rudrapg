<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\CreateComplaintRequest;
use App\Http\Requests\Student\RegisterStudentRequest;
use App\Http\Requests\Student\SubmitElectricityReadingRequest;
use App\Http\Requests\Student\UploadPaymentProofRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\ComplaintResource;
use App\Http\Resources\ElectricityReadingResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentApiController extends Controller
{
    public function __construct(protected StudentService $studentService) {}

    public function register(RegisterStudentRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('uploads/kyc', 'public');
        }
        if ($request->hasFile('aadhaar_front')) {
            $data['aadhaar_front_path'] = $request->file('aadhaar_front')->store('uploads/kyc', 'public');
        }
        if ($request->hasFile('aadhaar_back')) {
            $data['aadhaar_back_path'] = $request->file('aadhaar_back')->store('uploads/kyc', 'public');
        }
        if ($request->hasFile('pan_card')) {
            $data['pan_card_path'] = $request->file('pan_card')->store('uploads/kyc', 'public');
        }

        $student = $this->studentService->registerFromQr($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration request submitted successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function profile(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => new StudentResource($student)]);
    }

    public function uploadPaymentProof(UploadPaymentProofRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('screenshot_path')) {
            $data['screenshot_path'] = $request->file('screenshot_path')->store('uploads/proofs', 'public');
        }

        $student = $this->studentService->getProfile($request->user());
        $payment = $this->studentService->uploadPaymentProof($student, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment proof uploaded successfully.',
            'data' => new PaymentResource($payment->load('proof')),
        ]);
    }

    public function submitElectricityReading(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $currentReading = $request->input('current_reading') ?? $request->input('reading');

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['current_reading' => $currentReading],
            ['current_reading' => ['required', 'numeric', 'min:0']]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first('current_reading') ?: 'Current meter reading is required and must be a valid number.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photoPath = null;
        if ($request->hasFile('meter_photo_path')) {
            $photoPath = $request->file('meter_photo_path')->store('uploads/meter', 'public');
        } elseif ($request->hasFile('meter_photo')) {
            $photoPath = $request->file('meter_photo')->store('uploads/meter', 'public');
        } elseif ($request->filled('meter_photo_path') && is_string($request->input('meter_photo_path')) && !str_starts_with($request->input('meter_photo_path'), 'blob:')) {
            $photoPath = $request->input('meter_photo_path');
        }

        $data = [
            'current_reading' => (float) $currentReading,
            'meter_photo_path' => $photoPath,
        ];

        $reading = $this->studentService->submitElectricityReading($student, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity sub-meter reading logged successfully.',
            'data' => new ElectricityReadingResource($reading),
        ]);
    }

    public function createComplaint(CreateComplaintRequest $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $complaint = $this->studentService->createComplaint($student, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Support ticket created.',
            'data' => new ComplaintResource($complaint),
        ], 201);
    }

    public function notices(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $notices = $this->studentService->getNotices($student);

        return response()->json([
            'status' => 'success',
            'data' => AnnouncementResource::collection($notices),
        ]);
    }

    public function electricityReadings(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $readings = \App\Models\ElectricityReading::where(function ($query) use ($student) {
            $query->where('student_id', $student->id);
            if ($student->room_id) {
                $query->orWhere('room_id', $student->room_id);
            }
        })
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => ElectricityReadingResource::collection($readings),
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $payments = \App\Models\Payment::with('proof')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => PaymentResource::collection($payments),
        ]);
    }

    public function complaints(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $complaints = \App\Models\Complaint::where(function ($query) use ($student) {
            $query->where('student_id', $student->id);
            if ($student->room_id) {
                $query->orWhere('room_id', $student->room_id);
            }
        })
        ->latest()
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => ComplaintResource::collection($complaints),
        ]);
    }

    public function verifyBranchQr(Request $request): JsonResponse
    {
        $qrData = $request->input('qr_data') ?? $request->input('code') ?? $request->input('branch_code');
        
        if (empty($qrData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No QR code payload or branch code provided.',
            ], 422);
        }

        // Handle JSON payload if student scanned a structured QR e.g. {"branch_code":"PG-NRD-01", ...}
        $branchCode = $qrData;
        if (is_string($qrData) && (str_starts_with(trim($qrData), '{') || str_contains($qrData, 'branch_code'))) {
            $json = json_decode($qrData, true);
            if (is_array($json) && !empty($json['branch_code'])) {
                $branchCode = $json['branch_code'];
            }
        }
        
        // Handle URL payload e.g. https://domain.com/register?branch=PG-NRD-01
        if (is_string($qrData) && str_contains($qrData, 'branch=')) {
            parse_str(parse_url($qrData, PHP_URL_QUERY) ?? '', $query);
            if (!empty($query['branch'])) {
                $branchCode = $query['branch'];
            }
        }

        $branch = \App\Models\Branch::where('status', 'ACTIVE')
            ->where(function ($q) use ($branchCode) {
                $q->where('code', $branchCode)
                  ->orWhere('id', $branchCode)
                  ->orWhere('qr_code_hash', $branchCode);
            })->first();

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or Inactive Branch QR Code. Please scan the official Rudra PG reception QR standee.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Branch QR code verified successfully.',
            'data' => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'city' => $branch->city,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'electricity_unit_rate' => (float) $branch->electricity_unit_rate,
                'manager_name' => $branch->manager_name,
                'manager_phone' => $branch->manager_phone,
                'qr_payload' => json_encode([
                    'branch_code' => $branch->code,
                    'branch_name' => $branch->name,
                    'type' => 'RUDRA_BRANCH_ONBOARDING',
                    'timestamp' => now()->timestamp,
                ]),
            ],
        ]);
    }

    public function activeBranchesList(): JsonResponse
    {
        $branches = \App\Models\Branch::where('status', 'ACTIVE')->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'code' => $b->code,
                'name' => $b->name,
                'city' => $b->city,
                'address' => $b->address,
                'phone' => $b->phone,
                'manager_name' => $b->manager_name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();

        if ($student) {
            $hasActiveAllocation = \App\Models\RoomAllocation::where('student_id', $student->id)
                ->where('status', 'ACTIVE')
                ->exists();

            if ($hasActiveAllocation) {
                $student->update(['status' => 'DELETION_REQUESTED']);

                \App\Models\Complaint::create([
                    'student_id' => $student->id,
                    'branch_id' => $student->branch_id,
                    'room_id' => $student->room_id,
                    'ticket_number' => 'TKT-'.date('Y').'-'.rand(1000, 9999),
                    'category' => 'GENERAL',
                    'subject' => 'Resident Account Deletion & Exit Request',
                    'description' => 'Resident initiated account deletion via mobile app. Notice period and security deposit settlement required.',
                    'status' => 'OPEN',
                    'priority' => 'HIGH',
                ]);
            } else {
                $student->update([
                    'status' => 'ARCHIVED',
                    'pan_number' => null,
                    'aadhaar_number' => null,
                    'emergency_contact' => null,
                ]);
            }
        }

        // Revoke all tokens and soft-delete user
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Account deletion request processed successfully. Personal session tokens have been revoked.',
        ]);
    }

    public function viewDocument(Request $request, int $id): \Symfony\Component\HttpFoundation\Response
    {
        $student = $this->studentService->getProfile($request->user());
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Resident profile not found.'], 404);
        }

        $doc = \App\Models\StudentDocument::where('student_id', $student->id)->findOrFail($id);
        $cleanPath = ltrim(str_replace('storage/', '', $doc->file_path), '/');

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($cleanPath);
        }
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($cleanPath);
        }

        return response()->json(['status' => 'error', 'message' => 'Document not found.'], 404);
    }
}
