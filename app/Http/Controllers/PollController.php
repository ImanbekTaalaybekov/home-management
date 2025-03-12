<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $polls = Poll::where('residential_complex_id', $user->residential_complex_id)
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($polls);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'residential_complex_id' => 'required|exists:residential_complexes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $poll = Poll::create($request->all());

        return response()->json($poll, 201);
    }

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'vote' => 'required|in:yes,no',
        ]);

        $user = $request->user();

        if ($poll->residential_complex_id !== $user->residential_complex_id) {
            return response()->json(['message' => 'Вы не можете голосовать в этом опросе'], 403);
        }

        $existingVote = PollVote::where('poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existingVote) {
            return response()->json(['message' => 'Вы уже проголосовали'], 400);
        }

        PollVote::create([
            'poll_id' => $poll->id,
            'user_id' => $user->id,
            'vote' => $request->vote,
        ]);

        return response()->json(['message' => 'Ваш голос учтен']);
    }

    public function show(Poll $poll, Request $request)
    {
        $user = $request->user();

        if ($poll->residential_complex_id !== $user->residential_complex_id) {
            return response()->json(['message' => 'Опрос недоступен'], 403);
        }

        $yesVotes = PollVote::where('poll_id', $poll->id)->where('vote', 'yes')->count();
        $noVotes = PollVote::where('poll_id', $poll->id)->where('vote', 'no')->count();

        return response()->json([
            'poll' => $poll,
            'votes' => [
                'yes' => $yesVotes,
                'no' => $noVotes,
            ]
        ]);
    }
}
