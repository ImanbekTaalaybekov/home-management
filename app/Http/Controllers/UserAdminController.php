<?php

namespace App\Http\Controllers;

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
            'login'                 => 'nullable|string',
            'personal_account'      => 'nullable|string',
            'phone_number'          => 'nullable|string',
            'name'                  => 'nullable|string',
            'block_number'          => 'nullable|string',
            'apartment_number'      => 'nullable|string',
            'non_residential_premises' => 'nullable|string',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'language'              => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user,
        ]);
    }

    public function createTenantByOwnerId(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'new_login'   => 'required|string',
            'name'        => 'required|string',
            'phone_number' => 'nullable|string',
            'role'        => 'nullable|in:tenant,family'
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
            'login'                  => $fullLogin,
            'name'                   => $validated['name'],
            'phone_number'           => $validated['phone_number'] ?? null,
            'password'               => bcrypt('default123'),
            'role'                   => $role,
            'residential_complex_id' => $owner->residential_complex_id,
            'block_number'           => $owner->block_number,
            'apartment_number'       => $owner->apartment_number,
            'non_residential_premises' => $owner->non_residential_premises,
            'personal_account'       => null,
        ]);

        return response()->json([
            'message' => 'Tenant created successfully',
            'user'    => $tenant,
        ]);
    }

}