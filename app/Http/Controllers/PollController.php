<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PollController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        $polls = Poll::where('residential_complex_id', $user->residential_complex_id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($polls);
    }

    /*public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $poll = Poll::create([
            'title' => $request->title,
            'description' => $request->description,
            'residential_complex_id' => $user->residential_complex_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json($poll, 201);
    }*/

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'vote' => 'required|in:yes,no,abstain',
        ]);

        $user = Auth::guard('sanctum')->user();

        if ((int)$poll->residential_complex_id !== (int)$user->residential_complex_id) {
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

    public function show(Poll $poll)
    {
        $user = Auth::guard('sanctum')->user();

        if ((int)$poll->residential_complex_id !== (int)$user->residential_complex_id) {
            return response()->json(['message' => 'Опрос недоступен'], 403);
        }

        $yesVotes = PollVote::where('poll_id', $poll->id)->where('vote', 'yes')->count();
        $noVotes = PollVote::where('poll_id', $poll->id)->where('vote', 'no')->count();
        $abstainVotes = PollVote::where('poll_id', $poll->id)->where('vote', 'abstain')->count();

        return response()->json([
            'poll' => $poll,
            'votes' => [
                'yes' => $yesVotes,
                'no' => $noVotes,
                'abstain' => $abstainVotes,
            ]
        ]);
    }

    public function generateProtocol(Poll $poll)
    {
        $votes = $poll->votes()->with('user')->get();

        $yesVotes = $votes->where('vote', 'yes');
        $noVotes = $votes->where('vote', 'no');
        $abstainVotes = $votes->where('vote', 'abstain');

        $data = [
            'poll' => $poll,
            'totalVotes' => $votes->count(),
            'yesCount'    => $yesVotes->count(),
            'noCount'     => $noVotes->count(),
            'abstainCount'=> $abstainVotes->count(),
            'yesVoters'   => $yesVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'noVoters'    => $noVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'abstainVoters' => $abstainVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'residentialComplex' => $poll->residentialComplex,
        ];

        $pdf = PDF::loadView('pdf.poll_protocol', $data);

        return $pdf->download("poll_protocol_{$poll->id}.pdf");
    }
}
