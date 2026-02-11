<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $query = Route::with('stops');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:pickup,drop',
            'bus_id' => 'required|exists:buses,id',
            'stops' => 'required|array|min:2',
            'stops.*.name' => 'required|string',
            'stops.*.sequence' => 'required|integer',
            'stops.*.lat' => 'nullable|numeric',
            'stops.*.lng' => 'nullable|numeric',
        ]);

        $route = Route::create([
            'name' => $request->name,
            'type' => $request->type,
            'bus_id' => $request->bus_id,
            'created_by' => $request->user()->id,
        ]);

        foreach ($request->stops as $stopData) {
            Stop::create([
                'route_id' => $route->id,
                'stop_name' => $stopData['name'],
                'sequence' => $stopData['sequence'],
                'latitude' => $stopData['lat'] ?? null,
                'longitude' => $stopData['lng'] ?? null,
            ]);
        }

        return response()->json($route->load('stops'), 201);
    }

    public function show(Route $route)
    {
        return response()->json($route->load('stops'));
    }

    public function destroy(Route $route)
    {
        $route->delete();
        return response()->json(['message' => 'Route deleted successfully']);
    }
}
