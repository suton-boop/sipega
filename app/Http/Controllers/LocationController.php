<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    /**
     * Store a new geo-fencing location for attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|default:100' // Meter
        ]);

        $location = Location::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius ?? 100
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Titik lokasi berhasil didaftarkan.',
            'data' => $location
        ]);
    }
}
