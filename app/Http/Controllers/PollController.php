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

        $polls = Poll::where(function ($query) use ($user) {
            $query->where('residential_complex_id', $user->residential_complex_id)
                ->where('type', 'complex');
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('building_number', $user->building_number)
                    ->where('type', 'building');
            })
            ->with('options')
            ->get();

        return response()->json($polls);
    }

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'poll_option_id' => 'required|exists:poll_options,id',
        ]);

        $existingVote = PollVote::where('poll_id', $poll->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($existingVote) {
            return response()->json(['message' => 'Вы уже проголосовали'], 400);
        }

        PollVote::create([
            'poll_id' => $poll->id,
            'user_id' => $request->user()->id,
            'poll_option_id' => $request->poll_option_id,
        ]);

        return response()->json(['message' => 'Ваш голос учтен']);
    }
}
