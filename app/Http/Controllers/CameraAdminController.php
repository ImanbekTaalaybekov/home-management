<?php

namespace App\Http\Controllers;

use App\Models\ResidentialCamera;
use App\Models\ResidentialComplex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CameraAdminController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $query = ResidentialCamera::with('residentialComplex')
            ->whereHas('residentialComplex', function ($q) use ($admin, $request) {
                $q->where('client_id', $admin->client_id);

                if ($residentialComplexId = $request->query('residential_complex_id')) {
                    $q->where('id', $residentialComplexId);
                }
            });

        $cameras = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($cameras);
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'residential_complex_id' => 'required|exists:residential_complexes,id',
            'type'  => 'required|in:hikvision,dahua',
            'name'  => 'required|string|max:255',
            'link'  => 'nullable|string|max:500',
        ]);

        $complex = ResidentialComplex::where('id', $request->residential_complex_id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$complex) {
            return response()->json(['message' => 'Residential complex not found or forbidden'], 404);
        }

        $camera = ResidentialCamera::create([
            'residential_complex_id' => $complex->id,
            'type'  => $request->type,
            'name'  => $request->name,
            'link'  => $request->link,
        ]);

        return response()->json($camera->load('residentialComplex'), 201);
    }

    public function show($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $camera = ResidentialCamera::with('residentialComplex')
            ->where('id', $id)
            ->whereHas('residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$camera) {
            return response()->json(['message' => 'Camera not found'], 404);
        }

        return response()->json($camera);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $camera = ResidentialCamera::with('residentialComplex')
            ->where('id', $id)
            ->whereHas('residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$camera) {
            return response()->json(['message' => 'Camera not found'], 404);
        }

        $request->validate([
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'type'  => 'nullable|in:hikvision,dahua',
            'name'  => 'nullable|string|max:255',
            'link'  => 'nullable|string|max:500',
        ]);

        $data = [];

        if ($request->filled('residential_complex_id')) {
            $complex = ResidentialComplex::where('id', $request->residential_complex_id)
                ->where('client_id', $admin->client_id)
                ->first();

            if (!$complex) {
                return response()->json(['message' => 'Residential complex not found or forbidden'], 404);
            }

            $data['residential_complex_id'] = $complex->id;
        }

        if ($request->filled('type')) {
            $data['type'] = $request->type;
        }

        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }

        if ($request->filled('link')) {
            $data['link'] = $request->link;
        }

        if (!empty($data)) {
            $camera->update($data);
        }

        return response()->json($camera->load('residentialComplex'));
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $camera = ResidentialCamera::where('id', $id)
            ->whereHas('residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$camera) {
            return response()->json(['message' => 'Camera not found'], 404);
        }

        $camera->delete();

        return response()->json(['message' => 'Camera deleted successfully']);
    }
}
