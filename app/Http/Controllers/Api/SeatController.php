<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Seat;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    /**
     * Generate seats for a bus based on its layout_type and rows
     */
    public function generateLayout(Request $request, Bus $bus)
    {
        $rows = $bus->rows ?? 10;
        $layout = $bus->layout_type; // '2x2' or '3x2'

        // Delete existing seats
        $bus->seats()->delete();

        // Create Driver Seat (Row 0, Col 0)
        Seat::create([
            'bus_id' => $bus->id,
            'row' => 0,
            'column' => 0,
            'label' => 'Driver',
            'type' => 'driver'
        ]);

        for ($r = 1; $r <= $rows; $r++) {
            if ($layout === '3x2') {
                // Column 1-3 (Left side)
                for ($c = 1; $c <= 3; $c++) {
                    Seat::create([
                        'bus_id' => $bus->id,
                        'row' => $r,
                        'column' => $c,
                        'label' => $r . $this->getColLabel($c, '3x2'),
                        'type' => $this->getSeatType($c, '3x2')
                    ]);
                }
                // Column 4 (Aisle) - Optional, we can just skip it in UI or mark it
                // Column 5-6 (Right side)
                for ($c = 5; $c <= 6; $c++) {
                    Seat::create([
                        'bus_id' => $bus->id,
                        'row' => $r,
                        'column' => $c,
                        'label' => $r . $this->getColLabel($c, '3x2'),
                        'type' => $this->getSeatType($c, '3x2')
                    ]);
                }
            } else { // 2x2
                // Column 1-2 (Left)
                for ($c = 1; $c <= 2; $c++) {
                    Seat::create([
                        'bus_id' => $bus->id,
                        'row' => $r,
                        'column' => $c,
                        'label' => $r . $this->getColLabel($c, '2x2'),
                        'type' => $this->getSeatType($c, '2x2')
                    ]);
                }
                // Column 4-5 (Right)
                for ($c = 4; $c <= 5; $c++) {
                    Seat::create([
                        'bus_id' => $bus->id,
                        'row' => $r,
                        'column' => $c,
                        'label' => $r . $this->getColLabel($c, '2x2'),
                        'type' => $this->getSeatType($c, '2x2')
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Layout generated successfully', 'seats' => $bus->seats]);
    }

    private function getColLabel($col, $layout)
    {
        $labels = [
            '3x2' => [1 => 'W', 2 => 'M', 3 => 'A', 5 => 'A', 6 => 'W'],
            '2x2' => [1 => 'W', 2 => 'A', 4 => 'A', 5 => 'W']
        ];
        return $labels[$layout][$col] ?? '';
    }

    private function getSeatType($col, $layout)
    {
        $types = [
            '3x2' => [1 => 'window', 2 => 'middle', 3 => 'aisle', 5 => 'aisle', 6 => 'window'],
            '2x2' => [1 => 'window', 2 => 'aisle', 4 => 'aisle', 5 => 'window']
        ];
        return $types[$layout][$col] ?? 'seat';
    }

    public function getBusLayout(Bus $bus)
    {
        return response()->json([
            'bus' => $bus,
            'seats' => $bus->seats()->with([
                'reservations' => function ($q) use ($bus) {
                    $q->where('bus_trip_id', $bus->current_trip_id);
                }
            ])->get()
        ]);
    }
}
