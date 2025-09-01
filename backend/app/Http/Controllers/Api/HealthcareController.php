<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Clinic;
use Illuminate\Http\Request;

class HealthcareController extends Controller
{
    /**
     * Get all healthcare facilities.
     */
    public function index()
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();
        $clinics = Clinic::where('is_active', true)->orderBy('name')->get();
        
        $facilities = $hospitals->map(function($hospital) {
            return [
                'id' => $hospital->id,
                'name' => $hospital->name,
                'type' => 'hospital',
                'address' => $hospital->address,
                'city' => $hospital->city,
                'state' => $hospital->state,
                'phone' => $hospital->phone,
                'email' => $hospital->email,
                'specialties' => $hospital->specialties,
                'status' => $hospital->is_active ? 'active' : 'inactive'
            ];
        })->concat($clinics->map(function($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'type' => 'clinic',
                'address' => $clinic->address,
                'city' => $clinic->city,
                'state' => $clinic->state,
                'phone' => $clinic->phone,
                'email' => $clinic->email,
                'specialties' => $clinic->specialties,
                'operating_hours' => $clinic->operating_hours,
                'status' => $clinic->is_active ? 'active' : 'inactive'
            ];
        }));

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
        $hospitals = Hospital::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($hospital) {
                return [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'type' => 'hospital',
                    'address' => $hospital->address,
                    'city' => $hospital->city,
                    'state' => $hospital->state,
                    'phone' => $hospital->phone,
                    'email' => $hospital->email,
                    'specialties' => $hospital->specialties,
                    'status' => $hospital->is_active ? 'active' : 'inactive'
                ];
            });

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
        $clinics = Clinic::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'type' => 'clinic',
                    'address' => $clinic->address,
                    'city' => $clinic->city,
                    'state' => $clinic->state,
                    'phone' => $clinic->phone,
                    'email' => $clinic->email,
                    'specialties' => $clinic->specialties,
                    'operating_hours' => $clinic->operating_hours,
                    'status' => $clinic->is_active ? 'active' : 'inactive'
                ];
            });

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

        $hospitals = Hospital::where('is_active', true)
            ->when($query, function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                         ->orWhere('city', 'like', "%{$query}%");
            })
            ->when($state, function($q) use ($state) {
                return $q->where('state', $state);
            })
            ->orderBy('name')
            ->get()
            ->map(function($hospital) {
                return [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'type' => 'hospital',
                    'address' => $hospital->address,
                    'city' => $hospital->city,
                    'state' => $hospital->state,
                    'phone' => $hospital->phone,
                    'email' => $hospital->email,
                    'specialties' => $hospital->specialties,
                    'status' => $hospital->is_active ? 'active' : 'inactive'
                ];
            });

        $clinics = Clinic::where('is_active', true)
            ->when($query, function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                         ->orWhere('city', 'like', "%{$query}%");
            })
            ->when($state, function($q) use ($state) {
                return $q->where('state', $state);
            })
            ->orderBy('name')
            ->get()
            ->map(function($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'type' => 'clinic',
                    'address' => $clinic->address,
                    'city' => $clinic->city,
                    'state' => $clinic->state,
                    'phone' => $clinic->phone,
                    'email' => $clinic->email,
                    'specialties' => $clinic->specialties,
                    'operating_hours' => $clinic->operating_hours,
                    'status' => $clinic->is_active ? 'active' : 'inactive'
                ];
            });

        $facilities = $hospitals->concat($clinics);

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
        $facility = null;
        
        // Try to find in hospitals first
        $hospital = Hospital::where('id', $id)->where('is_active', true)->first();
        if ($hospital) {
            $facility = [
                'id' => $hospital->id,
                'name' => $hospital->name,
                'type' => 'hospital',
                'address' => $hospital->address,
                'city' => $hospital->city,
                'state' => $hospital->state,
                'phone' => $hospital->phone,
                'email' => $hospital->email,
                'specialties' => $hospital->specialties,
                'status' => $hospital->is_active ? 'active' : 'inactive'
            ];
        }
        
        // If not found in hospitals, try clinics
        if (!$facility) {
            $clinic = Clinic::where('id', $id)->where('is_active', true)->first();
            if ($clinic) {
                $facility = [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'type' => 'clinic',
                    'address' => $clinic->address,
                    'city' => $clinic->city,
                    'state' => $clinic->state,
                    'phone' => $clinic->phone,
                    'email' => $clinic->email,
                    'specialties' => $clinic->specialties,
                    'operating_hours' => $clinic->operating_hours,
                    'status' => $clinic->is_active ? 'active' : 'inactive'
                ];
            }
        }

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
