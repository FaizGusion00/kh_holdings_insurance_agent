<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use App\Models\InsuranceProduct;
use App\Models\PaymentTransaction;
use App\Models\MemberPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display sales report.
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        
        // Convert string dates to Carbon instances
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom);
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo);
        }
        
        // Sales by product
        $salesByProduct = InsuranceProduct::withCount(['memberPolicies' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->withSum(['memberPolicies' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'total_paid')
        ->get()
        ->map(function ($product) {
            $totalRevenue = $product->member_policies_sum_total_paid ?? 0;
            $policiesCount = $product->member_policies_count ?? 0;
            $allProductsTotal = InsuranceProduct::withSum('memberPolicies', 'total_paid')->get()->sum('member_policies_sum_total_paid');
            
            return (object) [
                'name' => $product->name,
                'category' => $product->product_type,
                'policies_count' => $policiesCount,
                'total_revenue' => $totalRevenue,
                'market_share' => $allProductsTotal > 0 ? ($totalRevenue / $allProductsTotal) * 100 : 0,
            ];
        })->sortByDesc('total_revenue');
        
        // Sales by agent (top performing agents)
        $topAgents = User::withCount(['downlines' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->withSum(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'commission_amount')
        ->get()
        ->map(function ($agent) {
            $policiesCount = $agent->members_count ?? 0;
            $commissionEarned = $agent->commissions_sum_commission_amount ?? 0;
            $totalRevenue = $commissionEarned * 10; // Estimate revenue from commissions
            
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'policies_count' => $policiesCount,
                'total_revenue' => $totalRevenue,
                'commission_earned' => $commissionEarned,
            ];
        })->sortByDesc('total_revenue')->take(10);
        
        // Get products and agents for filtering
        $products = InsuranceProduct::where('is_active', true)->get();
        $agents = User::where('status', 'active')->get();
        
        // Calculate summary statistics
        $totalSales = $salesByProduct->sum('total_revenue');
        $totalPolicies = $salesByProduct->sum('policies_count');
        $averagePremium = $totalPolicies > 0 ? $totalSales / $totalPolicies : 0;
        
        // Calculate growth rate (compare with previous period)
        $previousPeriodStart = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo));
        $previousPeriodEnd = $dateFrom->copy()->subDay();
        $previousSales = MemberPolicy::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->sum('total_paid');
        $growthRate = $previousSales > 0 ? (($totalSales - $previousSales) / $previousSales) * 100 : 0;
        
        $summary = [
            'total_sales' => $totalSales,
            'total_policies' => $totalPolicies,
            'average_premium' => $averagePremium,
            'growth_rate' => $growthRate,
        ];
        
        return view('admin.reports.sales', compact(
            'salesByProduct',
            'topAgents',
            'dateFrom',
            'dateTo',
            'products',
            'agents',
            'summary'
        ));
    }

    /**
     * Display commission report.
     */
    public function commissions(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        
        // Convert string dates to Carbon instances
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom);
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo);
        }
        
        // Commission by agent
        $commissionsByAgent = User::withSum(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'commission_amount')
        ->withCount(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->get()
        ->filter(function ($user) {
            return ($user->commissions_sum_commission_amount ?? 0) > 0;
        })
        ->map(function ($agent) use ($dateFrom, $dateTo) {
            $totalCommissions = $agent->commissions_sum_commission_amount ?? 0;
            
            $paidCommissions = $agent->commissions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'paid')
                ->sum('commission_amount');
                
            $pendingCommissions = $agent->commissions()
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'pending')
                ->sum('commission_amount');
                
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'total_commissions' => $totalCommissions,
                'paid_commissions' => $paidCommissions,
                'pending_commissions' => $pendingCommissions,
                'policies_count' => $agent->commissions_count ?? 0,
            ];
        })->sortByDesc('total_commissions');
        
        // Commission by product
        $commissionsByProduct = InsuranceProduct::withSum(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'commission_amount')
        ->withCount(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->get()
        ->filter(function ($product) {
            return ($product->commissions_sum_commission_amount ?? 0) > 0;
        })
        ->map(function ($product) {
            $totalCommissions = $product->commissions_sum_commission_amount ?? 0;
            $policiesCount = $product->commissions_count ?? 0;
            $averageCommissionRate = $policiesCount > 0 ? ($totalCommissions / $policiesCount) * 100 : 0;
            
            return (object) [
                'name' => $product->name,
                'category' => $product->product_type,
                'total_commissions' => $totalCommissions,
                'average_commission_rate' => $averageCommissionRate,
                'policies_count' => $policiesCount,
            ];
        })->sortByDesc('total_commissions');
        
        // Get agents for filtering
        $agents = User::where('status', 'active')->get();
        
        // Summary statistics
        $totalCommissions = $commissionsByAgent->sum('total_commissions');
        $paidCommissions = $commissionsByAgent->sum('paid_commissions');
        $pendingCommissions = $commissionsByAgent->sum('pending_commissions');
        $averageCommission = $commissionsByAgent->count() > 0 ? $totalCommissions / $commissionsByAgent->count() : 0;
        
        $summary = [
            'total_commissions' => $totalCommissions,
            'paid_commissions' => $paidCommissions,
            'pending_commissions' => $pendingCommissions,
            'average_commission' => $averageCommission,
        ];
        
        return view('admin.reports.commissions', compact(
            'commissionsByAgent',
            'commissionsByProduct',
            'dateFrom',
            'dateTo',
            'agents',
            'summary'
        ));
    }

    /**
     * Display members report.
     */
    public function members(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        
        // Convert string dates to Carbon instances
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom);
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo);
        }
        
        // Member registration by month (last 12 months)
        $memberRegistrationByMonth = User::whereNotNull('plan_name')->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_members
            ')
            ->whereBetween('created_at', [Carbon::now()->subMonths(11), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Member registration by agent
        $membersByAgent = User::withCount(['downlines' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->get()
        ->map(function ($agent) {
            $totalMembers = $agent->downlines_count ?? 0;
            $activeMembers = $agent->downlines()->where('status', 'active')->count();
            $newMembersMonth = $agent->downlines()
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'new_members_month' => $newMembersMonth,
            ];
        })
        ->sortByDesc('total_members');
        
        // Get agents for filtering
        $agents = User::where('status', 'active')->get();
        
        // Calculate summary statistics
        $totalMembers = User::whereNotNull('plan_name')->count();
        $newMembersMonth = User::whereNotNull('plan_name')->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $activeMembers = User::whereNotNull('plan_name')->where('status', 'active')->count();
        
        // Calculate growth rate (compare with previous month)
        $previousMonth = Carbon::now()->subMonth();
        $previousMonthMembers = User::whereNotNull('plan_name')->whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->count();
        $growthRate = $previousMonthMembers > 0 ? (($newMembersMonth - $previousMonthMembers) / $previousMonthMembers) * 100 : 0;
        
        $summary = [
            'total_members' => $totalMembers,
            'new_members_month' => $newMembersMonth,
            'active_members' => $activeMembers,
            'growth_rate' => $growthRate,
        ];
        
        // Format registration trend data
        $registrationTrend = $memberRegistrationByMonth->map(function ($item) {
            $date = Carbon::createFromFormat('Y-m', $item->month);
            $previousMonth = $date->copy()->subMonth();
            $previousCount = User::whereNotNull('plan_name')->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();
            
            $growthRate = $previousCount > 0 ? (($item->total_members - $previousCount) / $previousCount) * 100 : 0;
            
            return (object) [
                'month_name' => $date->format('F'),
                'year' => $date->year,
                'new_members' => $item->total_members,
                'total_members' => User::whereNotNull('plan_name')->where('created_at', '<=', $date->endOfMonth())->count(),
                'growth_rate' => $growthRate,
            ];
        });
        
        return view('admin.reports.members', compact(
            'dateFrom',
            'dateTo',
            'agents',
            'summary',
            'registrationTrend',
            'membersByAgent'
        ));
    }

    /**
     * Export report data.
     */
    public function export(Request $request, $type)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        
        // Convert string dates to Carbon instances
        if (is_string($dateFrom)) {
            $dateFrom = Carbon::parse($dateFrom);
        }
        if (is_string($dateTo)) {
            $dateTo = Carbon::parse($dateTo);
        }
        
        switch ($type) {
            case 'sales':
                $data = $this->getSalesExportData($dateFrom, $dateTo);
                $filename = "sales_report_{$dateFrom->format('Y-m-d')}_{$dateTo->format('Y-m-d')}.csv";
                break;
                
            case 'commissions':
                $data = $this->getCommissionExportData($dateFrom, $dateTo);
                $filename = "commission_report_{$dateFrom->format('Y-m-d')}_{$dateTo->format('Y-m-d')}.csv";
                break;
                
            case 'members':
                $data = $this->getMembersExportData($dateFrom, $dateTo);
                $filename = "members_report_{$dateFrom->format('Y-m-d')}_{$dateTo->format('Y-m-d')}.csv";
                break;
                
            default:
                abort(404);
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get sales export data.
     */
    private function getSalesExportData($dateFrom, $dateTo)
    {
        return MemberPolicy::with(['member', 'product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($policy) {
                return [
                    'Policy ID' => $policy->id,
                    'Member Name' => $policy->member->name,
                    'Product Name' => $policy->product->name,
                    'Amount' => $policy->total_paid,
                    'Status' => $policy->status,
                    'Created Date' => $policy->created_at->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    /**
     * Get commission export data.
     */
    private function getCommissionExportData($dateFrom, $dateTo)
    {
        return Commission::with(['agent', 'product'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($commission) {
                return [
                    'Commission ID' => $commission->id,
                    'Agent Name' => $commission->agent->name ?? 'N/A',
                    'Product Name' => $commission->product->name ?? 'N/A',
                    'Amount' => $commission->commission_amount,
                    'Type' => $commission->commission_type,
                    'Tier Level' => $commission->tier_level,
                    'Status' => $commission->status,
                    'Created Date' => $commission->created_at->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    /**
     * Get members export data.
     */
    private function getMembersExportData($dateFrom, $dateTo)
    {
        return User::whereNotNull('plan_name')->with('referrer')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($member) {
                return [
                    'Member ID' => $member->id,
                    'Name' => $member->name,
                    'NRIC' => $member->nric,
                    'Phone' => $member->phone,
                    'Email' => $member->email,
                    'Agent Name' => $member->agent->name ?? 'N/A',
                    'Status' => $member->status,
                    'Registration Date' => $member->registration_date ? $member->registration_date->format('Y-m-d') : $member->created_at->format('Y-m-d'),
                ];
            })
            ->toArray();
    }
}
