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
        $student = $this->studentService->getProfile($request->user());
        $payment = $this->studentService->uploadPaymentProof($student, $request->validated());

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
}
