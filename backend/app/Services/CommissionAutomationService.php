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
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionAutomationService
{
    protected $walletService;
    protected $notificationService;

    public function __construct(WalletService $walletService, NotificationService $notificationService)
    {
        $this->walletService = $walletService;
        $this->notificationService = $notificationService;
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

                // Determine payer's user (the customer who becomes an agent) and compute uplines
                $payerUser = User::where('nric', $customer['nric'] ?? null)->first();
                $networkLevels = $payerUser
                    ? $this->getUplinesFromPayer($payerUser)
                    : $this->getAgentNetworkLevels($registration->agent_id);
                
                // Process commission for each network level (tier1 = immediate upline)
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

                // Additional commission for Medical Card add-on (fixed per-tier amounts)
                if (!empty($customer['medical_card_type'])) {
                    foreach ($networkLevels as $level => $agentId) {
                        $fixed = $this->getMedicalCardFixedAmountByTier($level);
                        if ($fixed > 0) {
                            $mc = $this->createFixedCommission(
                                $agentId,
                                'Medical Card',
                                $paymentFrequency,
                                $level,
                                $registration->id,
                                $customer['type'] . '_' . $customer['nric'] . '_card',
                                $fixed
                            );
                            if ($mc) {
                                $totalCommissions += $mc->commission_amount;
                                $processedCount++;
                            }
                        }
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

        // Level 1 is the agent themselves
        while ($currentAgentId && $level <= 5) {
            $networkLevels[$level] = $currentAgentId;

            $referral = \App\Models\Referral::where('user_id', $currentAgentId)->first();
            if ($referral && $referral->referrer_code) {
                $upline = User::where('agent_code', $referral->referrer_code)->first();
                $currentAgentId = $upline?->id;
            } else {
                break;
            }

            $level++;
        }

        return $networkLevels;
    }

    /**
     * Get uplines from a payer (newly created agent). Tier1 is immediate upline, etc.
     */
    protected function getUplinesFromPayer(User $payer): array
    {
        $levels = [];
        $referral = \App\Models\Referral::where('user_id', $payer->id)->first();
        $currentCode = $referral?->referrer_code;
        $tier = 1;
        while ($currentCode && $tier <= 5) {
            $upline = User::where('agent_code', $currentCode)->first();
            if (!$upline) { break; }
            $levels[$tier] = $upline->id;
            $uplineRef = \App\Models\Referral::where('user_id', $upline->id)->first();
            $currentCode = $uplineRef?->referrer_code;
            $tier++;
        }
        return $levels;
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

            // Determine base amount from plan & frequency (premium portion)
            $planModel = \App\Models\MedicalInsurancePlan::where('name', $planName)->first();
            $baseAmount = $planModel ? $planModel->getPriceByFrequency($paymentFrequency) : 0;

            if (!$rule) {
                // Fallback defaults when rules not set in DB
                $defaultPercentages = [1 => 10.0, 2 => 5.0, 3 => 3.0, 4 => 2.0, 5 => 1.0];
                $percentage = $defaultPercentages[$tierLevel] ?? 0.0;
                $computedAmount = ($baseAmount * $percentage) / 100.0;
                if ($computedAmount <= 0) {
                    Log::warning("No commission rule and computed amount is zero for plan: {$planName}, freq: {$paymentFrequency}, tier: {$tierLevel}");
                    return null;
                }

                $commission = Commission::create([
                    'user_id' => $agentId,
                    'product_id' => null,
                    'policy_id' => $sourceId,
                    'tier_level' => $tierLevel,
                    'commission_type' => $this->getCommissionType($tierLevel),
                    'base_amount' => $baseAmount,
                    'commission_percentage' => $percentage,
                    'commission_amount' => $computedAmount,
                    'payment_frequency' => $paymentFrequency,
                    'month' => now()->month,
                    'year' => now()->year,
                    'status' => 'pending',
                    'notes' => "Auto-generated commission (default rule) for {$planName} - Tier {$tierLevel}",
                ]);

                $this->walletService->processCommissionPayment($commission->id);
                $commission->update(['status' => 'paid']);
                $this->notificationService->createCommissionNotification(
                    $agentId,
                    $computedAmount,
                    "Commission earned from {$planName} - Tier {$tierLevel}",
                    "/profile?tab=referrer&subtab=commission"
                );

                return $commission;
            }

            // Calculate commission amount
            $commissionAmount = $rule->calculateCommission($baseAmount);
            
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
                'base_amount' => $baseAmount,
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

            // Create notification for commission earned
            $this->notificationService->createCommissionNotification(
                $agentId,
                $commissionAmount,
                "Commission earned from {$planName} - Tier {$tierLevel}",
                "/profile?tab=referrer&subtab=commission"
            );

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
            // allow both canonical names and friendly keys
            'Senior Care Plan Gold 270' => 'Senior Care Plan Gold 270',
            'Senior Care Plan Diamond 370' => 'Senior Care Plan Diamond 370',
            'senior_care_gold' => 'Senior Care Plan Gold 270',
            'senior_care_diamond' => 'Senior Care Plan Diamond 370',
            'medical_card' => 'Medical Card',
            'medplan_coop' => 'MediPlan Coop',
        ];
        
        return $planMapping[$planType] ?? $planType;
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
            'semi_annually' => 'semi_annually',
            'yearly' => 'annually',
            'annually' => 'annually',
        ];
        
        return $frequencyMapping[$paymentMode] ?? 'monthly';
    }

    /**
     * Fixed Medical Card amounts by tier (RM) based on provided guide.
     * T1=10, T2=2, T3=2, T4=1, T5=0.75
     */
    protected function getMedicalCardFixedAmountByTier(int $tier): float
    {
        return match($tier) {
            1 => 10.0,
            2 => 2.0,
            3 => 2.0,
            4 => 1.0,
            5 => 0.75,
            default => 0.0,
        };
    }

    /**
     * Create a fixed-amount commission record and pay it out immediately.
     */
    protected function createFixedCommission($agentId, string $planName, string $paymentFrequency, int $tierLevel, $sourceId, $customerId, float $amount)
    {
        if ($amount <= 0) { return null; }
        $commission = Commission::create([
            'user_id' => $agentId,
            'product_id' => null,
            'policy_id' => $sourceId,
            'tier_level' => $tierLevel,
            'commission_type' => $this->getCommissionType($tierLevel),
            'base_amount' => 0,
            'commission_percentage' => 0,
            'commission_amount' => $amount,
            'payment_frequency' => $paymentFrequency,
            'month' => now()->month,
            'year' => now()->year,
            'status' => 'pending',
            'notes' => "Fixed commission for {$planName} - Tier {$tierLevel}",
        ]);

        $this->walletService->processCommissionPayment($commission->id);
        $commission->update(['status' => 'paid']);
        $this->notificationService->createCommissionNotification(
            $agentId,
            $amount,
            "Commission earned (fixed) from {$planName} - Tier {$tierLevel}",
            "/profile?tab=referrer&subtab=commission"
        );

        return $commission;
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

    /**
     * Process renewal commission for a policy
     */
    public function processRenewalCommission($policyId, $policyType = 'member')
    {
        try {
            if ($policyType === 'member') {
                return $this->processPolicyCommission($policyId);
            } else {
                // For medical insurance, we need the registration ID
                $policy = MedicalInsurancePolicy::find($policyId);
                if ($policy) {
                    return $this->processMedicalInsuranceCommission($policy->registration_id);
                }
            }

            return [
                'success' => false,
                'error' => 'Invalid policy type or policy not found'
            ];

        } catch (\Exception $e) {
            Log::error("Failed to process renewal commission for policy {$policyId}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get policy expiration summary for an agent
     */
    public function getPolicyExpirationSummary($agentId)
    {
        $summary = [
            'expiring_soon' => 0,
            'expired' => 0,
            'total_active' => 0,
            'renewal_opportunities' => []
        ];

        // Get member policies
        $memberPolicies = MemberPolicy::whereHas('member', function($query) use ($agentId) {
            $query->where('user_id', $agentId);
        })->get();

        // Get medical insurance policies
        $medicalPolicies = MedicalInsurancePolicy::where('agent_id', $agentId)->get();

        $allPolicies = $memberPolicies->concat($medicalPolicies);

        foreach ($allPolicies as $policy) {
            if ($policy->status === 'active') {
                $summary['total_active']++;
                
                $daysUntilExpiration = now()->diffInDays($policy->end_date, false);
                
                if ($daysUntilExpiration <= 30 && $daysUntilExpiration > 0) {
                    $summary['expiring_soon']++;
                    $summary['renewal_opportunities'][] = [
                        'policy_id' => $policy->id,
                        'policy_number' => $policy->policy_number,
                        'customer_name' => $policy->customer_name ?? $policy->member->user->name ?? 'Unknown',
                        'expiration_date' => $policy->end_date->format('Y-m-d'),
                        'days_until_expiration' => $daysUntilExpiration,
                        'type' => $policy instanceof MedicalInsurancePolicy ? 'medical' : 'member'
                    ];
                }
            } elseif ($policy->status === 'expired') {
                $summary['expired']++;
            }
        }

        return $summary;
    }
}
