<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search');
            $state = $request->input('state');

            $query = Clinic::active()->panel();

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            if ($state) {
                $query->where('state', $state);
            }

            $clinics = $query->orderBy('name')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $clinics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch clinics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $clinic = Clinic::active()->panel()->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => ['clinic' => $clinic]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Clinic not found'
            ], 404);
        }
    }

    public function search(Request $request)
    {
        try {
            $search = $request->input('q');
            $limit = $request->input('limit', 10);

            if (!$search) {
                return response()->json([
                    'status' => 'success',
                    'data' => ['clinics' => []]
                ]);
            }

            $clinics = Clinic::active()->panel()
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('city', 'LIKE', "%{$search}%")
                ->orWhere('state', 'LIKE', "%{$search}%")
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => ['clinics' => $clinics]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}