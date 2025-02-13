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
        ]);

        $serviceRequest = ServiceRequest::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json($serviceRequest, 201);
    }
}
