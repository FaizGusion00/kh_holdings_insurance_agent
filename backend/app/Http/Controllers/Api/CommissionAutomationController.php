<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommissionAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionAutomationController extends Controller
{
    protected $commissionAutomationService;

    public function __construct(CommissionAutomationService $commissionAutomationService)
    {
        $this->commissionAutomationService = $commissionAutomationService;
    }

    /**
     * Process commission for medical insurance registration
     */
    public function processMedicalInsuranceCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:medical_insurance_registrations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->commissionAutomationService->processMedicalInsuranceCommission($request->registration_id);

        return response()->json($result);
    }

    /**
     * Process commission for policy payment
     */
    public function processPolicyCommission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'policy_id' => 'required|exists:member_policies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->commissionAutomationService->processPolicyCommission($request->policy_id);

        return response()->json($result);
    }

    /**
     * Sync pending commissions for an agent
     */
    public function syncPendingCommissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $processed = $this->commissionAutomationService->syncPendingCommissionsForAgent($request->agent_id);

        return response()->json([
            'success' => true,
            'processed_count' => $processed,
            'message' => "Successfully synced {$processed} pending commissions"
        ]);
    }

    /**
     * Get commission summary for an agent
     */
    public function getCommissionSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $months = $request->months ?? 12;
        $summary = $this->commissionAutomationService->getCommissionSummary($request->agent_id, $months);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
