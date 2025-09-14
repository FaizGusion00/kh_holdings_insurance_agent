<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\InsuranceProduct;
use App\Models\PaymentTransaction;
use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with key metrics and statistics.
     */
    public function index()
    {
        // Get current month and year for filtering
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Key metrics
        $metrics = [
            'total_agents' => User::count(),
            'active_agents' => User::where('status', 'active')->count(),
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'total_products' => InsuranceProduct::where('is_active', true)->count(),
            'monthly_commissions' => Commission::where('month', $currentMonth)
                                              ->where('year', $currentYear)
                                              ->sum('commission_amount'),
            'pending_payments' => PaymentTransaction::where('status', 'pending')->count(),
            
            // Commission Automation Metrics
            'total_commission_rules' => CommissionRule::where('is_active', true)->count(),
            'auto_processed_commissions' => Commission::where('status', 'paid')
                                                     ->where('created_at', '>=', now()->subDays(7))
                                                     ->count(),
            'pending_commissions' => Commission::where('status', 'pending')->count(),
            'total_wallet_balance' => AgentWallet::sum('balance'),
            'recent_wallet_transactions' => WalletTransaction::where('created_at', '>=', now()->subDays(7))->count(),
            'total_revenue' => PaymentTransaction::where('status', 'completed')
                                               ->whereMonth('created_at', $currentMonth)
                                               ->whereYear('created_at', $currentYear)
                                               ->sum('amount'),
            // Revenue breakdown for doughnut chart
            'completed_revenue' => PaymentTransaction::where('status', 'completed')
                                                   ->whereMonth('created_at', $currentMonth)
                                                   ->whereYear('created_at', $currentYear)
                                                   ->sum('amount'),
            'pending_revenue' => PaymentTransaction::where('status', 'pending')
                                                 ->whereMonth('created_at', $currentMonth)
                                                 ->whereYear('created_at', $currentYear)
                                                 ->sum('amount'),
            'failed_revenue' => PaymentTransaction::where('status', 'failed')
                                                ->whereMonth('created_at', $currentMonth)
                                                ->whereYear('created_at', $currentYear)
                                                ->sum('amount'),
        ];
        
        // Monthly commission trends (last 6 months)
        $commissionTrends = Commission::selectRaw('
                month, year, 
                SUM(commission_amount) as total_commission,
                COUNT(*) as total_transactions
            ')
            ->where('year', '>=', $currentYear - 1)
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'commission' => (float) $item->total_commission,
                    'transactions' => (int) $item->total_transactions,
                ];
            })
            ->reverse()
            ->values();
        
        // If no commission data, create empty data for the last 6 months
        if ($commissionTrends->isEmpty()) {
            $commissionTrends = collect();
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $commissionTrends->push([
                    'month' => $date->format('M Y'),
                    'commission' => 0,
                    'transactions' => 0,
                ]);
            }
        }
        
        // Top performing agents
        $topAgents = User::selectRaw('
                users.name, users.agent_code,
                COUNT(members.id) as total_members,
                SUM(commissions.commission_amount) as total_commission
            ')
            ->leftJoin('members', 'users.id', '=', 'members.user_id')
            ->leftJoin('commissions', 'users.id', '=', 'commissions.user_id')
            ->groupBy('users.id', 'users.name', 'users.agent_code')
            ->orderBy('total_commission', 'desc')
            ->limit(5)
            ->get();
        
        // Recent activities
        $recentActivities = collect();
        
        // Recent members
        $recentMembers = Member::with('agent')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($member) {
                return [
                    'type' => 'member_registered',
                    'message' => "New member {$member->name} registered by " . ($member->agent ? $member->agent->name : 'Unknown Agent'),
                    'time' => $member->created_at->diffForHumans(),
                    'icon' => 'user-plus',
                    'color' => 'text-blue-600',
                ];
            });
        
        // Recent commissions
        $recentCommissions = Commission::with(['agent', 'product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($commission) {
                return [
                    'type' => 'commission_earned',
                    'message' => ($commission->agent ? $commission->agent->name : 'Unknown Agent') . " earned RM " . number_format($commission->commission_amount, 2) . " from " . ($commission->product ? $commission->product->name : 'Unknown Product'),
                    'time' => $commission->created_at->diffForHumans(),
                    'icon' => 'dollar-sign',
                    'color' => 'text-green-600',
                ];
            });
        
        // Recent payments
        $recentPayments = PaymentTransaction::with('member')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment_received',
                    'message' => "Payment of RM " . number_format($payment->amount, 2) . " received from " . ($payment->member ? $payment->member->name : 'Unknown Member'),
                    'time' => $payment->created_at->diffForHumans(),
                    'icon' => 'credit-card',
                    'color' => 'text-purple-600',
                ];
            });
        
        // Merge and sort all activities
        $recentActivities = $recentMembers->concat($recentCommissions)->concat($recentPayments)
            ->sortByDesc(function ($activity) {
                return $activity['time'];
            })
            ->take(10);
        
        // Chart data for member growth
        $memberGrowth = Member::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as count
            ')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => (int) $item->count,
                ];
            });
        
        // If no member growth data, create empty data for the last 30 days
        if ($memberGrowth->isEmpty()) {
            $memberGrowth = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $memberGrowth->push([
                    'date' => $date->format('M d'),
                    'count' => 0,
                ]);
            }
        }
        
        return view('admin.dashboard', compact(
            'metrics',
            'commissionTrends',
            'topAgents',
            'recentActivities',
            'memberGrowth'
        ));
    }
}
