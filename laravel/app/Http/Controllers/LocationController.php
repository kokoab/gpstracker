<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Store GPS coordinates from SIM900A
     * POST /api/location
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Store location
        $location = Location::create([
            'device_id' => $request->device_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location stored successfully',
            'data' => $location
        ], 201);
    }

    /**
     * Get the latest location for display on map
     * GET /api/location/latest
     */
    public function latest()
    {
        $location = Location::orderBy('created_at', 'desc')->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'No location data available'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $location->device_id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'updated_at' => $location->created_at->toIso8601String()
            ]
        ]);
    }

    /**
     * Show the map view
     */
    public function map()
    {
        return view('map');
    }
}
