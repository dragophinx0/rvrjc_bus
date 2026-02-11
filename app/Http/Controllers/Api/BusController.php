<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocationHistory;
use App\Jobs\UpdateBusLocationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bus::with(['routes.stops', 'currentDriver']);

        if ($request->has('bus_number')) {
            $query->where('bus_number', 'like', '%' . $request->bus_number . '%');
        }

        if ($request->has('route_type')) {
            $query->whereHas('routes', function ($q) use ($request) {
                $q->where('type', $request->route_type);
            });
        }

        if ($request->has('from_stop') || $request->has('to_stop')) {
            $query->whereHas('routes.stops', function ($q) use ($request) {
                if ($request->has('from_stop')) {
                    $q->where('stop_name', 'like', '%' . $request->from_stop . '%');
                }
                if ($request->has('to_stop')) {
                    $q->where('stop_name', 'like', '%' . $request->to_stop . '%');
                }
            });
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bus_number' => 'required|unique:buses',
            'capacity' => 'integer|min:1',
        ]);

        $bus = Bus::create([
            'bus_number' => $request->bus_number,
            'capacity' => $request->capacity ?? 50,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($bus, 201);
    }

    public function show(Bus $bus)
    {
        $bus->load(['routes.stops', 'routes.timetables', 'currentDriver']);
        $location = Cache::get("bus_{$bus->id}_location");

        return response()->json([
            'bus' => $bus,
            'current_location' => $location
        ]);
    }

    public function update(Request $request, Bus $bus)
    {
        $request->validate([
            'bus_number' => 'string|unique:buses,bus_number,' . $bus->id,
            'capacity' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $bus->update($request->all());

        return response()->json($bus);
    }

    public function destroy(Bus $bus)
    {
        $bus->delete();
        return response()->json(['message' => 'Bus deleted successfully']);
    }

    public function selectBus(Request $request, Bus $bus)
    {
        $user = $request->user();

        if (!$user->isDriver()) {
            return response()->json(['message' => 'Only drivers can select a bus.'], 403);
        }

        $user->update(['current_bus_id' => $bus->id]);

        return response()->json(['message' => "You have selected Bus #{$bus->bus_number}"]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'nullable|numeric',
        ]);

        UpdateBusLocationJob::dispatch(
            $request->bus_id,
            $request->lat,
            $request->lng,
            $request->speed,
            $request->user()->id
        );

        return response()->json(['status' => 'ok']);
    }
}
