<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusTrip;
use App\Models\Seat;
use App\Models\SeatReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeatReservationController extends Controller
{
    /**
     * Reserve a seat (HOLD for 5 minutes)
     */
    public function reserve(Request $request, BusTrip $trip, Seat $seat)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if (!$trip->isBoarding() && !$trip->isRunning()) {
            return response()->json(['message' => 'Boarding is not open for this trip'], 422);
        }

        // Proximity Check
        $busLocation = Cache::get("bus_{$trip->bus_id}_location");
        if (!$busLocation) {
            return response()->json(['message' => 'Bus location not available'], 422);
        }

        $distance = $this->calculateDistance($request->lat, $request->lng, $busLocation['lat'], $busLocation['lng']);

        if ($distance > 50) { // 50 meters
            return response()->json(['message' => 'You are not near the bus. Distance: ' . round($distance) . 'm'], 403);
        }

        // Check if seat is already taken
        $existing = SeatReservation::where('bus_trip_id', $trip->id)
            ->where('seat_id', $seat->id)
            ->where(function ($q) {
                $q->where('status', 'confirmed')
                    ->orWhere('expires_at', '>', now());
            })->first();

        if ($existing) {
            return response()->json(['message' => 'Seat is already reserved or occupied'], 422);
        }

        // One reservation per user per trip
        SeatReservation::where('bus_trip_id', $trip->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        $reservation = SeatReservation::create([
            'bus_trip_id' => $trip->id,
            'seat_id' => $seat->id,
            'user_id' => $request->user()->id,
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(5),
        ]);

        return response()->json([
            'message' => 'Seat reserved for 5 minutes. Please confirm once the journey begins.',
            'reservation' => $reservation
        ]);
    }

    /**
     * Confirm a reserved seat (Only after journey starts)
     */
    public function confirm(Request $request, SeatReservation $reservation)
    {
        if (!$reservation->trip->journey_started_at) {
            return response()->json(['message' => 'You can only confirm your seat after the journey starts'], 422);
        }

        if ($reservation->isExpired()) {
            return response()->json(['message' => 'Reservation expired'], 422);
        }

        $reservation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'expires_at' => null
        ]);

        return response()->json(['message' => 'Seat confirmed! Enjoy your journey.']);
    }

    /**
     * Helper to extend timer if user is still near but need more time
     */
    public function extend(Request $request, SeatReservation $reservation)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $busLocation = Cache::get("bus_{$reservation->trip->bus_id}_location");
        $distance = $this->calculateDistance($request->lat, $request->lng, $busLocation['lat'], $busLocation['lng']);

        if ($distance > 50) {
            return response()->json(['message' => 'Cannot extend. You are too far from the bus.'], 403);
        }

        $reservation->update(['expires_at' => now()->addMinutes(5)]);

        return response()->json(['message' => 'Reservation extended by 5 minutes.']);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }
}
