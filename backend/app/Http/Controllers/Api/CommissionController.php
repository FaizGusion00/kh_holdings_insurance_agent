<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Referral;
use App\Services\CommissionCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CommissionController extends Controller
{
    protected CommissionCalculationService $commissionService;

    public function __construct(CommissionCalculationService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Get all commissions for the authenticated agent.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2030',
            'status' => 'nullable|in:pending,calculated,paid',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Commission::where('user_id', $request->user()->id)
            ->with(['product', 'policy.member']);

        // Apply filters
        if ($request->has('month')) {
            $query->where('month', $request->month);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $commissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $commissions
        ]);
    }

    /**
     * Get commission summary for the authenticated agent.
     */
    public function getSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2030',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $agent = $request->user();
        $performance = $this->commissionService->calculateAgentPerformance($agent, $month, $year);

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }

    /**
     * Get commission history for the authenticated agent.
     */
    public function getHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $months = $request->input('months', 12);
        $agent = $request->user();

        $history = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < $months; $i++) {
            $month = $currentDate->month;
            $year = $currentDate->year;

            $performance = $this->commissionService->calculateAgentPerformance($agent, $month, $year);
            $history[] = $performance;

            $currentDate->subMonth();
        }

        return response()->json([
            'success' => true,
            'data' => array_reverse($history)
        ]);
    }

    /**
     * Get my commissions for the authenticated agent.
     */
    public function myCommissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2030',
            'status' => 'nullable|in:pending,calculated,paid',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Commission::where('user_id', $request->user()->id)
            ->with(['product', 'policy.member']);

        // Apply filters
        if ($request->has('month')) {
            $query->where('month', $request->month);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $commissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $commissions
        ]);
    }

    /**
     * Get agent's referral information.
     */
    public function getReferrals(Request $request)
    {
        $agent = $request->user();
        $referral = Referral::where('user_id', $agent->id)->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Referral information not found'
            ], 404);
        }

        // Get direct referrals
        $directReferrals = Referral::where('referrer_code', $agent->agent_code)
            ->with('agent')
            ->where('status', 'active')
            ->get();

        // Get upline information
        $uplineAgents = $referral->getUplineAgents();

        return response()->json([
            'success' => true,
            'data' => [
                'agent_code' => $agent->agent_code,
                'referrer_code' => $referral->referrer_code,
                'referral_level' => $referral->referral_level,
                'direct_referrals_count' => $referral->downline_count,
                'total_downlines_count' => $referral->total_downline_count,
                'direct_referrals' => $directReferrals,
                'upline_agents' => $uplineAgents,
                'upline_chain' => $referral->upline_chain
            ]
        ]);
    }

    /**
     * Get referral tree for the authenticated agent.
     */
    public function getReferralTree(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'depth' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $agent = $request->user();
        $depth = $request->input('depth', 3);

        $tree = $this->buildReferralTree($agent->agent_code, $depth);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    /**
     * Get downline agents.
     */
    public function getDownlines(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'nullable|integer|min:1|max:5',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $agent = $request->user();
        $level = $request->input('level');
        $perPage = $request->input('per_page', 15);

        $query = Referral::whereJsonContains('upline_chain', $agent->agent_code)
            ->with('agent')
            ->where('status', 'active');

        if ($level) {
            // Calculate the position in upline chain for the specified level
            $query->whereRaw('JSON_LENGTH(upline_chain) >= ?', [$level])
                  ->whereRaw('JSON_EXTRACT(upline_chain, "$[?]") = ?', [$level - 1, $agent->agent_code]);
        }

        $downlines = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $downlines
        ]);
    }

    /**
     * Get downline agents for a specified user (relative levels up to 5).
     */
    public function getUserDownlines(Request $request, int $userId)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'nullable|integer|min:1|max:5',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Target agent to inspect
        $targetAgent = \App\Models\User::find($userId);
        if (!$targetAgent || !$targetAgent->agent_code) {
            return response()->json([
                'success' => false,
                'message' => 'Agent not found'
            ], 404);
        }

        $agentCode = $targetAgent->agent_code;
        $level = $request->input('level');
        $perPage = $request->input('per_page', 15);

        $query = Referral::query()
            ->with('agent')
            ->where('status', 'active');

        if ($level && (int)$level === 1) {
            // Direct referrals
            $query->where('referrer_code', $agentCode);
        } else {
            // Any level under this agent (or a specific level > 1)
            $query->whereJsonContains('upline_chain', $agentCode);

            if ($level) {
                // Ensure the agent code is at the correct index of upline_chain (level-1)
                // JSON_EXTRACT(upline_chain, '$[index]') = agentCode
                $index = ((int)$level) - 1;
                $query->whereRaw('JSON_EXTRACT(upline_chain, "$[$index]") = ?', [$agentCode]);
            }
        }

        $downlines = $query->paginate($perPage);

        // Aggregate counts by level (1..5 relative to target agent)
        $byLevel = [1=>0,2=>0,3=>0,4=>0,5=>0];
        foreach ($downlines->items() as $ref) {
            // Determine relative level position in upline_chain
            $chain = is_array($ref->upline_chain) ? $ref->upline_chain : json_decode($ref->upline_chain, true);
            $pos = array_search($agentCode, $chain ?? [], true);
            if ($pos !== false) {
                $rel = $pos + 1; // index 0 => level 1
                if (isset($byLevel[$rel])) { $byLevel[$rel]++; }
            } elseif ($ref->referrer_code === $agentCode) {
                $byLevel[1]++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $downlines,
            'by_level_counts' => $byLevel,
            'agent' => [
                'id' => $targetAgent->id,
                'agent_code' => $targetAgent->agent_code,
                'name' => $targetAgent->name,
            ]
        ]);
    }

    /**
     * Get upline agents.
     */
    public function getUplines(Request $request)
    {
        $agent = $request->user();
        $referral = Referral::where('user_id', $agent->id)->first();

        if (!$referral || !$referral->upline_chain) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $uplineAgents = $referral->getUplineAgents();

        return response()->json([
            'success' => true,
            'data' => $uplineAgents
        ]);
    }

    /**
     * Calculate commission for a specific scenario (for testing purposes).
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:insurance_products,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annually,annually',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // This is a simulation, not actual commission creation
        $agent = $request->user();
        $referral = Referral::where('user_id', $agent->id)->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Agent referral information not found'
            ], 404);
        }

        // Get commission rules
        $commissionRules = \App\Models\ProductCommissionRule::where('product_id', $request->product_id)
            ->where('payment_frequency', $request->payment_frequency)
            ->where('is_active', true)
            ->orderBy('tier_level')
            ->get();

        if ($commissionRules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No commission rules found for this product'
            ], 404);
        }

        $uplineChain = $referral->upline_chain ?: [];
        array_unshift($uplineChain, $referral->agent_code);

        $simulatedCommissions = [];

        foreach ($commissionRules as $rule) {
            if ($rule->tier_level <= count($uplineChain)) {
                $tierAgentCode = $uplineChain[$rule->tier_level - 1];
                $commissionAmount = $rule->calculateCommission($request->amount);

                $simulatedCommissions[] = [
                    'tier_level' => $rule->tier_level,
                    'agent_code' => $tierAgentCode,
                    'commission_type' => $rule->commission_type,
                    'commission_value' => $rule->commission_value,
                    'commission_amount' => $commissionAmount,
                    'base_amount' => $request->amount
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'simulated_commissions' => $simulatedCommissions,
                'total_commission' => array_sum(array_column($simulatedCommissions, 'commission_amount'))
            ]
        ]);
    }

    /**
     * Build referral tree recursively.
     */
    private function buildReferralTree($agentCode, $depth, $currentDepth = 0)
    {
        if ($currentDepth >= $depth) {
            return null;
        }

        $agent = \App\Models\User::where('agent_code', $agentCode)->first();
        if (!$agent) {
            return null;
        }

        $directReferrals = Referral::where('referrer_code', $agentCode)
            ->with('agent')
            ->where('status', 'active')
            ->get();

        $children = [];
        foreach ($directReferrals as $referral) {
            $child = $this->buildReferralTree($referral->agent_code, $depth, $currentDepth + 1);
            if ($child) {
                $children[] = $child;
            }
        }

        return [
            'agent_code' => $agent->agent_code,
            'agent_name' => $agent->name,
            'level' => $currentDepth + 1,
            'direct_referrals_count' => $directReferrals->count(),
            'children' => $children
        ];
    }
}
