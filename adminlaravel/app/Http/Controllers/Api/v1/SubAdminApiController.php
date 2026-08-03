<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubAdmin\ApproveRegistrationRequest;
use App\Http\Resources\ComplaintResource;
use App\Http\Resources\ElectricityReadingResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\StudentResource;
use App\Services\SubAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubAdminApiController extends Controller
{
    public function __construct(protected SubAdminService $subAdminService) {}

    public function pendingVerifications(Request $request): JsonResponse
    {
        $requests = $this->subAdminService->getPendingVerifications($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    public function approveRegistration(ApproveRegistrationRequest $request, int $id): JsonResponse
    {
        $student = $this->subAdminService->approveRegistration($id, $request->validated(), $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Resident registration approved and bed allocated.',
            'data' => new StudentResource($student),
        ]);
    }

    public function rejectRegistration(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $req = $this->subAdminService->rejectRegistration($id, $request->input('reason'), $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Registration request rejected.',
            'data' => $req,
        ]);
    }

    public function roomMatrix(Request $request): JsonResponse
    {
        $matrix = $this->subAdminService->getBedMapMatrix($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $matrix,
        ]);
    }

    public function verifyPayment(Request $request, int $id): JsonResponse
    {
        $payment = $this->subAdminService->verifyPayment($id, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment verified successfully.',
            'data' => new PaymentResource($payment),
        ]);
    }

    public function auditElectricity(Request $request, int $id): JsonResponse
    {
        $reading = $this->subAdminService->auditElectricity($id, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Electricity reading approved.',
            'data' => new ElectricityReadingResource($reading),
        ]);
    }

    public function resolveComplaint(Request $request, int $id): JsonResponse
    {
        $complaint = $this->subAdminService->resolveComplaint($id, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Complaint marked as resolved.',
            'data' => new ComplaintResource($complaint),
        ]);
    }
}
