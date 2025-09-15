<?php

namespace App\Console\Commands;

use App\Models\MemberPolicy;
use App\Models\MedicalInsurancePolicy;
use App\Services\CommissionAutomationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRenewalCommissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'policies:process-renewal-commissions {policy_id? : Specific policy ID to process} {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Process renewal commissions for renewed policies';

    protected $commissionService;

    public function __construct(CommissionAutomationService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $policyId = $this->argument('policy_id');
        
        $this->info('Processing renewal commissions...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if ($policyId) {
            $this->processSpecificPolicy($policyId, $isDryRun);
        } else {
            $this->processAllRenewedPolicies($isDryRun);
        }

        $this->info('Renewal commission processing completed!');
    }

    /**
     * Process a specific policy renewal
     */
    private function processSpecificPolicy($policyId, $isDryRun)
    {
        // Try member policy first
        $memberPolicy = MemberPolicy::find($policyId);
        if ($memberPolicy) {
            $this->processMemberPolicyRenewal($memberPolicy, $isDryRun);
            return;
        }

        // Try medical insurance policy
        $medicalPolicy = MedicalInsurancePolicy::find($policyId);
        if ($medicalPolicy) {
            $this->processMedicalPolicyRenewal($medicalPolicy, $isDryRun);
            return;
        }

        $this->error("Policy with ID {$policyId} not found");
    }

    /**
     * Process all renewed policies
     */
    private function processAllRenewedPolicies($isDryRun)
    {
        // Process member policies that were renewed in the last 24 hours
        $renewedMemberPolicies = MemberPolicy::where('status', 'active')
            ->where('created_at', '>=', now()->subDay())
            ->where('start_date', '>=', now()->subDay())
            ->with(['member.user', 'product'])
            ->get();

        $this->info("Found {$renewedMemberPolicies->count()} renewed member policies");

        foreach ($renewedMemberPolicies as $policy) {
            $this->processMemberPolicyRenewal($policy, $isDryRun);
        }

        // Process medical insurance policies that were renewed in the last 24 hours
        $renewedMedicalPolicies = MedicalInsurancePolicy::where('status', 'active')
            ->where('created_at', '>=', now()->subDay())
            ->where('start_date', '>=', now()->subDay())
            ->with(['agent', 'plan'])
            ->get();

        $this->info("Found {$renewedMedicalPolicies->count()} renewed medical insurance policies");

        foreach ($renewedMedicalPolicies as $policy) {
            $this->processMedicalPolicyRenewal($policy, $isDryRun);
        }
    }

    /**
     * Process member policy renewal commission
     */
    private function processMemberPolicyRenewal($policy, $isDryRun)
    {
        $this->line("Processing renewal commission for member policy #{$policy->policy_number}");

        if (!$isDryRun) {
            try {
                $result = $this->commissionService->processPolicyCommission($policy->id);
                
                if ($result['success']) {
                    $this->line("  ✓ Commission processed: {$result['processed_count']} commissions, Total: RM " . number_format($result['total_amount'], 2));
                } else {
                    $this->error("  ✗ Commission processing failed: " . $result['error']);
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error processing commission: " . $e->getMessage());
            }
        }
    }

    /**
     * Process medical insurance policy renewal commission
     */
    private function processMedicalPolicyRenewal($policy, $isDryRun)
    {
        $this->line("Processing renewal commission for medical policy #{$policy->policy_number}");

        if (!$isDryRun) {
            try {
                // For medical insurance, we need to process the registration
                $result = $this->commissionService->processMedicalInsuranceCommission($policy->registration_id);
                
                if ($result['success']) {
                    $this->line("  ✓ Commission processed: {$result['processed_count']} commissions, Total: RM " . number_format($result['total_amount'], 2));
                } else {
                    $this->error("  ✗ Commission processing failed: " . $result['error']);
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error processing commission: " . $e->getMessage());
            }
        }
    }
}
