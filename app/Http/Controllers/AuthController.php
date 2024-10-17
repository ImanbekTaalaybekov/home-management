<?php

namespace App\Http\Controllers;

use App\Enums\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password'
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
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'device' => 'required',
            'city_id' => 'required|integer|exists:cities,id',
            'language' => ['nullable', Rule::enum(Language::class)],
        ]);

        $user = User::create([
            'email' => $request->email,
            'language' => $request->language,
            'password' => $request->password,
            'city_id' => $request->city_id
        ]);

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'notification' => $user->notifications,
            'user' => new UserResource($request->user()),
        ]);
    }

    public function update(Request $request)
    {
        $input = $request->validate([
            'language' => ['nullable', Rule::enum(Language::class)],
            'password' => ['nullable', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'notifications' => ['nullable', 'array'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $user = $request->user();
        $input = array_filter($input);
        $user->update($input);

        return response()->json([
            'notification' => $user->notifications,
            'user' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
