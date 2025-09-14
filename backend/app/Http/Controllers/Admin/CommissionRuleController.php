<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionRuleController extends Controller
{
    /**
     * Display a listing of commission rules.
     */
    public function index(Request $request)
    {
        $query = CommissionRule::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('plan_type', 'like', "%{$search}%");
            });
        }
        
        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }
        
        // Filter by tier level
        if ($request->filled('tier_level')) {
            $query->where('tier_level', $request->tier_level);
        }
        
        $rules = $query->orderBy('plan_name')->orderBy('tier_level')->paginate(20);
        
        return view('admin.commission-rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new commission rule.
     */
    public function create()
    {
        return view('admin.commission-rules.create');
    }

    /**
     * Store a newly created commission rule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:senior_care,medical_card',
            'payment_frequency' => 'nullable|in:monthly,quarterly,semi_annually,annually',
            'base_amount' => 'required|numeric|min:0',
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:percentage,fixed_amount',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Validate commission type specific fields
        if ($request->commission_type === 'percentage' && !$request->commission_percentage) {
            return redirect()->back()
                ->withErrors(['commission_percentage' => 'Commission percentage is required for percentage type.'])
                ->withInput();
        }

        if ($request->commission_type === 'fixed_amount' && !$request->commission_amount) {
            return redirect()->back()
                ->withErrors(['commission_amount' => 'Commission amount is required for fixed amount type.'])
                ->withInput();
        }

        CommissionRule::create($request->all());

        return redirect()->route('admin.commission-rules.index')
            ->with('success', 'Commission rule created successfully!');
    }

    /**
     * Show the form for editing the specified commission rule.
     */
    public function edit(CommissionRule $commissionRule)
    {
        return view('admin.commission-rules.edit', compact('commissionRule'));
    }

    /**
     * Update the specified commission rule.
     */
    public function update(Request $request, CommissionRule $commissionRule)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:senior_care,medical_card',
            'payment_frequency' => 'nullable|in:monthly,quarterly,semi_annually,annually',
            'base_amount' => 'required|numeric|min:0',
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:percentage,fixed_amount',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'commission_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Validate commission type specific fields
        if ($request->commission_type === 'percentage' && !$request->commission_percentage) {
            return redirect()->back()
                ->withErrors(['commission_percentage' => 'Commission percentage is required for percentage type.'])
                ->withInput();
        }

        if ($request->commission_type === 'fixed_amount' && !$request->commission_amount) {
            return redirect()->back()
                ->withErrors(['commission_amount' => 'Commission amount is required for fixed amount type.'])
                ->withInput();
        }

        $commissionRule->update($request->all());

        return redirect()->route('admin.commission-rules.index')
            ->with('success', 'Commission rule updated successfully!');
    }

    /**
     * Toggle the active status of a commission rule.
     */
    public function toggle(CommissionRule $commissionRule)
    {
        $commissionRule->update(['is_active' => !$commissionRule->is_active]);

        $status = $commissionRule->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Commission rule {$status} successfully!");
    }

    /**
     * Remove the specified commission rule.
     */
    public function destroy(CommissionRule $commissionRule)
    {
        $commissionRule->delete();

        return redirect()->route('admin.commission-rules.index')
            ->with('success', 'Commission rule deleted successfully!');
    }
}
