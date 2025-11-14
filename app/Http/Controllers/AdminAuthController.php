<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Models\FcmUserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'username'   => 'required|string',
            'password'=> 'required|string',
            'device'  => 'required|string',
        ]);

        $user = Admin::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверные данные для входа в систему'], 401);
        }

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new AdminResource($user),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'password' => 'required',
            'client_id' => 'required',
            'accesses' => 'required|string',
            'device' => 'required|string',
        ]);

        $user = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'client_id' => $request->client_id,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'accesses' => $request->accesses
        ]);

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new AdminResource($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => new AdminResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $pat  = $user->currentAccessToken();

        if ($pat) {
            FcmUserToken::where('user_id', $user->id)
                ->where('device', $pat->name)
                ->delete();

            $pat->delete();
        }

        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device'    => 'required|string',
        ]);

        $user   = $request->user();
        $device = (string) $request->input('device');

        FcmUserToken::updateOrCreate(
            ['user_id' => $user->id, 'device' => $device],
            ['fcm_token' => $request->fcm_token]
        );

        return response()->json(['message' => 'FCM-токен сохранён']);
    }

    public function removeFcmToken(Request $request)
    {
        $request->validate([
            'device' => 'required|string',
        ]);

        $user   = $request->user();
        $device = (string) $request->input('device');

        FcmUserToken::where('user_id', $user->id)
            ->where('device', $device)
            ->delete();

        return response()->json(['message' => 'FCM-токен удалён']);
    }
}