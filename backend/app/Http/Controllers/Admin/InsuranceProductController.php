<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProduct;
use App\Models\ProductCommissionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InsuranceProductController extends Controller
{
    /**
     * Display a listing of insurance products.
     */
    public function index(Request $request)
    {
        $query = InsuranceProduct::withCount(['memberPolicies as policies_count', 'commissionRules as commission_rules_count'])
            ->withSum('commissions', 'commission_amount');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by product type
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }
        
        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('base_price', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('base_price', '<=', $request->price_max);
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get unique product types for filter
        $productTypes = InsuranceProduct::distinct()->pluck('product_type');
        
        return view('admin.products.index', compact('products', 'productTypes'));
    }

    /**
     * Show the form for creating a new insurance product.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created insurance product in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|string|max:100',
            'name' => 'required|string|max:255|unique:insurance_products',
            'description' => 'required|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annually,annually',
            'price_multiplier' => 'required|numeric|min:0.1|max:10',
            'coverage_details' => 'required|array',
            'coverage_details.*.coverage_type' => 'required|string|max:100',
            'coverage_details.*.amount' => 'required|numeric|min:0',
            'coverage_details.*.description' => 'required|string|max:500',
            'waiting_period_days' => 'required|integer|min:0|max:365',
            'max_coverage_amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = InsuranceProduct::create([
            'product_type' => $request->product_type,
            'name' => $request->name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'payment_frequency' => $request->payment_frequency,
            'price_multiplier' => $request->price_multiplier,
            'coverage_details' => $request->coverage_details,
            'waiting_period_days' => $request->waiting_period_days,
            'max_coverage_amount' => $request->max_coverage_amount,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Insurance product created successfully!');
    }

    /**
     * Display the specified insurance product.
     */
    public function show(InsuranceProduct $product)
    {
        $product->load(['memberPolicies.member', 'commissions.agent', 'commissionRules']);
        
        // Get product statistics
        $stats = [
            'total_policies' => $product->memberPolicies()->count(),
            'active_policies' => $product->memberPolicies()->where('status', 'active')->count(),
            'total_revenue' => $product->memberPolicies()->sum('total_paid'),
            'total_commissions' => $product->commissions()->sum('commission_amount'),
            'monthly_revenue' => $product->memberPolicies()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_paid'),
        ];
        
        return view('admin.products.show', compact('product', 'stats'));
    }

    /**
     * Show the form for editing the specified insurance product.
     */
    public function edit(InsuranceProduct $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified insurance product in storage.
     */
    public function update(Request $request, InsuranceProduct $product)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|string|max:100',
            'name' => 'required|string|max:255|unique:insurance_products,name,' . $product->id,
            'description' => 'required|string|max:1000',
            'base_price' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annually,annually',
            'price_multiplier' => 'required|numeric|min:0.1|max:10',
            'coverage_details' => 'required|array',
            'coverage_details.*.coverage_type' => 'required|string|max:100',
            'coverage_details.*.amount' => 'required|numeric|min:0',
            'coverage_details.*.description' => 'required|string|max:500',
            'waiting_period_days' => 'required|integer|min:0|max:365',
            'max_coverage_amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product->update($request->all());

        return redirect()->route('admin.products.index')
            ->with('success', 'Insurance product updated successfully!');
    }

    /**
     * Remove the specified insurance product from storage.
     */
    public function destroy(InsuranceProduct $product)
    {
        // Check if product has policies or commissions
        if ($product->memberPolicies()->count() > 0 || $product->commissions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete product with existing policies or commissions.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Insurance product deleted successfully!');
    }

    /**
     * Toggle product status between active/inactive.
     */
    public function toggleStatus(InsuranceProduct $product)
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Product {$statusText} successfully!");
    }

    /**
     * Show product commission rules.
     */
    public function commissionRules(InsuranceProduct $product)
    {
        $commissionRules = $product->commissionRules()->orderBy('tier_level')->get();
        
        return view('admin.products.commission-rules', compact('product', 'commissionRules'));
    }

    /**
     * Store commission rule for product.
     */
    public function storeCommissionRule(Request $request, InsuranceProduct $product)
    {
        $request->validate([
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:direct,referral,override',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gt:min_amount',
            'is_active' => 'required|boolean',
        ]);

        $product->commissionRules()->create($request->all());

        return redirect()->back()
            ->with('success', 'Commission rule created successfully!');
    }

    /**
     * Update commission rule.
     */
    public function updateCommissionRule(Request $request, ProductCommissionRule $rule)
    {
        $request->validate([
            'tier_level' => 'required|integer|min:1|max:5',
            'commission_type' => 'required|in:direct,referral,override',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gt:min_amount',
            'is_active' => 'required|boolean',
        ]);

        $rule->update($request->all());

        return redirect()->back()
            ->with('success', 'Commission rule updated successfully!');
    }

    /**
     * Delete commission rule.
     */
    public function deleteCommissionRule(ProductCommissionRule $rule)
    {
        $rule->delete();

        return redirect()->back()
            ->with('success', 'Commission rule deleted successfully!');
    }
}
