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
        $student = $this->studentService->registerFromQr($request->validated());

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
}
