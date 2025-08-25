<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function getCategories()
    {
        return response()->json(ServiceRequestCategory::all());
    }

    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $requests = ServiceRequest::with('photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($requests);
    }

    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();

        $serviceRequest = ServiceRequest::with('photos')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Заявка не найдена'], 404);
        }

        return response()->json($serviceRequest);
    }

    public function remove($id)
    {
        $user = Auth::guard('sanctum')->user();

        $serviceRequest = ServiceRequest::with('photos')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Заявка не найдена'], 404);
        }

        DB::transaction(function () use ($serviceRequest) {
            foreach ($serviceRequest->photos as $photo) {
                $relative = $photo->path ?? '';

                if ($relative !== '' && !Str::startsWith($relative, ['photos/'])) {
                    $relative = 'photos/service/' . ltrim($relative, '/');
                }

                if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                    Storage::disk('public')->delete($relative);
                }

                $photo->delete();
            }

            $serviceRequest->delete();
        });

        return response()->json(['message' => 'Заявка и связанные фото удалены']);
    }

    public function rate(Request $request, $id)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
        ]);

        $serviceRequest = ServiceRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Заявка не найдена'], 404);
        }

        $serviceRequest->rate = $request->rate;
        $serviceRequest->save();

        return response()->json([
            'message' => 'Оценка сохранена',
            'service_request' => $serviceRequest
        ]);
    }
}
