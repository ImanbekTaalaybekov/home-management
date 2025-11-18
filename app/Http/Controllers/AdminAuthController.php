<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminResource;
use App\Models\Admin;
use App\Models\FcmAdminToken;
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
        $admin = $request->user();

        $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'password' => 'required',
            'accesses' => 'required',
            'device' => 'required|string',
        ]);

        $user = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'client_id' => $admin->client_id,
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
            FcmAdminToken::where('user_id', $user->id)
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

        FcmAdminToken::updateOrCreate(
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

        FcmAdminToken::where('user_id', $user->id)
            ->where('device', $device)
            ->delete();

        return response()->json(['message' => 'FCM-токен удалён']);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Пользователь не найден',
            ], 404);
        }

        if ($request->has('username')) {
            $admin->username = $request->username;
        }

        if ($request->has('name')) {
            $admin->name = $request->name;
        }

        if ($request->has('role')) {
            $admin->role = $request->role;
        }

        if ($request->has('accesses')) {
            $admin->accesses = $request->accesses;
        }

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return response()->json([
            'message' => 'Данные пользователя обновлены',
            'user'    => new AdminResource($admin),
        ]);
    }

    public function delete(Request $request, $id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Пользователь не найден',
            ], 404);
        }

        FcmUserToken::where('user_id', $admin->id)->delete();

        if (method_exists($admin, 'tokens')) {
            $admin->tokens()->delete();
        }

        $admin->delete();

        return response()->json([
            'message' => 'Пользователь успешно удалён',
        ]);
    }

    public function listByRole(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'role' => 'sometimes|string',
        ]);

        $role = $request->query('role');

        $query = Admin::query()
            ->where('client_id', $admin->client_id);

        if (!empty($role)) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('id')->get();

        return response()->json([
            'users' => AdminResource::collection($users),
        ]);
    }
}