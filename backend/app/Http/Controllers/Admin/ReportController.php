<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
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
        
        // Sales by product
        $salesByProduct = InsuranceProduct::withCount(['memberPolicies' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->withSum(['memberPolicies' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'total_paid')
        ->get();
        
        // Sales by agent
        $salesByAgent = User::withCount(['members' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->withSum(['commissions' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }], 'commission_amount')
        ->get();
        
        // Format top agents data for the view
        $topAgents = $salesByAgent->map(function ($agent) {
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'policies_count' => $agent->members_count,
                'total_revenue' => $agent->commissions_sum_commission_amount * 10, // Estimate revenue from commissions
                'commission_earned' => $agent->commissions_sum_commission_amount,
            ];
        })->sortByDesc('total_revenue')->take(10);
        
        // Monthly sales trend
        $monthlySales = MemberPolicy::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_policies,
                SUM(total_paid) as total_amount
            ')
            ->whereBetween('created_at', [Carbon::now()->subMonths(11), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Daily sales for current month
        $dailySales = MemberPolicy::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_policies,
                SUM(total_paid) as total_amount
            ')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Get products and agents for filtering
        $products = InsuranceProduct::where('is_active', true)->get();
        $agents = User::where('status', 'active')->get();
        
        // Summary statistics
        $summary = [
            'total_sales' => $salesByProduct->sum('member_policies_sum_total_paid'),
            'total_policies' => $salesByProduct->sum('member_policies_count'),
            'average_premium' => $salesByProduct->avg('member_policies_sum_total_paid'),
            'growth_rate' => 15.5, // This should be calculated based on previous period
        ];
        
        return view('admin.reports.sales', compact(
            'salesByProduct',
            'salesByAgent',
            'monthlySales',
            'dailySales',
            'dateFrom',
            'dateTo',
            'products',
            'agents',
            'summary',
            'topAgents'
        ));
    }

    /**
     * Display commission report.
     */
    public function commissions(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Commission by agent
        $commissionByAgent = User::withSum(['commissions' => function ($query) use ($month, $year) {
            $query->where('month', $month)->where('year', $year);
        }], 'commission_amount')
        ->withCount(['commissions' => function ($query) use ($month, $year) {
            $query->where('month', $month)->where('year', $year);
        }])
        ->get()
        ->filter(function ($user) {
            return $user->commissions_sum_commission_amount > 0;
        });
        
        // Format commissions by agent data for the view
        $commissionsByAgent = $commissionByAgent->map(function ($agent) use ($month, $year) {
            $paidCommissions = $agent->commissions()
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', 'paid')
                ->sum('commission_amount');
                
            $pendingCommissions = $agent->commissions()
                ->where('month', $month)
                ->where('year', $year)
                ->where('status', 'pending')
                ->sum('commission_amount');
                
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'total_commissions' => $agent->commissions_sum_commission_amount,
                'paid_commissions' => $paidCommissions,
                'pending_commissions' => $pendingCommissions,
                'policies_count' => $agent->commissions_count,
            ];
        })->sortByDesc('total_commissions');
        
        // Commission by product
        $commissionByProduct = InsuranceProduct::withSum(['commissions' => function ($query) use ($month, $year) {
            $query->where('month', $month)->where('year', $year);
        }], 'commission_amount')
        ->withCount(['commissions' => function ($query) use ($month, $year) {
            $query->where('month', $month)->where('year', $year);
        }])
        ->get()
        ->filter(function ($product) {
            return $product->commissions_sum_commission_amount > 0;
        });
        
        // Format commissions by product data for the view
        $commissionsByProduct = $commissionByProduct->map(function ($product) {
            return (object) [
                'name' => $product->name,
                'category' => $product->product_type,
                'total_commissions' => $product->commissions_sum_commission_amount,
                'commission_rate' => 10, // Default rate, you can calculate this based on product rules
                'policies_count' => $product->commissions_count,
            ];
        })->sortByDesc('total_commissions');
        
        // Commission by type
        $commissionByType = Commission::selectRaw('
                commission_type,
                COUNT(*) as total_transactions,
                SUM(commission_amount) as total_amount
            ')
            ->where('month', $month)
            ->where('year', $year)
            ->groupBy('commission_type')
            ->get();
        
        // Commission by tier level
        $commissionByTier = Commission::selectRaw('
                tier_level,
                COUNT(*) as total_transactions,
                SUM(commission_amount) as total_amount
            ')
            ->where('month', $month)
            ->where('year', $year)
            ->groupBy('tier_level')
            ->orderBy('tier_level')
            ->get();
        
        // Monthly commission trend
        $monthlyCommissionTrend = Commission::selectRaw('
                month, year,
                COUNT(*) as total_transactions,
                SUM(commission_amount) as total_amount
            ')
            ->where('year', '>=', $year - 1)
            ->groupBy('month', 'year')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'month' => $date->format('M Y'),
                    'transactions' => $item->total_transactions,
                    'amount' => $item->total_amount,
                ];
            })
            ->reverse()
            ->values();
        
        // Get agents for filtering
        $agents = User::where('status', 'active')->get();
        
        // Summary statistics
        $summary = [
            'total_commissions' => $commissionByAgent->sum('commissions_sum_commission_amount'),
            'paid_commissions' => Commission::where('month', $month)->where('year', $year)->where('status', 'paid')->sum('commission_amount'),
            'pending_commissions' => Commission::where('month', $month)->where('year', $year)->where('status', 'pending')->sum('commission_amount'),
            'average_commission' => $commissionByAgent->avg('commissions_sum_commission_amount'),
        ];
        
        return view('admin.reports.commissions', compact(
            'commissionByAgent',
            'commissionByProduct',
            'commissionByType',
            'commissionByTier',
            'monthlyCommissionTrend',
            'month',
            'year',
            'agents',
            'summary',
            'commissionsByAgent',
            'commissionsByProduct'
        ));
    }

    /**
     * Display members report.
     */
    public function members(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        
        // Member registration by month
        $memberRegistrationByMonth = Member::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_members
            ')
            ->whereBetween('created_at', [Carbon::now()->subMonths(11), Carbon::now()])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Member registration by agent
        $memberRegistrationByAgent = User::withCount(['members' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])
        ->get()
        ->sortByDesc('members_count');
        
        // Member status distribution
        $memberStatusDistribution = Member::selectRaw('
                status,
                COUNT(*) as total_count
            ')
            ->groupBy('status')
            ->get();
        
        // Member by age group
        $memberByAgeGroup = Member::selectRaw('
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25 THEN "18-24"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 35 THEN "25-34"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 45 THEN "35-44"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 55 THEN "45-54"
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 65 THEN "55-64"
                    ELSE "65+"
                END as age_group,
                COUNT(*) as total_count
            ')
            ->groupBy('age_group')
            ->orderBy('age_group')
            ->get();
        
        // Member by gender
        $memberByGender = Member::selectRaw('
                gender,
                COUNT(*) as total_count
            ')
            ->groupBy('gender')
            ->get();
        
        // Member by state
        $memberByState = Member::selectRaw('
                state,
                COUNT(*) as total_count
            ')
            ->groupBy('state')
            ->orderBy('total_count', 'desc')
            ->get();
        
        // Get agents for filtering
        $agents = User::where('status', 'active')->get();
        
        // Summary statistics
        $summary = [
            'total_members' => Member::count(),
            'new_members_month' => Member::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'active_members' => Member::where('status', 'active')->count(),
            'growth_rate' => 12.5, // This should be calculated based on previous period
        ];
        
        // Format registration trend data
        $registrationTrend = $memberRegistrationByMonth->map(function ($item) {
            $date = Carbon::createFromFormat('Y-m', $item->month);
            $previousMonth = $date->copy()->subMonth();
            $previousCount = Member::whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->count();
            
            $growthRate = $previousCount > 0 ? (($item->total_members - $previousCount) / $previousCount) * 100 : 0;
            
            return (object) [
                'month_name' => $date->format('F'),
                'year' => $date->year,
                'new_members' => $item->total_members,
                'total_members' => Member::where('created_at', '<=', $date->endOfMonth())->count(),
                'growth_rate' => $growthRate,
            ];
        });
        
        // Format members by agent data
        $membersByAgent = $memberRegistrationByAgent->map(function ($agent) {
            return (object) [
                'name' => $agent->name,
                'email' => $agent->email,
                'total_members' => $agent->members_count,
                'active_members' => $agent->members()->where('status', 'active')->count(),
                'new_members_month' => $agent->members()
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
            ];
        });
        
        return view('admin.reports.members', compact(
            'memberRegistrationByMonth',
            'memberRegistrationByAgent',
            'memberStatusDistribution',
            'memberByAgeGroup',
            'memberByGender',
            'memberByState',
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
        
        switch ($type) {
            case 'sales':
                $data = $this->getSalesExportData($dateFrom, $dateTo);
                $filename = "sales_report_{$dateFrom->format('Y-m-d')}_{$dateTo->format('Y-m-d')}.csv";
                break;
                
            case 'commissions':
                $month = $request->get('month', Carbon::now()->month);
                $year = $request->get('year', Carbon::now()->year);
                $data = $this->getCommissionExportData($month, $year);
                $filename = "commission_report_{$year}_{$month}.csv";
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
    private function getCommissionExportData($month, $year)
    {
        return Commission::with(['agent', 'product'])
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function ($commission) {
                return [
                    'Commission ID' => $commission->id,
                    'Agent Name' => $commission->agent->name,
                    'Product Name' => $commission->product->name,
                    'Amount' => $commission->commission_amount,
                    'Type' => $commission->commission_type,
                    'Tier Level' => $commission->tier_level,
                    'Status' => $commission->status,
                ];
            })
            ->toArray();
    }

    /**
     * Get members export data.
     */
    private function getMembersExportData($dateFrom, $dateTo)
    {
        return Member::with('agent')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($member) {
                return [
                    'Member ID' => $member->id,
                    'Name' => $member->name,
                    'NRIC' => $member->nric,
                    'Phone' => $member->phone,
                    'Email' => $member->email,
                    'Agent Name' => $member->agent->name,
                    'Status' => $member->status,
                    'Registration Date' => $member->registration_date->format('Y-m-d'),
                ];
            })
            ->toArray();
    }
}
