<?php

namespace App\Http\Controllers;

use App\Models\ResidentialComplex;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAdminController extends Controller
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

        $query = User::with('residentialComplex')
            ->whereHas('residentialComplex', function ($q) use ($admin, $request) {
                $q->where('client_id', $admin->client_id);

                if ($request->filled('residential_complex_id')) {
                    $q->where('id', $request->input('residential_complex_id'));
                }
            });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('personal_account', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('login', 'like', "%{$search}%");
            });
        }

        $residents = $query
            ->orderBy('name')
            ->paginate(20);

        return response()->json($residents);
    }

    public function deleteUser($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->residentialComplex->client_id !== $admin->client_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function updateUser(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->residentialComplex->client_id !== $admin->client_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'login' => 'nullable|string',
            'personal_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'name' => 'nullable|string',
            'block_number' => 'nullable|string',
            'apartment_number' => 'nullable|string',
            'non_residential_premises' => 'nullable|string',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'language' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function createTenantByOwnerId(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'new_login' => 'required|string',
            'name' => 'required|string',
            'phone_number' => 'nullable|string',
            'password' => 'required|string',
            'role' => 'nullable|in:tenant,family'
        ]);

        $owner = User::find($id);

        if (!$owner) {
            return response()->json(['message' => 'Owner not found'], 404);
        }

        if (!$owner->residentialComplex || $owner->residentialComplex->client_id !== $admin->client_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $fullLogin = $owner->login . '_' . $validated['new_login'];

        if (User::where('login', $fullLogin)->exists()) {
            return response()->json(['message' => 'Login already exists'], 422);
        }

        $role = $validated['role'] ?? 'tenant';
        $tenant = User::create([
            'login' => $fullLogin,
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => bcrypt($validated['password']),
            'role' => $role,
            'residential_complex_id' => $owner->residential_complex_id,
            'block_number' => $owner->block_number,
            'apartment_number' => $owner->apartment_number,
            'non_residential_premises' => $owner->non_residential_premises,
            'personal_account' => null,
        ]);

        return response()->json([
            'message' => 'Tenant created successfully',
            'user' => $tenant,
        ]);
    }

    public function createUser(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $validated = $request->validate([
            'login' => 'required|string|unique:users,login',
            'personal_account' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'name' => 'required|string',
            'password' => 'required|string',
            'block_number' => 'nullable|string',
            'apartment_number' => 'nullable|string',
            'non_residential_premises' => 'nullable|string',
            'residential_complex_id' => 'required|exists:residential_complexes,id',
            'language' => 'nullable|string',
            'role' => 'nullable|in:owner,tenant,family',
        ]);

        $residentialComplex = ResidentialComplex::where('id', $validated['residential_complex_id'])
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$residentialComplex) {
            return response()->json(['message' => 'Forbidden: residential complex not belongs to this client'], 403);
        }

        $user = User::create([
            'login' => $validated['login'],
            'personal_account' => $validated['personal_account'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'name' => $validated['name'],
            'password' => bcrypt($validated['password']),
            'block_number' => $validated['block_number'] ?? null,
            'apartment_number' => $validated['apartment_number'] ?? null,
            'non_residential_premises' => $validated['non_residential_premises'] ?? null,
            'residential_complex_id' => $validated['residential_complex_id'],
            'language' => $validated['language'] ?? null,
            'role' => $validated['role'] ?? 'owner',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }
}