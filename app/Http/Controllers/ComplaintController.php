<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/complaint');
                $complaint->photos()->create(['path' => $path]);
            }
        }

        return response()->json($complaint, 201);
    }

    public function index()
    {
        $user = Auth::user();

        $complaints = Complaint::with('photos')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($complaints);
    }

    public function show($id)
    {
        $complaint = Complaint::with('photos')->find($id);

        if (!$complaint) {
            return response()->json(['message' => 'Жалоба не найдена'], 404);
        }

        return response()->json($complaint);
    }
}
