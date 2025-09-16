<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Dashboard Controller for API
 * 
 * Handles dashboard statistics, recent activities, and performance data
 */
class DashboardController extends Controller
{
    /**
     * Get dashboard overview data
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get dashboard statistics
            $stats = $this->getDashboardStats($user);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($user);
            
            // Get performance data
            $performanceData = $this->getPerformanceData($user);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'recent_activities' => $recentActivities,
                    'performance_data' => $performanceData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function statistics()
    {
        try {
            $user = Auth::user();
            $stats = $this->getDashboardStats($user);

            return response()->json([
                'status' => 'success',
                'data' => ['stats' => $stats]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function recentActivities()
    {
        try {
            $user = Auth::user();
            $activities = $this->getRecentActivities($user);

            return response()->json([
                'status' => 'success',
                'data' => ['activities' => $activities]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch recent activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for user
     */
    private function getDashboardStats($user)
    {
        // Total members in user's downline
        $totalMembers = User::where('referrer_code', $user->agent_code)->count();
        
        // New members this month
        $newMembers = User::where('referrer_code', $user->agent_code)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Active members (those with active policies)
        $activeMembers = User::where('referrer_code', $user->agent_code)
            ->whereHas('memberPolicies', function($query) {
                $query->where('status', 'active')
                      ->where('policy_end_date', '>', Carbon::now());
            })
            ->count();

        // Total commission earned
        $totalCommissionEarned = $user->total_commission_earned ?? 0;

        // Monthly commission target achievement percentage
        $monthlyTarget = $user->monthly_commission_target ?? 1;
        $monthlyEarned = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'commission_earned')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');
        
        $targetAchievement = $monthlyTarget > 0 ? ($monthlyEarned / $monthlyTarget) * 100 : 0;

        // MLM Level
        $mlmLevel = $user->mlm_level ?? 1;

        return [
            'total_members' => $totalMembers,
            'new_members' => $newMembers,
            'active_members' => $activeMembers,
            'total_commission_earned' => number_format($totalCommissionEarned, 2),
            'monthly_commission_earned' => number_format($monthlyEarned, 2),
            'target_achievement' => round($targetAchievement, 1),
            'mlm_level' => $mlmLevel,
            'wallet_balance' => number_format($user->wallet_balance ?? 0, 2),
        ];
    }

    /**
     * Get recent activities for user
     */
    private function getRecentActivities($user)
    {
        $activities = [];

        // Recent member registrations
        $newMembers = User::where('referrer_code', $user->agent_code)
            ->latest()
            ->limit(5)
            ->get(['name', 'created_at']);

        foreach ($newMembers as $member) {
            $activities[] = [
                'type' => 'member_registration',
                'title' => 'New Member Registered',
                'description' => "{$member->name} joined your network",
                'created_at' => $member->created_at,
                'icon' => 'user_plus',
                'color' => 'green'
            ];
        }

        // Recent commission earnings
        $recentCommissions = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'commission_earned')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'type' => 'commission_earned',
                'title' => 'Commission Earned',
                'description' => "Earned RM " . number_format($commission->amount, 2) . " commission",
                'created_at' => $commission->created_at,
                'icon' => 'dollar_sign',
                'color' => 'blue'
            ];
        }

        // Recent payments
        $recentPayments = PaymentTransaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentPayments as $payment) {
            $activities[] = [
                'type' => 'payment_received',
                'title' => 'Payment Received',
                'description' => "Payment of RM " . number_format($payment->amount, 2) . " processed",
                'created_at' => $payment->created_at,
                'icon' => 'credit_card',
                'color' => 'emerald'
            ];
        }

        // Sort by date and return latest 10
        usort($activities, function($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get performance data for charts
     */
    private function getPerformanceData($user)
    {
        // Monthly commission data for the last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = WalletTransaction::where('user_id', $user->id)
                ->where('type', 'commission_earned')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'amount' => floatval($amount),
                'target' => floatval($user->monthly_commission_target ?? 0)
            ];
        }

        // Network growth data
        $networkGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = User::where('referrer_code', $user->agent_code)
                ->where('created_at', '<=', $date->endOfMonth())
                ->count();
            
            $networkGrowth[] = [
                'month' => $date->format('M Y'),
                'members' => $count
            ];
        }

        return [
            'monthly_commissions' => $monthlyData,
            'network_growth' => $networkGrowth,
            'total_earnings' => floatval($user->total_commission_earned ?? 0),
            'avg_monthly_earnings' => floatval($monthlyData ? array_sum(array_column($monthlyData, 'amount')) / count($monthlyData) : 0)
        ];
    }

    /**
     * Get members list for dashboard
     */
    public function getMembers(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = User::where('referrer_code', $user->agent_code)
                ->with(['memberPolicies' => function($q) {
                    $q->where('status', 'active');
                }]);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('nric', 'LIKE', "%{$search}%");
                });
            }

            $members = $query->paginate($perPage);

            // Transform the data to include additional information
            $members->getCollection()->transform(function($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'nric' => $member->nric,
                    'phone_number' => $member->phone_number,
                    'status' => $member->status,
                    'registration_date' => $member->registration_date,
                    'balance' => $member->balance,
                    'wallet_balance' => $member->wallet_balance,
                    'mlm_level' => $member->mlm_level,
                    'active_policies_count' => $member->memberPolicies->count(),
                    'total_commission_earned' => $member->total_commission_earned,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $members
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}