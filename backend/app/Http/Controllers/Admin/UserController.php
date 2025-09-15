<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users (agents).
     */
    public function index(Request $request)
    {
        $query = User::withCount(['downlines', 'commissions'])
            ->withSum('commissions', 'commission_amount');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('agent_code', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by MLM level
        if ($request->filled('mlm_level')) {
            $query->where('mlm_level', $request->mlm_level);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'agent_number' => 'required|string|unique:users|max:50',
            'agent_code' => 'required|string|unique:users|max:50',
            'phone_number' => 'required|string|max:20',
            'nric' => 'required|string|max:20|unique:users',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_owner' => 'required|string|max:255',
            'mlm_level' => 'required|integer|min:1|max:10',
            'monthly_commission_target' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'agent_number' => $request->agent_number,
            'agent_code' => $request->agent_code,
            'phone_number' => $request->phone_number,
            'nric' => $request->nric,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_owner' => $request->bank_account_owner,
            'mlm_level' => $request->mlm_level,
            'monthly_commission_target' => $request->monthly_commission_target,
            'status' => $request->status,
            'mlm_activation_date' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Agent created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['downlines', 'commissions', 'referral']);
        
        // Get performance statistics
        $stats = [
            'total_members' => $user->downlines()->count(),
            'active_members' => $user->downlines()->where('status', 'active')->count(),
            'total_commission' => $user->commissions()->sum('commission_amount'),
            'monthly_commission' => $user->commissions()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('commission_amount'),
            'target_achievement' => $user->monthly_commission_target > 0 
                ? ($user->commissions()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('commission_amount') / $user->monthly_commission_target) * 100
                : 0,
        ];
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'agent_number' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'agent_code' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'required|string|max:20',
            'nric' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_owner' => 'required|string|max:255',
            'mlm_level' => 'required|integer|min:1|max:10',
            'monthly_commission_target' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update($request->except(['password']));

        return redirect()->route('admin.users.index')
            ->with('success', 'Agent updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Check if this user has a plan (is a customer)
            $hasPlan = $user->plan_name !== null;
            
            if ($hasPlan) {
                // If it's an agent, use the cascading delete
                if ($user->agent_code) {
                    return $this->deleteAgentWithCascade($user);
                } else {
                    // If it's a customer, delete normally
                    $user->medicalInsurancePolicies()->delete();
                    $user->paymentTransactions()->delete();
                    $user->delete();
                }
            }

            // Delete user's commissions
            $user->commissions()->delete();
            
            // Delete user's wallet and transactions
            if ($user->agentWallet) {
                $user->agentWallet->transactions()->delete();
                $user->agentWallet->delete();
            }

            // Delete user's withdrawal requests
            $user->withdrawalRequests()->delete();

            // Delete the user
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'Agent and all related records deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting agent: ' . $e->getMessage());
        }
    }

    /**
     * Delete agent with cascading deletes for their network
     */
    private function deleteAgentWithCascade(User $agent)
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

        // Delete agent's wallet and transactions
        $user = User::find($agent->user_id);
        if ($user && $user->agentWallet) {
            $user->agentWallet->transactions()->delete();
            $user->agentWallet->delete();
        }

        // Delete agent's withdrawal requests
        if ($user) {
            $user->withdrawalRequests()->delete();
        }

        // Delete the agent from members table
        $agent->delete();

        // Also delete from users table if it exists
        if ($user) {
            $user->commissions()->delete();
            $user->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Agent and all related records deleted successfully!');
    }

    /**
     * Toggle user status between active/inactive.
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Agent status changed to {$newStatus} successfully!");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()
            ->with('success', 'Password reset successfully!');
    }
}
