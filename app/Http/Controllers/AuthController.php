<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'personal_account' => 'required',
            'password' => 'required',
            'device' => 'required',
        ]);

        $user = User::where('personal_account', $request->personal_account)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid personal account or password'
            ], 401);
        }

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'personal_account' => 'required|string|unique:users,personal_account',
            'password' => ['required', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'device' => 'required|string',
            'residential_complex_id' => 'nullable|integer|exists:residential_complexes,id',
            'block_number' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'personal_account' => $request->personal_account,
            'password' => Hash::make($request->password),
            'residential_complex_id' => $request->residential_complex_id,
            'block_number' => $request->block_number,
            'apartment_number' => $request->apartment_number,
        ]);

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $input = $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => ['nullable', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'residential_complex_id' => 'nullable|integer|exists:residential_complexes,id',
            'block_number' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }

        $user->update(array_filter($input));

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
