<?php

namespace App\Services;

use App\Models\User;
use App\Models\Commission;
use App\Models\ProductCommissionRule;
use App\Models\PaymentTransaction;
use App\Models\MemberPolicy;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionCalculationService
{
    /**
     * Calculate commission for a specific payment transaction.
     */
    public function calculateCommissionForPayment(PaymentTransaction $transaction): array
    {
        try {
            DB::beginTransaction();

            $policy = $transaction->policy;
            $product = $policy->product;
            $agent = $policy->member->agent;
            
            Log::info("Calculating commission for payment", [
                'transaction_id' => $transaction->id,
                'product_type' => $product->product_type,
                'amount' => $transaction->amount,
                'agent_id' => $agent->id
            ]);

            // Get commission rules for this product and payment frequency
            $commissionRules = ProductCommissionRule::where('product_id', $product->id)
                ->where('payment_frequency', $product->payment_frequency)
                ->where('is_active', true)
                ->orderBy('tier_level')
                ->get();

            if ($commissionRules->isEmpty()) {
                Log::warning("No commission rules found for product", [
                    'product_id' => $product->id,
                    'payment_frequency' => $product->payment_frequency
                ]);
                DB::rollback();
                return ['success' => false, 'message' => 'No commission rules found'];
            }

            // Get the upline chain for the agent
            $referral = Referral::where('user_id', $agent->id)->first();
            $uplineChain = $referral ? $referral->upline_chain : [];
            
            // Add the current agent to the beginning of the chain
            array_unshift($uplineChain, $referral->agent_code);

            $commissionsCreated = [];
            $totalCommissions = 0;

            foreach ($commissionRules as $rule) {
                $tierLevel = $rule->tier_level;
                
                // Check if we have enough levels in the upline chain
                if ($tierLevel > count($uplineChain)) {
                    Log::debug("Tier level exceeds upline chain length", [
                        'tier_level' => $tierLevel,
                        'upline_chain_length' => count($uplineChain)
                    ]);
                    continue;
                }

                // Get the agent at this tier level
                $tierAgentCode = $uplineChain[$tierLevel - 1];
                $tierAgent = User::where('agent_code', $tierAgentCode)->first();

                if (!$tierAgent || $tierAgent->status !== 'active') {
                    Log::debug("Tier agent not found or inactive", [
                        'tier_level' => $tierLevel,
                        'agent_code' => $tierAgentCode
                    ]);
                    continue;
                }

                // Calculate commission amount
                $commissionAmount = $rule->calculateCommission($transaction->amount);

                if ($commissionAmount <= 0) {
                    Log::debug("Commission amount is zero or negative", [
                        'tier_level' => $tierLevel,
                        'commission_amount' => $commissionAmount
                    ]);
                    continue;
                }

                // Create commission record
                $commission = Commission::create([
                    'user_id' => $tierAgent->id,
                    'referrer_id' => $tierLevel === 1 ? null : $agent->id,
                    'product_id' => $product->id,
                    'policy_id' => $policy->id,
                    'tier_level' => $tierLevel,
                    'commission_type' => $tierLevel === 1 ? 'direct' : 'indirect',
                    'base_amount' => $transaction->amount,
                    'commission_percentage' => $rule->commission_type === 'percentage' ? $rule->commission_value : 0,
                    'commission_amount' => $commissionAmount,
                    'payment_frequency' => $product->payment_frequency,
                    'month' => Carbon::now()->month,
                    'year' => Carbon::now()->year,
                    'status' => 'calculated',
                ]);

                $commissionsCreated[] = $commission;
                $totalCommissions += $commissionAmount;

                Log::info("Commission created", [
                    'commission_id' => $commission->id,
                    'tier_agent_id' => $tierAgent->id,
                    'tier_level' => $tierLevel,
                    'amount' => $commissionAmount
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'commissions_created' => count($commissionsCreated),
                'total_commission_amount' => $totalCommissions,
                'commissions' => $commissionsCreated
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Commission calculation failed", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Commission calculation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process monthly commission calculations for all agents.
     */
    public function processMonthlyCommissions($month = null, $year = null): array
    {
        $month = $month ?: Carbon::now()->month;
        $year = $year ?: Carbon::now()->year;

        try {
            Log::info("Starting monthly commission processing", [
                'month' => $month,
                'year' => $year
            ]);

            // Get all calculated commissions for the month
            $commissions = Commission::where('month', $month)
                ->where('year', $year)
                ->where('status', 'calculated')
                ->with(['agent', 'product'])
                ->get();

            if ($commissions->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No commissions to process for this period',
                    'processed_count' => 0
                ];
            }

            $processedCount = 0;
            $totalAmount = 0;

            DB::beginTransaction();

            foreach ($commissions as $commission) {
                // Update commission status to processed
                $commission->update([
                    'status' => 'pending',
                    'payment_date' => Carbon::now()->endOfMonth()
                ]);

                // Update agent's total commission earned
                $commission->agent->increment('total_commission_earned', $commission->commission_amount);

                $processedCount++;
                $totalAmount += $commission->commission_amount;
            }

            DB::commit();

            Log::info("Monthly commission processing completed", [
                'month' => $month,
                'year' => $year,
                'processed_count' => $processedCount,
                'total_amount' => $totalAmount
            ]);

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'total_amount' => $totalAmount,
                'month' => $month,
                'year' => $year
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Monthly commission processing failed", [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Monthly commission processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate agent performance metrics.
     */
    public function calculateAgentPerformance(User $agent, $month = null, $year = null): array
    {
        $month = $month ?: Carbon::now()->month;
        $year = $year ?: Carbon::now()->year;

        // Direct commissions (T1)
        $directCommissions = Commission::where('user_id', $agent->id)
            ->where('tier_level', 1)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('commission_amount');

        // Indirect commissions (T2-T5)
        $indirectCommissions = Commission::where('user_id', $agent->id)
            ->where('tier_level', '>', 1)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('commission_amount');

        // Total commissions
        $totalCommissions = $directCommissions + $indirectCommissions;

        // Number of direct referrals
        $directReferrals = Referral::where('referrer_code', $agent->agent_code)
            ->where('status', 'active')
            ->count();

        // Total downlines
        $agentReferral = Referral::where('user_id', $agent->id)->first();
        $totalDownlines = $agentReferral ? $agentReferral->total_downline_count : 0;

        return [
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'agent_code' => $agent->agent_code,
            'month' => $month,
            'year' => $year,
            'direct_commissions' => $directCommissions,
            'indirect_commissions' => $indirectCommissions,
            'total_commissions' => $totalCommissions,
            'direct_referrals' => $directReferrals,
            'total_downlines' => $totalDownlines,
            'target_achievement' => $agent->monthly_commission_target > 0 
                ? round(($totalCommissions / $agent->monthly_commission_target) * 100, 2) 
                : 0
        ];
    }

    /**
     * Get commission summary for a specific period.
     */
    public function getCommissionSummary($month = null, $year = null): array
    {
        $month = $month ?: Carbon::now()->month;
        $year = $year ?: Carbon::now()->year;

        $summary = Commission::where('month', $month)
            ->where('year', $year)
            ->selectRaw('
                COUNT(*) as total_commissions,
                SUM(commission_amount) as total_amount,
                SUM(CASE WHEN tier_level = 1 THEN commission_amount ELSE 0 END) as direct_commissions,
                SUM(CASE WHEN tier_level > 1 THEN commission_amount ELSE 0 END) as indirect_commissions,
                COUNT(DISTINCT user_id) as unique_agents,
                AVG(commission_amount) as average_commission
            ')
            ->first();

        return [
            'month' => $month,
            'year' => $year,
            'summary' => $summary
        ];
    }

    /**
     * Recalculate commissions for a specific policy (for corrections).
     */
    public function recalculateCommissionsForPolicy(MemberPolicy $policy): array
    {
        try {
            DB::beginTransaction();

            // Delete existing commissions for this policy
            Commission::where('policy_id', $policy->id)->delete();

            // Get all completed payment transactions for this policy
            $transactions = PaymentTransaction::where('policy_id', $policy->id)
                ->where('status', 'completed')
                ->get();

            $results = [];
            
            foreach ($transactions as $transaction) {
                $result = $this->calculateCommissionForPayment($transaction);
                $results[] = $result;
            }

            DB::commit();

            return [
                'success' => true,
                'policy_id' => $policy->id,
                'transactions_processed' => count($transactions),
                'results' => $results
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Commission recalculation failed", [
                'policy_id' => $policy->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Commission recalculation failed: ' . $e->getMessage()
            ];
        }
    }
}
