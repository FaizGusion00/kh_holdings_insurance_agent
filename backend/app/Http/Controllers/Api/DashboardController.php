<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Commission;
use App\Models\PaymentTransaction;
use App\Models\MedicalCase;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get basic stats
        $stats = $this->getStatsData($user, $currentMonth, $currentYear);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivitiesData($user);
        
        // Get performance data
        $performanceData = $this->getPerformanceDataData($user, $currentMonth, $currentYear);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'performance_data' => $performanceData,
                'current_month' => $currentMonth,
                'current_year' => $currentYear
            ]
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        return response()->json([
            'success' => true,
            'data' => $this->getStatsData($user, $currentMonth, $currentYear)
        ]);
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->getRecentActivitiesData($user)
        ]);
    }

    /**
     * Get sharing records (for Records page).
     */
    public function getSharingRecords(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Get monthly performance data for charts
        $monthlyData = $this->getMonthlyPerformanceData($user, $year);

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_performance' => $monthlyData,
                'current_month' => $month,
                'current_year' => $year
            ]
        ]);
    }

    /**
     * Get performance data for Records page.
     */
    public function getPerformanceData(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        return response()->json([
            'success' => true,
            'data' => $this->getPerformanceDataData($user, $month, $year)
        ]);
    }

    /**
     * Private method to get comprehensive stats.
     */
    private function getStatsData($user, $month, $year)
    {
        // Total members
        $totalMembers = Member::where('user_id', $user->id)->count();
        
        // Active members this month
        $activeMembers = Member::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // New members this month
        $newMembers = Member::where('user_id', $user->id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
        
        // Total commission earned
        $totalCommission = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount');
        
        // Monthly commission
        $monthlyCommission = Commission::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'paid')
            ->sum('commission_amount');
        
        // Commission target achievement
        $targetAchievement = $user->monthly_commission_target > 0 
            ? round(($monthlyCommission / $user->monthly_commission_target) * 100, 2) 
            : 0;
        
        // Total referrals
        $referral = Referral::where('user_id', $user->id)->first();
        $totalReferrals = $referral ? $referral->total_downline_count : 0;
        
        // Direct referrals
        $directReferrals = $referral ? $referral->downline_count : 0;

        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'new_members' => $newMembers,
            'total_commission_earned' => round($totalCommission, 2),
            'monthly_commission' => round($monthlyCommission, 2),
            'commission_target' => $user->monthly_commission_target,
            'target_achievement' => $targetAchievement,
            'total_referrals' => $totalReferrals,
            'direct_referrals' => $directReferrals,
            'mlm_level' => $user->mlm_level
        ];
    }

    /**
     * Private method to get recent activities.
     */
    private function getRecentActivitiesData($user)
    {
        $activities = [];

        // Recent member registrations
        $recentMembers = Member::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentMembers as $member) {
            $activities[] = [
                'type' => 'member_registration',
                'title' => 'New Member Registered',
                'description' => "Member {$member->name} registered successfully",
                'date' => $member->created_at,
                'icon' => 'user-plus',
                'color' => 'green'
            ];
        }

        // Recent commission earnings
        $recentCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'type' => 'commission_earned',
                'title' => 'Commission Earned',
                'description' => "RM {$commission->commission_amount} commission from {$commission->product->name}",
                'date' => $commission->payment_date,
                'icon' => 'dollar-sign',
                'color' => 'blue'
            ];
        }

        // Recent payments
        $recentPayments = PaymentTransaction::whereHas('policy.member', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'completed')
        ->orderBy('transaction_date', 'desc')
        ->limit(5)
        ->get();

        foreach ($recentPayments as $payment) {
            $activities[] = [
                'type' => 'payment_received',
                'title' => 'Payment Received',
                'description' => "RM {$payment->amount} payment from {$payment->policy->member->name}",
                'date' => $payment->transaction_date,
                'icon' => 'credit-card',
                'color' => 'green'
            ];
        }

        // Sort by date and return top 10
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Private method to get performance data.
     */
    private function getPerformanceDataData($user, $month, $year)
    {
        // Monthly commission trend (last 12 months)
        $monthlyTrend = [];
        $currentDate = Carbon::now();
        
        for ($i = 0; $i < 12; $i++) {
            $checkMonth = $currentDate->month;
            $checkYear = $currentDate->year;
            
            $monthlyCommission = Commission::where('user_id', $user->id)
                ->where('month', $checkMonth)
                ->where('year', $checkYear)
                ->where('status', 'paid')
                ->sum('commission_amount');
            
            $monthlyTrend[] = [
                'month' => $checkMonth,
                'year' => $checkYear,
                'commission' => round($monthlyCommission, 2),
                'label' => Carbon::createFromDate($checkYear, $checkMonth, 1)->format('M Y')
            ];
            
            $currentDate->subMonth();
        }

        // Member growth trend
        $memberGrowth = [];
        $currentDate = Carbon::now();
        
        for ($i = 0; $i < 12; $i++) {
            $checkMonth = $currentDate->month;
            $checkYear = $currentDate->year;
            
            $newMembers = Member::where('user_id', $user->id)
                ->whereMonth('created_at', $checkMonth)
                ->whereYear('created_at', $checkYear)
                ->count();
            
            $memberGrowth[] = [
                'month' => $checkMonth,
                'year' => $checkYear,
                'new_members' => $newMembers,
                'label' => Carbon::createFromDate($checkYear, $checkMonth, 1)->format('M Y')
            ];
            
            $currentDate->subMonth();
        }

        // Product performance
        $productPerformance = Commission::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'paid')
            ->with('product')
            ->select('product_id', DB::raw('SUM(commission_amount) as total_commission'), DB::raw('COUNT(*) as sales_count'))
            ->groupBy('product_id')
            ->get()
            ->map(function($item) {
                return [
                    'product_name' => $item->product->name,
                    'total_commission' => round($item->total_commission, 2),
                    'sales_count' => $item->sales_count
                ];
            });

        return [
            'monthly_commission_trend' => array_reverse($monthlyTrend),
            'member_growth_trend' => array_reverse($memberGrowth),
            'product_performance' => $productPerformance,
            'current_month' => $month,
            'current_year' => $year
        ];
    }

    /**
     * Private method to get monthly performance data for charts.
     */
    private function getMonthlyPerformanceData($user, $year)
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            // Commission data
            $commission = Commission::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', 'paid')
                ->sum('commission_amount');
            
            // Member data
            $newMembers = Member::where('user_id', $user->id)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();
            
            // Payment data
            // Use gateway payments instead of legacy payment_transactions
            $payments = \App\Models\GatewayPayment::where('agent_id', $user->id)
                ->whereMonth('completed_at', $month)
                ->whereYear('completed_at', $year)
                ->where('status', 'completed')
                ->sum('amount');

            // Medical case counts (approved)
            $hospitalCases = MedicalCase::where('user_id', $user->id)
                ->where('case_type', 'hospital')
                ->where('status', 'approved')
                ->whereMonth('approved_at', $month)
                ->whereYear('approved_at', $year)
                ->count();

            $clinicCases = MedicalCase::where('user_id', $user->id)
                ->where('case_type', 'clinic')
                ->where('status', 'approved')
                ->whereMonth('approved_at', $month)
                ->whereYear('approved_at', $year)
                ->count();
            
            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::createFromDate($year, $month, 1)->format('M'),
                'commission' => round($commission, 2),
                'new_members' => $newMembers,
                'payments' => round($payments, 2),
                'hospital_cases' => $hospitalCases,
                'clinic_cases' => $clinicCases,
            ];
        }
        
        return $monthlyData;
    }
}
