<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    /**
     * Display a listing of members (now consolidated users).
     */
    public function index(Request $request)
    {
        $query = User::with(['referrer'])
            ->withCount(['paymentTransactions'])
            ->withSum('paymentTransactions', 'amount');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nric', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('agent_code', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by agent level
        if ($request->filled('level')) {
            $query->where('mlm_level', $request->level);
        }
        
        // Filter by agent type (now all are agents in consolidated users table)
        if ($request->filled('type')) {
            if ($request->type === 'active_agents') {
                $query->where('status', 'active')
                      ->whereNotNull('agent_code')
                      ->whereNotNull('mlm_activation_date');
            } elseif ($request->type === 'customers') {
                $query->whereNotNull('plan_name'); // Users who have purchased plans
            }
        }
        
        // Filter by referring agent
        if ($request->filled('agent')) {
            $query->where('referrer_id', $request->agent);
        }
        
        // Filter by registration date
        if ($request->filled('date_from')) {
            $query->where('registration_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('registration_date', '<=', $request->date_to);
        }
        
        $members = $query->orderBy('mlm_level', 'asc')->orderBy('created_at', 'desc')->paginate(15);
        $agents = User::whereNotNull('agent_code')
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();
        
        // Get network statistics
        $networkStats = [
            'total_agents' => User::whereNotNull('agent_code')->where('status', 'active')->count(),
            'level_1_agents' => User::whereNotNull('agent_code')->where('mlm_level', 1)->count(),
            'level_2_agents' => User::whereNotNull('agent_code')->where('mlm_level', 2)->count(),
            'level_3_agents' => User::whereNotNull('agent_code')->where('mlm_level', 3)->count(),
            'total_customers' => User::whereNotNull('plan_name')->count(),
        ];
        
        return view('admin.members.index', compact('members', 'agents', 'networkStats'));
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

        $member = User::create([
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
        $member->load(['agent', 'referrerAgent', 'paymentTransactions']);
        
        // Get member statistics
        $stats = [
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
        try {
            // If this is an agent, handle cascading deletes
            if ($member->is_agent) {
                return $this->deleteAgentWithCascade($member);
            } else {
                // For regular customers, check if they have policies or transactions
                if ($member->policies()->count() > 0 || $member->paymentTransactions()->count() > 0) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete member with existing policies or transactions.');
                }

                $member->delete();

                return redirect()->route('admin.members.index')
                    ->with('success', 'Member deleted successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting member: ' . $e->getMessage());
        }
    }

    /**
     * Delete agent with cascading deletes for their network
     */
    private function deleteAgentWithCascade(Member $agent)
    {
        // Get all agents under this agent (their downline)
        $downlineAgents = User::where('referrer_id', $agent->id)
            ->whereNotNull('agent_code')
            ->get();

        // Get all customers under this agent
        $customers = User::where('referrer_id', $agent->id)
            ->whereNotNull('plan_name')
            ->get();

        // Check if any downline agents have customers or policies
        $hasActiveDownline = false;
        foreach ($downlineAgents as $downlineAgent) {
            if ($downlineAgent->medicalInsurancePolicies()->count() > 0 || 
                $downlineAgent->paymentTransactions()->count() > 0 ||
                User::where('referrer_id', $downlineAgent->id)->whereNotNull('plan_name')->count() > 0) {
                $hasActiveDownline = true;
                break;
            }
        }

        if ($hasActiveDownline) {
            return redirect()->back()
                ->with('error', 'Cannot delete agent with active downline agents who have customers or policies.');
        }

        // Delete all customers under this agent
        foreach ($customers as $customer) {
            // Delete customer's policies and transactions
            $customer->policies()->delete();
            $customer->paymentTransactions()->delete();
            $customer->delete();
        }

        // Delete all downline agents (recursively)
        foreach ($downlineAgents as $downlineAgent) {
            $this->deleteAgentWithCascade($downlineAgent);
        }

        // Delete the agent's own policies and transactions
        $agent->policies()->delete();
        $agent->paymentTransactions()->delete();

        // Delete the agent from members table
        $agent->delete();

        // Also delete from users table if it exists
        $user = User::find($agent->user_id);
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.members.index')
            ->with('success', 'Agent and all related records deleted successfully!');
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
