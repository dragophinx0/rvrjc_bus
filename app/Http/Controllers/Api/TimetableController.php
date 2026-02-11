<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $query = Timetable::with(['route', 'stop']);

        if ($request->has('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->has('shift')) {
            $query->where('shift', $request->shift);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'route_id' => 'required|exists:routes,id',
            'stop_id' => 'required|exists:stops,id',
            'shift' => 'required|in:first,second',
            'arrival_time' => 'required',
        ]);

        $timetable = Timetable::create([
            'route_id' => $request->route_id,
            'stop_id' => $request->stop_id,
            'shift' => $request->shift,
            'arrival_time' => $request->arrival_time,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($timetable, 201);
    }

    public function update(Request $request, Timetable $timetable)
    {
        $timetable->update($request->all());
        return response()->json($timetable);
    }

    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return response()->json(['message' => 'Timetable entry deleted successfully']);
    }
}
