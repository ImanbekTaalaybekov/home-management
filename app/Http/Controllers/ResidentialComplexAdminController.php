<?php

namespace App\Http\Controllers;

use App\Models\ResidentialComplex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentialComplexAdminController extends Controller
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

        $query = ResidentialComplex::where('client_id', $admin->client_id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $complexes = $query
            ->orderBy('name')
            ->paginate(20);

        return response()->json($complexes);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $complex = ResidentialComplex::find($id);

        if (!$complex) {
            return response()->json(['message' => 'Residential complex not found'], 404);
        }

        if ($complex->client_id !== $admin->client_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name'    => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $complex->update($validated);

        return response()->json([
            'message' => 'Residential complex updated successfully',
            'data'    => $complex,
        ]);
    }

    public function destroy($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $complex = ResidentialComplex::find($id);

        if (!$complex) {
            return response()->json(['message' => 'Residential complex not found'], 404);
        }

        if ($complex->client_id !== $admin->client_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $complex->delete();

        return response()->json([
            'message' => 'Residential complex deleted successfully',
        ]);
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

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $complex = ResidentialComplex::create([
            'name'      => $validated['name'],
            'address'   => $validated['address'],
            'client_id' => $admin->client_id,
        ]);

        return response()->json([
            'message' => 'Residential complex created successfully',
            'data'    => $complex,
        ], 201);
    }
}