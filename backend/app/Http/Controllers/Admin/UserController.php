<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
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
        $query = User::withCount(['members', 'commissions'])
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
        $user->load(['members', 'commissions', 'referral']);
        
        // Get performance statistics
        $stats = [
            'total_members' => $user->members()->count(),
            'active_members' => $user->members()->where('status', 'active')->count(),
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
        // Check if user has members or commissions
        if ($user->members()->count() > 0 || $user->commissions()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete agent with existing members or commissions.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Agent deleted successfully!');
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
