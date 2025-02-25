<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'personal_account' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'device' => 'required',
        ]);

        $user = User::where('personal_account', $request->personal_account)
            ->where('phone_number', $request->phone_number)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid personal account or password'
            ], 401);
        }

        $code = 1111; //Затычка, затем заменить на mt_rand(1000,9999)
        $expiresAt = now()->addMinutes(5);

        VerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => $expiresAt]
        );

        $this->sendSms($user->phone_number, "Ваш код подтверждения: $code");

        return response()->json([
            'message' => 'SMS code sent',
            'requires_verification' => true,
            'user_id' => $user->id,
        ]);
    }

    private function sendSms($phoneNumber, $message)
    {
    //Добавить отправку СМС исходя от выбранного оператора (KCELL)
    }

    public function verifySmsCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required',
        ]);

        $verificationCode = VerificationCode::where('user_id', $request->user_id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'message' => 'Invalid or expired code'
            ], 401);
        }

        $verificationCode->delete();
        $user = User::find($request->user_id);
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
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => ['required', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'device' => 'required|string',
            'residential_complex_id' => 'nullable|integer|exists:residential_complexes,id',
            'block_number' => 'nullable|string|max:255',
            'apartment_number' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'personal_account' => $request->personal_account,
            'phone_number' => $request->phone_number,
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
