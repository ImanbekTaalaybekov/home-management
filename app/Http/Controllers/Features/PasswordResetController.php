<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Rules\ValidPasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $status = Password::sendResetLink($request->only('email'));
        return response()->json(['success' => $status === Password::RESET_LINK_SENT]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['bail','required', 'string'],
            'email' => ['required', 'email', new ValidPasswordResetToken($request->input('token'))],
            'password' => 'required|min:8',
            'device' => 'required',
        ]);

        $email = $request->input('email');
        $newPassword = $request->input('password');

        $user = User::where('email', $email)->first();
        $user->password = $newPassword;
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
            'email' => ['required', 'email', new ValidPasswordResetToken($request->input('token'))],
            'token' => 'required'
        ]);

        return response()->json(['success' => true]);
    }
}
