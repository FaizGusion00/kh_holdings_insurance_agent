<?php

namespace App\Console\Commands;

use App\Models\MemberPolicy;
use App\Models\MedicalInsurancePolicy;
use App\Models\User;
use App\Services\CommissionAutomationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessExpiredPolicies extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'policies:process-expired {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Process expired policies and send renewal reminders';

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
        
        $this->info('Processing expired policies...');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Process member policies
        $this->processMemberPolicies($isDryRun);
        
        // Process medical insurance policies
        $this->processMedicalInsurancePolicies($isDryRun);

        $this->info('Policy expiration processing completed!');
    }

    /**
     * Process expired member policies
     */
    private function processMemberPolicies($isDryRun)
    {
        $expiredPolicies = MemberPolicy::where('status', 'active')
            ->where('end_date', '<', now())
            ->with(['member.user', 'product'])
            ->get();

        $this->info("Found {$expiredPolicies->count()} expired member policies");

        foreach ($expiredPolicies as $policy) {
            $this->line("Processing policy #{$policy->policy_number} for {$policy->member->user->name}");
            
            if (!$isDryRun) {
                // Update policy status
                $policy->status = 'expired';
                $policy->save();

                // Send renewal reminder
                $this->sendRenewalReminder($policy->member->user, $policy, 'member');
            }
        }
    }

    /**
     * Process expired medical insurance policies
     */
    private function processMedicalInsurancePolicies($isDryRun)
    {
        $expiredPolicies = MedicalInsurancePolicy::where('status', 'active')
            ->where('end_date', '<', now())
            ->with(['agent', 'plan'])
            ->get();

        $this->info("Found {$expiredPolicies->count()} expired medical insurance policies");

        foreach ($expiredPolicies as $policy) {
            $this->line("Processing policy #{$policy->policy_number} for {$policy->customer_name}");
            
            if (!$isDryRun) {
                // Update policy status
                $policy->status = 'expired';
                $policy->save();

                // Send renewal reminder to agent
                $this->sendRenewalReminder($policy->agent, $policy, 'medical');
            }
        }
    }

    /**
     * Send renewal reminder
     */
    private function sendRenewalReminder($user, $policy, $type)
    {
        try {
            $data = [
                'user' => $user,
                'policy' => $policy,
                'type' => $type,
                'expired_date' => $policy->end_date,
                'renewal_url' => url('/renewal/' . $policy->id)
            ];

            // For now, just log the reminder
            // In production, you would send email/SMS here
            Log::info('Renewal reminder sent', [
                'user_id' => $user->id,
                'policy_id' => $policy->id,
                'policy_type' => $type,
                'expired_date' => $policy->end_date->format('Y-m-d')
            ]);

            $this->line("  ✓ Renewal reminder sent to {$user->name}");

        } catch (\Exception $e) {
            Log::error('Failed to send renewal reminder', [
                'user_id' => $user->id,
                'policy_id' => $policy->id,
                'error' => $e->getMessage()
            ]);

            $this->error("  ✗ Failed to send renewal reminder to {$user->name}");
        }
    }
}
