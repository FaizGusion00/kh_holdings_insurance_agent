<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
use App\Models\Commission;
use App\Models\InsuranceProduct;
use App\Models\PaymentTransaction;
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
            'total_revenue' => PaymentTransaction::where('status', 'completed')
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
                    'commission' => $item->total_commission,
                    'transactions' => $item->total_transactions,
                ];
            })
            ->reverse()
            ->values();
        
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
                    'message' => "New member {$member->name} registered by {$member->agent->name}",
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
                    'message' => "{$commission->agent->name} earned RM {$commission->commission_amount} from {$commission->product->name}",
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
                    'message' => "Payment of RM {$payment->amount} received from {$payment->member->name}",
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
                    'count' => $item->count,
                ];
            });
        
        return view('admin.dashboard', compact(
            'metrics',
            'commissionTrends',
            'topAgents',
            'recentActivities',
            'memberGrowth'
        ));
    }
}
