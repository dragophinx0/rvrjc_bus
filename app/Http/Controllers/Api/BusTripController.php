<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusTrip;
use Illuminate\Http\Request;

class BusTripController extends Controller
{
    /**
     * Driver starts the boarding window
     */
    public function startBoarding(Request $request, Bus $bus)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'shift' => 'required|in:first,second',
            'trip_type' => 'required|in:pickup,drop',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $trip = BusTrip::create([
            'bus_id' => $bus->id,
            'route_id' => $request->route_id,
            'driver_id' => $request->user()->id,
            'shift' => $request->shift,
            'trip_type' => $request->trip_type,
            'boarding_started_at' => now(),
            'start_lat' => $request->lat,
            'start_lng' => $request->lng,
        ]);

        $bus->update(['current_trip_id' => $trip->id]);

        return response()->json(['message' => 'Boarding window opened', 'trip' => $trip]);
    }

    /**
     * Driver starts the journey (Locked seats)
     */
    public function startJourney(Request $request, BusTrip $trip)
    {
        if ($trip->journey_started_at) {
            return response()->json(['message' => 'Journey already started'], 422);
        }

        $trip->update(['journey_started_at' => now()]);

        return response()->json(['message' => 'Journey started. Students can now confirm their seats.']);
    }

    /**
     * Complete the trip
     */
    public function completeTrip(Request $request, BusTrip $trip)
    {
        $trip->update(['completed_at' => now()]);
        $trip->bus->update(['current_trip_id' => null]);

        return response()->json(['message' => 'Trip completed successfully']);
    }
}
