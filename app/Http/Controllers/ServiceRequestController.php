<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png',
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/service', 'public');
                $serviceRequest->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return response()->json($serviceRequest, 201);
    }
}
