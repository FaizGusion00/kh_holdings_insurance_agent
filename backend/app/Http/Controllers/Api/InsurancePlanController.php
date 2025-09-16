<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsurancePlan;
use App\Models\CommissionRate;
use Illuminate\Http\Request;

/**
 * Insurance Plan Controller for API
 * 
 * Handles insurance plan listings, details, and commission rates
 */
class InsurancePlanController extends Controller
{
    /**
     * Display a listing of insurance plans
     */
    public function index(Request $request)
    {
        try {
            $query = InsurancePlan::active();

            // Filter by age if provided
            if ($request->has('age')) {
                $age = (int) $request->age;
                $query->byAge($age);
            }

            // Search by plan name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('plan_name', 'LIKE', "%{$search}%");
            }

            $plans = $query->with('commissionRates')->get();

            // Calculate prices for different payment modes
            $plans->each(function ($plan) {
                $plan->pricing = [
                    'monthly' => [
                        'base_price' => $plan->monthly_price,
                        'commitment_fee' => $plan->commitment_fee,
                        'total_price' => $plan->getTotalPriceByMode('monthly')
                    ],
                    'quarterly' => [
                        'price' => $plan->quarterly_price
                    ],
                    'semi_annually' => [
                        'price' => $plan->semi_annually_price
                    ],
                    'annually' => [
                        'price' => $plan->annually_price
                    ],
                    'available_modes' => $plan->getAvailablePaymentModes()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'plans' => $plans,
                    'total' => $plans->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch insurance plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified insurance plan
     */
    public function show($id)
    {
        try {
            $plan = InsurancePlan::active()->with('commissionRates')->findOrFail($id);

            // Add detailed pricing information
            $plan->pricing = [
                'monthly' => [
                    'base_price' => $plan->monthly_price,
                    'commitment_fee' => $plan->commitment_fee,
                    'total_price' => $plan->getTotalPriceByMode('monthly')
                ],
                'quarterly' => [
                    'price' => $plan->quarterly_price
                ],
                'semi_annually' => [
                    'price' => $plan->semi_annually_price
                ],
                'annually' => [
                    'price' => $plan->annually_price
                ],
                'available_modes' => $plan->getAvailablePaymentModes()
            ];

            // Format benefits and terms for frontend display
            $plan->formatted_benefits = $plan->benefits ?? [];
            $plan->formatted_terms = $plan->terms_conditions ?? [];

            return response()->json([
                'status' => 'success',
                'data' => ['plan' => $plan]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance plan not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch insurance plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commission rates for a specific plan
     */
    public function getCommissionRates($id, Request $request)
    {
        try {
            $plan = InsurancePlan::active()->findOrFail($id);
            
            $query = CommissionRate::where('insurance_plan_id', $id);

            // Filter by payment mode if provided
            if ($request->has('payment_mode')) {
                $query->where('payment_mode', $request->payment_mode);
            }

            $commissionRates = $query->orderBy('tier_level')->get();

            // Group by payment mode for easier frontend consumption
            $groupedRates = $commissionRates->groupBy('payment_mode');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'plan' => $plan->only(['id', 'plan_name', 'plan_code']),
                    'commission_rates' => $groupedRates,
                    'commission_structure' => [
                        'T1' => 'RM10 - UNTUK SETIAP PELANGGAN',
                        'T2' => 'RM2 - UNTUK SETIAP JUALAN AGENT', 
                        'T3' => 'RM2 - UNTUK SETIAP JUALAN AGENT',
                        'T4' => 'RM1 - UNTUK SETIAP JUALAN AGENT',
                        'T5' => 'RM0.75 - UNTUK SETIAP JUALAN AGENT'
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance plan not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch commission rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check plan eligibility for a user
     */
    public function checkEligibility(Request $request, $id)
    {
        try {
            $plan = InsurancePlan::active()->findOrFail($id);
            
            $age = $request->input('age');
            if (!$age) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Age is required for eligibility check'
                ], 400);
            }

            $isEligible = $plan->isEligibleByAge($age);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'eligible' => $isEligible,
                    'plan' => $plan->only(['id', 'plan_name', 'min_age', 'max_age']),
                    'user_age' => $age,
                    'message' => $isEligible 
                        ? 'You are eligible for this plan' 
                        : "This plan is for ages {$plan->min_age}-{$plan->max_age}. You are {$age} years old."
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insurance plan not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check eligibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}