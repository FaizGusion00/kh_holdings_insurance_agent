<?php

namespace App\Services;

use App\Models\CommissionRule;
use App\Models\Commission;
use App\Models\User;
use App\Models\Member;
use App\Models\MemberPolicy;
use App\Models\MedicalInsuranceRegistration;
use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionAutomationService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Process commission for medical insurance payment success
     */
    public function processMedicalInsuranceCommission($registrationId)
    {
        try {
            $registration = MedicalInsuranceRegistration::with(['agent'])
                ->findOrFail($registrationId);

            if ($registration->status !== 'active') {
                Log::warning("Registration {$registrationId} is not active, skipping commission processing");
                return false;
            }

            DB::beginTransaction();

            $totalCommissions = 0;
            $processedCount = 0;

            // Get all customers from the registration
            $customers = $registration->getAllCustomers();
            
            Log::info("Processing commission for {$registrationId} with " . count($customers) . " customers");

            // Process commission for each customer in the registration
            foreach ($customers as $customer) {
                $planName = $this->getPlanNameFromCustomer($customer);
                $paymentFrequency = $this->getPaymentFrequencyFromCustomer($customer);
                
                if (!$planName || !$paymentFrequency) {
                    Log::warning("Could not determine plan or frequency for customer", [
                        'customer_type' => $customer['type'],
                        'plan_type' => $customer['plan_type'] ?? 'unknown',
                        'payment_mode' => $customer['payment_mode'] ?? 'unknown'
                    ]);
                    continue;
                }

                Log::info("Processing commission for customer", [
                    'customer_type' => $customer['type'],
                    'plan_name' => $planName,
                    'payment_frequency' => $paymentFrequency
                ]);

                // Get the agent's network levels (up to 5 levels)
                $networkLevels = $this->getAgentNetworkLevels($registration->agent_id);
                
                // Process commission for each network level
                foreach ($networkLevels as $level => $agentId) {
                    $commission = $this->createCommissionForAgent(
                        $agentId,
                        $planName,
                        $paymentFrequency,
                        $level,
                        $registration->id,
                        $customer['type'] . '_' . $customer['nric'] // Use customer type + NRIC as identifier
                    );
                    
                    if ($commission) {
                        $totalCommissions += $commission->commission_amount;
                        $processedCount++;
                        
                        Log::info("Created commission for agent", [
                            'agent_id' => $agentId,
                            'tier_level' => $level,
                            'commission_amount' => $commission->commission_amount,
                            'plan_name' => $planName
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info("Processed {$processedCount} commissions totaling RM " . number_format($totalCommissions, 2) . " for registration {$registrationId}");

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'total_amount' => $totalCommissions,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process medical insurance commission for registration {$registrationId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process commission for policy payment success
     */
    public function processPolicyCommission($policyId)
    {
        try {
            $policy = MemberPolicy::with(['member.user', 'product'])
                ->findOrFail($policyId);

            if ($policy->status !== 'active') {
                Log::warning("Policy {$policyId} is not active, skipping commission processing");
                return false;
            }

            DB::beginTransaction();

            $totalCommissions = 0;
            $processedCount = 0;

            // Get the agent's network levels (up to 5 levels)
            $networkLevels = $this->getAgentNetworkLevels($policy->member->user_id);
            
            // Process commission for each network level
            foreach ($networkLevels as $level => $agentId) {
                $commission = $this->createCommissionForAgent(
                    $agentId,
                    $policy->product->name,
                    $policy->payment_frequency,
                    $level,
                    $policy->id,
                    $policy->member_id
                );
                
                if ($commission) {
                    $totalCommissions += $commission->commission_amount;
                    $processedCount++;
                }
            }

            DB::commit();

            Log::info("Processed {$processedCount} commissions totaling RM " . number_format($totalCommissions, 2) . " for policy {$policyId}");

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'total_amount' => $totalCommissions,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process policy commission for policy {$policyId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get agent network levels (up to 5 levels)
     */
    protected function getAgentNetworkLevels($agentId)
    {
        $networkLevels = [];
        $currentAgentId = $agentId;
        $level = 1;

        while ($currentAgentId && $level <= 5) {
            $networkLevels[$level] = $currentAgentId;
            
            // Get the referrer (parent agent)
            $member = Member::where('user_id', $currentAgentId)->first();
            if ($member && $member->referrer_id) {
                $currentAgentId = $member->referrer_id;
            } else {
                break;
            }
            
            $level++;
        }

        return $networkLevels;
    }

    /**
     * Create commission for a specific agent
     */
    protected function createCommissionForAgent($agentId, $planName, $paymentFrequency, $tierLevel, $sourceId, $customerId)
    {
        try {
            // Find the appropriate commission rule
            $rule = CommissionRule::active()
                ->where('plan_name', $planName)
                ->where('payment_frequency', $paymentFrequency)
                ->where('tier_level', $tierLevel)
                ->first();

            if (!$rule) {
                Log::warning("No commission rule found for plan: {$planName}, frequency: {$paymentFrequency}, tier: {$tierLevel}");
                return null;
            }

            // Calculate commission amount
            $commissionAmount = $rule->calculateCommission();
            
            if ($commissionAmount <= 0) {
                Log::warning("Commission amount is zero or negative for agent {$agentId}, tier {$tierLevel}");
                return null;
            }

            // Create commission record
            $commission = Commission::create([
                'user_id' => $agentId,
                'product_id' => null, // Will be set based on plan type
                'policy_id' => $sourceId,
                'tier_level' => $tierLevel,
                'commission_type' => $this->getCommissionType($tierLevel),
                'base_amount' => $rule->base_amount,
                'commission_percentage' => $rule->commission_percentage,
                'commission_amount' => $commissionAmount,
                'payment_frequency' => $paymentFrequency,
                'month' => now()->month,
                'year' => now()->year,
                'status' => 'pending',
                'notes' => "Auto-generated commission for {$planName} - Tier {$tierLevel}",
            ]);

            // Process payment to wallet
            $this->walletService->processCommissionPayment($commission->id);
            
            // Update commission status to paid
            $commission->update(['status' => 'paid']);

            return $commission;

        } catch (\Exception $e) {
            Log::error("Failed to create commission for agent {$agentId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get commission type based on tier level
     */
    protected function getCommissionType($tierLevel)
    {
        switch ($tierLevel) {
            case 1:
                return 'direct';
            case 2:
                return 'direct';
            default:
                return 'referral';
        }
    }

    /**
     * Get plan name from customer data
     */
    protected function getPlanNameFromCustomer($customer)
    {
        $planType = $customer['plan_type'] ?? '';
        
        // Map plan types to commission rule plan names
        $planMapping = [
            'senior_care_gold' => 'Senior Care Plan Gold 270',
            'senior_care_diamond' => 'Senior Care Plan Diamond 370',
            'medical_card' => 'Medical Card',
            'medplan_coop' => 'MediPlan Coop',
        ];
        
        return $planMapping[$planType] ?? 'Senior Care Plan Gold 270';
    }

    /**
     * Get payment frequency from customer data
     */
    protected function getPaymentFrequencyFromCustomer($customer)
    {
        $paymentMode = $customer['payment_mode'] ?? '';
        
        // Map payment modes to commission rule frequencies
        $frequencyMapping = [
            'monthly' => 'monthly',
            'quarterly' => 'quarterly',
            'half_yearly' => 'semi_annually',
            'yearly' => 'annually',
        ];
        
        return $frequencyMapping[$paymentMode] ?? 'monthly';
    }

    /**
     * Sync all pending commissions for a specific agent
     */
    public function syncPendingCommissionsForAgent($agentId)
    {
        try {
            $pendingCommissions = Commission::where('user_id', $agentId)
                ->where('status', 'pending')
                ->get();

            $processed = 0;
            foreach ($pendingCommissions as $commission) {
                $this->walletService->processCommissionPayment($commission->id);
                $processed++;
            }

            return $processed;

        } catch (\Exception $e) {
            Log::error("Failed to sync pending commissions for agent {$agentId}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get commission summary for an agent
     */
    public function getCommissionSummary($agentId, $months = 12)
    {
        $summary = [
            'total_commission' => 0,
            'pending_commission' => 0,
            'paid_commission' => 0,
            'monthly_breakdown' => [],
        ];

        // Get total commissions
        $summary['total_commission'] = Commission::where('user_id', $agentId)->sum('commission_amount');
        $summary['pending_commission'] = Commission::where('user_id', $agentId)->where('status', 'pending')->sum('commission_amount');
        $summary['paid_commission'] = Commission::where('user_id', $agentId)->where('status', 'paid')->sum('commission_amount');

        // Get monthly breakdown
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            
            $amount = Commission::where('user_id', $agentId)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('commission_amount');
            
            $summary['monthly_breakdown'][] = [
                'month' => $month,
                'amount' => $amount,
                'formatted_month' => $date->format('M Y'),
            ];
        }

        return $summary;
    }
}
