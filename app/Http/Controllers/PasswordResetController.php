<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->input('email');

        $token = strtoupper(Str::random(8));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $user = User::where('email', $email)->first();
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['success' => true, 'message' => 'Код сброса отправлен на почту']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'device' => 'required',
        ]);

        $email = $request->input('email');
        $newPassword = $request->input('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user' => new UserResource($user),
        ]);
    }


    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'token' => 'required'
        ]);

        return response()->json(['success' => true]);
    }
}
