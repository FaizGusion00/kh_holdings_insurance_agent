<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MlmController extends Controller
{
    public function network(Request $request)
    {
        $user = auth('api')->user();
        $level = $request->get('level', 1);
        
        // Get direct referrals (level 1)
        $selectFields = ['id', 'name', 'email', 'agent_code', 'phone_number', 'created_at'];
        if (\Schema::hasColumn('users', 'status')) {
            $selectFields[] = 'status';
        }
        
        $directReferrals = User::where('referrer_code', $user->agent_code)
            ->select($selectFields)
            ->get()
            ->map(function ($m) {
                $m->mlm_level = 1;
                $m->registration_date = $m->created_at;
                $m->status = $m->status ?? 'active';
                $m->active_policies_count = 0;
                $m->total_commission_earned = 0;
                $m->downline_count = User::where('referrer_code', $m->agent_code)->count();
                return $m;
            });

        // Get downlines for other levels
        $downlines = collect();
        if ($level > 1) {
            $downlines = $this->getDownlinesRecursive($user->agent_code, $level - 1);
        }

        $allMembers = $directReferrals->concat($downlines);
        
        return response()->json([
            'status' => 'success', 
            'data' => [
                'network_members' => $allMembers->values(),
                'total_members' => $allMembers->count(),
                // Expose both the array of direct referrals and the count
                'direct_referrals' => $directReferrals->values(),
                'direct_referrals_count' => $directReferrals->count(),
                'total_downlines' => $downlines->count()
            ]
        ]);
    }

    private function getDownlinesRecursive($agentCode, $levels, $currentLevel = 1)
    {
        if ($levels <= 0) return collect();
        
        $selectFields = ['id', 'name', 'email', 'agent_code', 'phone_number', 'created_at'];
        if (Schema::hasColumn('users', 'status')) {
            $selectFields[] = 'status';
        }
        
        $downlines = User::where('referrer_code', $agentCode)
            ->select($selectFields)
            ->get()
            ->map(function ($m) use ($currentLevel) {
                $m->mlm_level = $currentLevel + 1;
                $m->registration_date = $m->created_at;
                $m->status = $m->status ?? 'active';
                $m->active_policies_count = 0;
                $m->total_commission_earned = 0;
                $m->downline_count = User::where('referrer_code', $m->agent_code)->count();
                return $m;
            });

        // Recursively get deeper levels
        $deeperDownlines = collect();
        foreach ($downlines as $downline) {
            $deeperDownlines = $deeperDownlines->concat(
                $this->getDownlinesRecursive($downline->agent_code, $levels - 1, $currentLevel + 1)
            );
        }

        return $downlines->concat($deeperDownlines);
    }

    public function commissionHistory(Request $request)
    {
        $user = auth('api')->user();
        $perPage = $request->get('per_page', 15);
        $from = $request->get('from');
        $to = $request->get('to');
        
        $query = \App\Models\CommissionTransaction::where('earner_user_id', $user->id);
        
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json(['status' => 'success', 'data' => $transactions]);
    }

    public function levelSummary()
    {
        $user = auth('api')->user();
        
        // Get counts for each level
        $levelCounts = [];
        for ($level = 1; $level <= 5; $level++) {
            $count = $this->getDownlinesCount($user->agent_code, $level);
            $levelCounts["level_{$level}"] = $count;
        }
        
        return response()->json([
            'status' => 'success', 
            'data' => [
                'level_summary' => $levelCounts,
                'total_network' => array_sum($levelCounts)
            ]
        ]);
    }

    private function getDownlinesCount($agentCode, $level)
    {
        if ($level <= 0) return 0;
        
        $directCount = User::where('referrer_code', $agentCode)->count();
        
        if ($level == 1) return $directCount;
        
        $totalCount = 0;
        $directDownlines = User::where('referrer_code', $agentCode)->get();
        
        foreach ($directDownlines as $downline) {
            $totalCount += $this->getDownlinesCount($downline->agent_code, $level - 1);
        }
        
        return $totalCount;
    }

    public function getReferrals()
    {
        return $this->network(request());
    }

    public function getDownlines($level = 2)
    {
        $request = request();
        $request->merge(['level' => $level]);
        return $this->network($request);
    }

    public function getCommissionSummary()
    {
        $user = auth('api')->user();
        
        $totalEarnedCents = \App\Models\CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'posted')
            ->sum('commission_cents');
            
        $pendingAmountCents = \App\Models\CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_cents');
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_earned' => ($totalEarnedCents ?? 0) / 100,
                'pending_amount' => ($pendingAmountCents ?? 0) / 100,
                'total_commission' => (($totalEarnedCents ?? 0) + ($pendingAmountCents ?? 0)) / 100
            ]
        ]);
    }

    public function registerClient(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ]);
        $user = auth('api')->user();
        $client = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('password'),
            'referrer_code' => $user->agent_code,
        ]);
        return response()->json(['status' => 'success', 'data' => ['client' => $client]]);
    }

    public function registerBulkClients(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1',
            'clients.*.name' => 'required|string',
            'clients.*.email' => 'required|email',
            'clients.*.insurance_plan_id' => 'required|integer',
        ]);

        $registrationId = now()->timestamp;
        return response()->json(['status' => 'success', 'data' => [
            'registration_id' => $registrationId,
            'clients' => $validated['clients'],
            'policies' => [],
            'total_amount' => 0,
        ]]);
    }
}


