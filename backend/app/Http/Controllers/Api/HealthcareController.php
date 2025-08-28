<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthcareController extends Controller
{
    /**
     * Get all healthcare facilities.
     */
    public function index()
    {
        $facilities = DB::table('healthcare_facilities')
            ->select('*')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $facilities
        ]);
    }

    /**
     * Get hospitals only.
     */
    public function hospitals()
    {
        $hospitals = DB::table('healthcare_facilities')
            ->where('type', 'hospital')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hospitals
        ]);
    }

    /**
     * Get clinics only.
     */
    public function clinics()
    {
        $clinics = DB::table('healthcare_facilities')
            ->where('type', 'clinic')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clinics
        ]);
    }

    /**
     * Search healthcare facilities.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type');
        $state = $request->input('state');

        $facilities = DB::table('healthcare_facilities')
            ->when($query, function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                         ->orWhere('city', 'like', "%{$query}%");
            })
            ->when($type, function($q) use ($type) {
                return $q->where('type', $type);
            })
            ->when($state, function($q) use ($state) {
                return $q->where('state', $type);
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $facilities
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $facility = DB::table('healthcare_facilities')
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$facility) {
            return response()->json([
                'success' => false,
                'message' => 'Healthcare facility not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $facility
        ]);
    }
}
