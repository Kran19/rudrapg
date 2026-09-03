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

    public function submitElectricityReading(SubmitElectricityReadingRequest $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        $reading = $this->studentService->submitElectricityReading($student, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity sub-meter reading logged successfully.',
            'data' => new ElectricityReadingResource($reading),
        ]);
    }

    public function createComplaint(CreateComplaintRequest $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
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
        $notices = $this->studentService->getNotices($student);

        return response()->json([
            'status' => 'success',
            'data' => AnnouncementResource::collection($notices),
        ]);
    }

    public function electricityReadings(Request $request): JsonResponse
    {
        $student = $this->studentService->getProfile($request->user());
        $readings = \App\Models\ElectricityReading::where('student_id', $student->id)
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
        $complaints = \App\Models\Complaint::where('student_id', $student->id)
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
}
