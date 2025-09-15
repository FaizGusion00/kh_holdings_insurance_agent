<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CreateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:create-test {--user-id= : Specific user ID to create notifications for}';

    /**
     * The console command description.
     */
    protected $description = 'Create test notifications for development';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return;
            }
            $this->createNotificationsForUser($user);
        } else {
            $users = User::where('role', 'agent')->take(3)->get();
            if ($users->isEmpty()) {
                $this->error("No agent users found");
                return;
            }
            
            foreach ($users as $user) {
                $this->createNotificationsForUser($user);
            }
        }

        $this->info('Test notifications created successfully!');
    }

    private function createNotificationsForUser($user)
    {
        $this->line("Creating test notifications for {$user->name} (ID: {$user->id})");

        // Commission notification
        $this->notificationService->createCommissionNotification(
            $user->id,
            150.00,
            "Commission earned from Senior Care Plan Gold 270 - Tier 1",
            "/profile?tab=referrer&subtab=commission"
        );

        // Policy expiry notification
        $this->notificationService->createPolicyExpiryNotification(
            $user->id,
            "POL20250914001",
            15,
            "/profile?tab=medical-insurance"
        );

        // Member registration notification
        $this->notificationService->createMemberRegistrationNotification(
            $user->id,
            "John Doe",
            "/profile?tab=referrer&subtab=referral"
        );

        // Payment notification
        $this->notificationService->createPaymentNotification(
            $user->id,
            500.00,
            "Payment received for Medical Insurance Premium",
            "/profile?tab=overview"
        );

        // Wallet notification
        $this->notificationService->createWalletNotification(
            $user->id,
            150.00,
            "commission",
            "Commission added to your wallet",
            "/profile?tab=overview"
        );

        // System notification
        $this->notificationService->createSystemNotification(
            $user->id,
            "System Update",
            "New features have been added to your dashboard. Check them out!",
            "/dashboard",
            false
        );

        // Important system notification
        $this->notificationService->createSystemNotification(
            $user->id,
            "Important: Policy Renewal Required",
            "Your medical insurance policy will expire in 7 days. Please renew to maintain coverage.",
            "/profile?tab=medical-insurance",
            true
        );

        $this->line("  ✓ Created 7 test notifications");
    }
}