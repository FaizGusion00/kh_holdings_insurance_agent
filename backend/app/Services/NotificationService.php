<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new notification.
     */
    public function create(array $data): Notification
    {
        // Set default values
        $data = array_merge([
            'is_read' => false,
            'is_important' => false,
            'expires_at' => now()->addDays(30), // Default 30 days expiry
        ], $data);

        return Notification::create($data);
    }

    /**
     * Create notification for commission earned.
     */
    public function createCommissionNotification($userId, $amount, $description = null, $actionUrl = null)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => 'commission',
            'title' => 'Commission Earned',
            'message' => $description ?: "You earned RM " . number_format($amount, 2) . " in commission!",
            'data' => [
                'amount' => $amount,
                'type' => 'commission_earned'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=referrer&subtab=commission',
            'action_text' => 'View Commission',
            'is_important' => $amount > 100, // Important if commission > RM 100
        ]);
    }

    /**
     * Create notification for policy expiry.
     */
    public function createPolicyExpiryNotification($userId, $policyNumber, $daysUntilExpiry, $actionUrl = null)
    {
        $isUrgent = $daysUntilExpiry <= 7;
        
        return $this->create([
            'user_id' => $userId,
            'type' => 'expiry',
            'title' => $isUrgent ? 'Policy Expiring Soon!' : 'Policy Expiry Reminder',
            'message' => "Policy #{$policyNumber} will expire in {$daysUntilExpiry} days. Please renew to maintain coverage.",
            'data' => [
                'policy_number' => $policyNumber,
                'days_until_expiry' => $daysUntilExpiry,
                'type' => 'policy_expiry'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=medical-insurance',
            'action_text' => 'Renew Policy',
            'is_important' => $isUrgent,
        ]);
    }

    /**
     * Create notification for policy renewal.
     */
    public function createPolicyRenewalNotification($userId, $policyNumber, $actionUrl = null)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => 'renewal',
            'title' => 'Policy Renewed Successfully',
            'message' => "Policy #{$policyNumber} has been successfully renewed. Your coverage is now extended.",
            'data' => [
                'policy_number' => $policyNumber,
                'type' => 'policy_renewal'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=medical-insurance',
            'action_text' => 'View Policy',
        ]);
    }

    /**
     * Create notification for new member registration.
     */
    public function createMemberRegistrationNotification($userId, $memberName, $actionUrl = null)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => 'referral',
            'title' => 'New Member Registered',
            'message' => "{$memberName} has been successfully registered under your network.",
            'data' => [
                'member_name' => $memberName,
                'type' => 'member_registration'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=referrer&subtab=referral',
            'action_text' => 'View Members',
        ]);
    }

    /**
     * Create notification for payment received.
     */
    public function createPaymentNotification($userId, $amount, $description = null, $actionUrl = null)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => $description ?: "Payment of RM " . number_format($amount, 2) . " has been received.",
            'data' => [
                'amount' => $amount,
                'type' => 'payment_received'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=overview',
            'action_text' => 'View Details',
        ]);
    }

    /**
     * Create notification for wallet transaction.
     */
    public function createWalletNotification($userId, $amount, $type, $description = null, $actionUrl = null)
    {
        $title = match($type) {
            'deposit' => 'Funds Added to Wallet',
            'withdrawal' => 'Withdrawal Processed',
            'commission' => 'Commission Added to Wallet',
            default => 'Wallet Transaction'
        };

        return $this->create([
            'user_id' => $userId,
            'type' => 'wallet',
            'title' => $title,
            'message' => $description ?: "RM " . number_format($amount, 2) . " has been {$type}ed to your wallet.",
            'data' => [
                'amount' => $amount,
                'transaction_type' => $type,
                'type' => 'wallet_transaction'
            ],
            'action_url' => $actionUrl ?: '/profile?tab=overview',
            'action_text' => 'View Wallet',
        ]);
    }

    /**
     * Create notification for system updates.
     */
    public function createSystemNotification($userId, $title, $message, $actionUrl = null, $isImportant = false)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'data' => [
                'type' => 'system_notification'
            ],
            'action_url' => $actionUrl,
            'action_text' => $actionUrl ? 'View Details' : null,
            'is_important' => $isImportant,
        ]);
    }

    /**
     * Get notifications for a user.
     */
    public function getUserNotifications($userId, $limit = 100, $unreadOnly = false)
    {
        $query = Notification::where('user_id', $userId)
            ->notExpired()
            ->recent($limit);

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->get();
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->notExpired()
            ->count();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete expired notifications.
     */
    public function deleteExpired()
    {
        return Notification::where('expires_at', '<', now())->delete();
    }

    /**
     * Clean up old notifications (keep only last 100 per user).
     */
    public function cleanupOldNotifications()
    {
        $users = User::all();
        
        foreach ($users as $user) {
            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->skip(100)
                ->get();

            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
    }

    /**
     * Create notification for multiple users (bulk).
     */
    public function createBulkNotification(array $userIds, array $data)
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = $this->create(array_merge($data, ['user_id' => $userId]));
        }

        return $notifications;
    }
}
