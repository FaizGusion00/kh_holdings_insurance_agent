<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Models\InsuranceProduct;
use App\Models\MemberPolicy;
use App\Models\ProductCommissionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionController extends Controller
{
    /**
     * Display a listing of commissions.
     */
    public function index(Request $request)
    {
        $query = Commission::with(['agent', 'product', 'policy.member'])
            ->withSum('policy', 'total_paid');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('agent', function ($agentQuery) use ($search) {
                    $agentQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('agent_code', 'like', "%{$search}%");
                })
                ->orWhereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by agent
        if ($request->filled('agent')) {
            $query->where('user_id', $request->agent);
        }
        
        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        // Filter by month and year
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')->paginate(15);
        $agents = User::where('status', 'active')->get();
        $products = InsuranceProduct::where('is_active', true)->get();
        
        // Summary statistics
        $summary = [
            'total_commission' => $commissions->sum('commission_amount'),
            'pending_commission' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'paid_commission' => $commissions->where('status', 'paid')->sum('commission_amount'),
            'total_transactions' => $commissions->count(),
        ];
        
        return view('admin.commissions.index', compact('commissions', 'agents', 'products', 'summary'));
    }

    /**
     * Show the form for creating a new commission.
     */
    public function create()
    {
        $agents = User::where('status', 'active')->get();
        $products = InsuranceProduct::where('is_active', true)->get();
        $policies = MemberPolicy::where('status', 'active')->get();
        
        return view('admin.commissions.create', compact('agents', 'products', 'policies'));
    }

    /**
     * Store a newly created commission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:insurance_products,id',
            'policy_id' => 'required|exists:member_policies,id',
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:direct,referral,override',
            'base_amount' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annually,annually',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        $commission = Commission::create([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'policy_id' => $request->policy_id,
            'tier_level' => $request->tier_level,
            'commission_type' => $request->commission_type,
            'base_amount' => $request->base_amount,
            'commission_percentage' => $request->commission_percentage,
            'commission_amount' => ($request->base_amount * $request->commission_percentage) / 100,
            'payment_frequency' => $request->payment_frequency,
            'month' => $request->month,
            'year' => $request->year,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission created successfully!');
    }

    /**
     * Display the specified commission.
     */
    public function show(Commission $commission)
    {
        $commission->load(['agent', 'product', 'policy.member', 'referrer']);
        
        return view('admin.commissions.show', compact('commission'));
    }

    /**
     * Show the form for editing the specified commission.
     */
    public function edit(Commission $commission)
    {
        $agents = User::where('status', 'active')->get();
        $products = InsuranceProduct::where('is_active', true)->get();
        $policies = MemberPolicy::where('status', 'active')->get();
        
        return view('admin.commissions.edit', compact('commission', 'agents', 'products', 'policies'));
    }

    /**
     * Update the specified commission in storage.
     */
    public function update(Request $request, Commission $commission)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:insurance_products,id',
            'policy_id' => 'required|exists:member_policies,id',
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:direct,referral,override',
            'base_amount' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annually,annually',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        $commission->update([
            'user_id' => $request->user_id,
            'product_id' => $request->product_id,
            'policy_id' => $request->policy_id,
            'tier_level' => $request->tier_level,
            'commission_type' => $request->commission_type,
            'base_amount' => $request->base_amount,
            'commission_percentage' => $request->commission_percentage,
            'commission_amount' => ($request->base_amount * $request->commission_percentage) / 100,
            'payment_frequency' => $request->payment_frequency,
            'month' => $request->month,
            'year' => $request->year,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission updated successfully!');
    }

    /**
     * Remove the specified commission from storage.
     */
    public function destroy(Commission $commission)
    {
        if ($commission->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Cannot delete paid commission.');
        }

        $commission->delete();

        return redirect()->route('admin.commissions.index')
            ->with('success', 'Commission deleted successfully!');
    }

    /**
     * Show commission calculation form.
     */
    public function showCalculateForm()
    {
        $agents = User::where('status', 'active')->get();
        $products = InsuranceProduct::where('is_active', true)->get();
        $commissionRules = ProductCommissionRule::with('product')->get();
        
        return view('admin.commissions.calculate', compact('agents', 'products', 'commissionRules'));
    }

    /**
     * Calculate commissions for specified period.
     */
    public function calculateCommissions(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'exists:users,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:insurance_products,id',
        ]);

        $month = $request->month;
        $year = $request->year;
        
        // Get active policies for the period
        $policies = MemberPolicy::where('status', 'active')
            ->when($request->agent_ids, function ($query, $agentIds) {
                return $query->whereHas('member', function ($memberQuery) use ($agentIds) {
                    $memberQuery->whereIn('user_id', $agentIds);
                });
            })
            ->when($request->product_ids, function ($query, $productIds) {
                return $query->whereIn('product_id', $productIds);
            })
            ->get();

        $commissionsCreated = 0;
        $totalAmount = 0;

        DB::beginTransaction();
        try {
            foreach ($policies as $policy) {
                // Get commission rules for the product
                $commissionRules = ProductCommissionRule::where('product_id', $policy->product_id)
                    ->where('is_active', true)
                    ->get();

                foreach ($commissionRules as $rule) {
                    // Calculate commission based on rule
                    $commissionAmount = ($policy->total_paid * $rule->commission_percentage) / 100;
                    
                    // Create commission record
                    Commission::create([
                        'user_id' => $policy->member->user_id,
                        'product_id' => $policy->product_id,
                        'policy_id' => $policy->id,
                        'tier_level' => $rule->tier_level,
                        'commission_type' => $rule->commission_type,
                        'base_amount' => $policy->total_paid,
                        'commission_percentage' => $rule->commission_percentage,
                        'commission_amount' => $commissionAmount,
                        'payment_frequency' => $policy->payment_frequency,
                        'month' => $month,
                        'year' => $year,
                        'status' => 'pending',
                    ]);

                    $commissionsCreated++;
                    $totalAmount += $commissionAmount;
                }
            }

            DB::commit();

            return redirect()->route('admin.commissions.index')
                ->with('success', "Successfully calculated {$commissionsCreated} commissions totaling RM " . number_format($totalAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error calculating commissions: ' . $e->getMessage());
        }
    }

    /**
     * Bulk pay commissions.
     */
    public function bulkPay(Request $request)
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commissions,id',
            'payment_date' => 'required|date',
        ]);

        $commissions = Commission::whereIn('id', $request->commission_ids)
            ->where('status', 'pending')
            ->get();

        $totalPaid = 0;
        $paidCount = 0;

        foreach ($commissions as $commission) {
            $commission->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date,
            ]);
            
            $totalPaid += $commission->commission_amount;
            $paidCount++;
        }

        return redirect()->route('admin.commissions.index')
            ->with('success', "Successfully paid {$paidCount} commissions totaling RM " . number_format($totalPaid, 2));
    }
}
