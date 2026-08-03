<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use App\Services\SuperAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAdminApiController extends Controller
{
    public function __construct(protected SuperAdminService $superAdminService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $kpis = $this->superAdminService->getDashboardKpis();

        return response()->json([
            'status' => 'success',
            'data' => $kpis,
        ]);
    }

    public function createBranch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'manager_name' => 'required|string|max:255',
            'manager_phone' => 'required|string|max:20',
            'electricity_unit_rate' => 'required|numeric|min:0',
        ]);

        $branch = $this->superAdminService->createBranch($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully.',
            'data' => new BranchResource($branch),
        ], 201);
    }

    public function createSubAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);

        $user = $this->superAdminService->createSubAdmin($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function updateSubAdmin(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$id,
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
        ]);

        $user = $this->superAdminService->updateSubAdmin($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function toggleSubAdminStatus(int $id): JsonResponse
    {
        $user = $this->superAdminService->toggleSubAdminStatus($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin status updated to '.$user->status,
            'data' => new UserResource($user),
        ]);
    }

    public function resetSubAdminPassword(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['password' => 'required|string|min:6']);
        $user = $this->superAdminService->resetSubAdminPassword($id, $validated['password']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin password reset successfully.',
        ]);
    }

    public function assignSubAdmin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $this->superAdminService->assignSubAdmin($validated['user_id'], $validated['branch_id']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Admin assigned to branch successfully.',
        ]);
    }

    public function auditLogs(Request $request): JsonResponse
    {
        $logs = $this->superAdminService->getAuditLogs();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
