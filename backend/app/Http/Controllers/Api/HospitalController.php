<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search');
            $state = $request->input('state');

            $query = Hospital::active()->panel();

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            if ($state) {
                $query->where('state', $state);
            }

            $hospitals = $query->orderBy('name')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $hospitals
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch hospitals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $hospital = Hospital::active()->panel()->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => ['hospital' => $hospital]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hospital not found'
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
                    'data' => ['hospitals' => []]
                ]);
            }

            $hospitals = Hospital::active()->panel()
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('city', 'LIKE', "%{$search}%")
                ->orWhere('state', 'LIKE', "%{$search}%")
                ->limit($limit)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => ['hospitals' => $hospitals]
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