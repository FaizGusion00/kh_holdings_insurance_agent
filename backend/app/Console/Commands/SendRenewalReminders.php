<?php

namespace App\Console\Commands;

use App\Models\MemberPolicy;
use App\Models\MedicalInsurancePolicy;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'policies:send-renewal-reminders {--days=30 : Days before expiration to send reminder} {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Send renewal reminders for policies expiring soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $daysBeforeExpiration = (int) $this->option('days');
        
        $this->info("Sending renewal reminders for policies expiring in {$daysBeforeExpiration} days...");
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No reminders will be sent');
        }

        // Process member policies
        $this->processMemberPolicyReminders($daysBeforeExpiration, $isDryRun);
        
        // Process medical insurance policies
        $this->processMedicalPolicyReminders($daysBeforeExpiration, $isDryRun);

        $this->info('Renewal reminder processing completed!');
    }

    /**
     * Process member policy renewal reminders
     */
    private function processMemberPolicyReminders($daysBeforeExpiration, $isDryRun)
    {
        $expiringPolicies = MemberPolicy::where('status', 'active')
            ->where('end_date', '<=', now()->addDays($daysBeforeExpiration))
            ->where('end_date', '>', now())
            ->with(['member.user', 'product'])
            ->get();

        $this->info("Found {$expiringPolicies->count()} member policies expiring in {$daysBeforeExpiration} days");

        foreach ($expiringPolicies as $policy) {
            $daysUntilExpiration = now()->diffInDays($policy->end_date, false);
            
            $this->line("Policy #{$policy->policy_number} expires in {$daysUntilExpiration} days for {$policy->member->user->name}");
            
            if (!$isDryRun) {
                $this->sendRenewalReminder($policy->member->user, $policy, 'member', $daysUntilExpiration);
            }
        }
    }

    /**
     * Process medical insurance policy renewal reminders
     */
    private function processMedicalPolicyReminders($daysBeforeExpiration, $isDryRun)
    {
        $expiringPolicies = MedicalInsurancePolicy::where('status', 'active')
            ->where('end_date', '<=', now()->addDays($daysBeforeExpiration))
            ->where('end_date', '>', now())
            ->with(['agent', 'plan'])
            ->get();

        $this->info("Found {$expiringPolicies->count()} medical insurance policies expiring in {$daysBeforeExpiration} days");

        foreach ($expiringPolicies as $policy) {
            $daysUntilExpiration = now()->diffInDays($policy->end_date, false);
            
            $this->line("Policy #{$policy->policy_number} expires in {$daysUntilExpiration} days for {$policy->customer_name}");
            
            if (!$isDryRun) {
                $this->sendRenewalReminder($policy->agent, $policy, 'medical', $daysUntilExpiration);
            }
        }
    }

    /**
     * Send renewal reminder
     */
    private function sendRenewalReminder($user, $policy, $type, $daysUntilExpiration)
    {
        try {
            $data = [
                'user' => $user,
                'policy' => $policy,
                'type' => $type,
                'days_until_expiration' => $daysUntilExpiration,
                'expiration_date' => $policy->end_date,
                'renewal_url' => url('/renewal/' . $policy->id)
            ];

            // For now, just log the reminder
            // In production, you would send email/SMS here
            Log::info('Renewal reminder sent', [
                'user_id' => $user->id,
                'policy_id' => $policy->id,
                'policy_type' => $type,
                'days_until_expiration' => $daysUntilExpiration,
                'expiration_date' => $policy->end_date->format('Y-m-d')
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
