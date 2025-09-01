<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClinicController extends Controller
{
    /**
     * Display a listing of clinics.
     */
    public function index(Request $request)
    {
        $query = Clinic::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }
        
        // Filter by state
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        
        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $clinics = $query->orderBy('name')->paginate(15);
        
        // Get unique states for filter
        $states = Clinic::distinct()->pluck('state');
        
        return view('admin.clinics.index', compact('clinics', 'states'));
    }

    /**
     * Show the form for creating a new clinic.
     */
    public function create()
    {
        return view('admin.clinics.create');
    }

    /**
     * Store a newly created clinic in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'specialties' => 'nullable|array',
            'specialties.*' => 'string|max:100',
            'operating_hours' => 'nullable|array',
            'operating_hours.*.day' => 'required|string|max:20',
            'operating_hours.*.open_time' => 'required|string|max:10',
            'operating_hours.*.close_time' => 'required|string|max:10',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Clinic::create($request->all());

        return redirect()->route('admin.clinics.index')
            ->with('success', 'Clinic created successfully!');
    }

    /**
     * Display the specified clinic.
     */
    public function show(Clinic $clinic)
    {
        return view('admin.clinics.show', compact('clinic'));
    }

    /**
     * Show the form for editing the specified clinic.
     */
    public function edit(Clinic $clinic)
    {
        return view('admin.clinics.edit', compact('clinic'));
    }

    /**
     * Update the specified clinic in storage.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'specialties' => 'nullable|array',
            'specialties.*' => 'string|max:100',
            'operating_hours' => 'nullable|array',
            'operating_hours.*.day' => 'required|string|max:20',
            'operating_hours.*.open_time' => 'required|string|max:10',
            'operating_hours.*.close_time' => 'required|string|max:10',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $clinic->update($request->all());

        return redirect()->route('admin.clinics.index')
            ->with('success', 'Clinic updated successfully!');
    }

    /**
     * Remove the specified clinic from storage.
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return redirect()->route('admin.clinics.index')
            ->with('success', 'Clinic deleted successfully!');
    }
}
