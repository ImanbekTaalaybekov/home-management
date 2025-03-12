<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('service_requests', 'public');

                $serviceRequest->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return response()->json($serviceRequest, 201);
    }
}
