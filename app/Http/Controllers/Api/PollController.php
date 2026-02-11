<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Get active polls for the user
     */
    public function index(Request $request)
    {
        $polls = Poll::where('is_active', true)
            ->with([
                'votes' => function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }
            ])
            ->get();

        return response()->json($polls);
    }

    /**
     * Vote in a poll
     */
    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'is_going' => 'required|boolean',
            'stop_id' => 'required_if:is_going,true|exists:stops,id',
        ]);

        if (!$poll->is_active) {
            return response()->json(['message' => 'This poll is no longer active'], 422);
        }

        $vote = PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => $request->user()->id],
            [
                'is_going' => $request->is_going,
                'stop_id' => $request->is_going ? $request->stop_id : null
            ]
        );

        return response()->json([
            'message' => 'Vote recorded successfully',
            'vote' => $vote
        ]);
    }

    /**
     * Coordinator Dashboard: Demand Prediction
     */
    public function stats(Request $request)
    {
        $pollType = $request->query('type', 'morning');
        $date = $request->query('date', now()->toDateString());

        $poll = Poll::where('type', $pollType)->where('date', $date)->first();

        if (!$poll) {
            return response()->json(['message' => 'No poll found for this date'], 404);
        }

        $stats = $poll->votes()
            ->where('is_going', true)
            ->with(['user', 'stop'])
            ->get()
            ->groupBy('stop_id')
            ->map(function ($votes, $stopId) {
                $stop = $votes->first()->stop;
                return [
                    'stop_name' => $stop ? $stop->name : 'Unknown',
                    'total' => $votes->count(),
                    'males' => $votes->where('user.gender', 'male')->count(),
                    'females' => $votes->where('user.gender', 'female')->count(),
                    'students' => $votes->where('user.role', 'student')->count(),
                    'faculty' => $votes->where('user.role', 'faculty')->count(),
                ];
            });

        return response()->json([
            'poll' => $poll,
            'stats' => $stats,
            'total_demand' => $poll->votes()->where('is_going', true)->count()
        ]);
    }

    /**
     * Create daily polls (Scheduled command helper)
     */
    public function createDailyPolls()
    {
        // Tomorrow's Morning Poll
        Poll::firstOrCreate([
            'type' => 'morning',
            'date' => now()->addDay()->toDateString()
        ]);

        // Today's Evening Poll
        Poll::firstOrCreate([
            'type' => 'evening',
            'date' => now()->toDateString()
        ]);
    }
}
