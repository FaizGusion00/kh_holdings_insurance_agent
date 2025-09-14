<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProduct;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Get all active insurance plans
     */
    public function index()
    {
        $plans = InsuranceProduct::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get a specific plan by ID
     */
    public function show($id)
    {
        $plan = InsuranceProduct::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Get plan pricing for different frequencies
     */
    public function getPricing($id)
    {
        $plan = InsuranceProduct::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        $pricing = [
            'monthly' => $plan->base_price,
            'quarterly' => $plan->base_price * 3,
            'semi_annually' => $plan->base_price * 6,
            'annually' => $plan->base_price * 12,
        ];

        // Use custom pricing if available in coverage_details
        if (isset($plan->coverage_details['pricing'])) {
            $pricing = array_merge($pricing, $plan->coverage_details['pricing']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'pricing' => $pricing
            ]
        ]);
    }
}
