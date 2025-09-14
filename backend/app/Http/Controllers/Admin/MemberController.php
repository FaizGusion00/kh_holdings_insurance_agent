<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $query = Member::with(['agent'])
            ->withCount(['policies', 'paymentTransactions'])
            ->withSum('paymentTransactions', 'amount');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nric', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
        
        // Filter by registration date
        if ($request->filled('date_from')) {
            $query->where('registration_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('registration_date', '<=', $request->date_to);
        }
        
        $members = $query->orderBy('created_at', 'desc')->paginate(15);
        $agents = User::where('status', 'active')->get();
        
        return view('admin.members.index', compact('members', 'agents'));
    }

    /**
     * Show the form for creating a new member.
     */
    public function create()
    {
        $agents = User::where('status', 'active')->get();
        return view('admin.members.create', compact('agents'));
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'nric' => 'required|string|max:20|unique:members',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'occupation' => 'required|string|max:100',
            'race' => 'required|string|max:50',
            'relationship_with_agent' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'referrer_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $member = Member::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'nric' => $request->nric,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'occupation' => $request->occupation,
            'race' => $request->race,
            'relationship_with_agent' => $request->relationship_with_agent,
            'status' => $request->status,
            'registration_date' => now(),
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'referrer_code' => $request->referrer_code,
            'balance' => 0,
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', 'Member created successfully!');
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member)
    {
        $member->load(['agent', 'policies.product', 'paymentTransactions']);
        
        // Get member statistics
        $stats = [
            'total_policies' => $member->policies()->count(),
            'active_policies' => $member->activePolicies()->count(),
            'total_premium_paid' => $member->total_premium_paid,
            'next_payment_due' => $member->next_payment_due_date,
            'total_transactions' => $member->paymentTransactions()->count(),
            'pending_payments' => $member->paymentTransactions()->where('status', 'pending')->count(),
        ];
        
        return view('admin.members.show', compact('member', 'stats'));
    }

    /**
     * Show the form for editing the specified member.
     */
    public function edit(Member $member)
    {
        $agents = User::where('status', 'active')->get();
        return view('admin.members.edit', compact('member', 'agents'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'nric' => 'required|string|max:20|unique:members,nric,' . $member->id,
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'occupation' => 'required|string|max:100',
            'race' => 'required|string|max:50',
            'relationship_with_agent' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,suspended',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'referrer_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $member->update($request->all());

        return redirect()->route('admin.members.index')
            ->with('success', 'Member updated successfully!');
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member)
    {
        // Check if member has policies or transactions
        if ($member->policies()->count() > 0 || $member->paymentTransactions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete member with existing policies or transactions.');
        }

        $member->delete();

        return redirect()->route('admin.members.index')
            ->with('success', 'Member deleted successfully!');
    }

    /**
     * Show member policies.
     */
    public function policies(Member $member)
    {
        $policies = $member->policies()
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.members.policies', compact('member', 'policies'));
    }

    /**
     * Show member transactions.
     */
    public function transactions(Member $member)
    {
        $transactions = $member->paymentTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.members.transactions', compact('member', 'transactions'));
    }
}
