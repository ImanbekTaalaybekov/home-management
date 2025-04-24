<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $user = User::where('personal_account', $request->personal_account)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Неверные данные для входа в систему'
            ], 401);
        }

        if ($user->phone_number !== $request->phone_number) {
            $user->phone_number = $request->phone_number;
            $user->save();
        }

        $code =  mt_rand(1000,9999);
        $expiresAt = now()->addMinutes(5);

        VerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => $expiresAt]
        );

        $this->sendSms($user->phone_number, "Ваш код подтверждения: $code. Сообщение от wires-home-kz");

        return response()->json([
            'message' => 'SMS code sent',
            'requires_verification' => true,
            'user_id' => $user->id,
        ]);
    }

    private function sendSms($phoneNumber, $message)
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        $response = Http::get('http://kazinfoteh.org:9507/api', [
            'action' => 'sendmessage',
            'username' => env('KAZINFOTEH_USERNAME'),
            'password' => env('KAZINFOTEH_PASSWORD'),
            'recipient' => $formattedPhone,
            'messagetype' => 'SMS:TEXT',
            'originator' => 'KiT_Notify',
            'messagedata' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Ошибка отправки SMS через KazInfoTeh: ' . $response->body());
        }
    }

    private function sendSmsHttps($phoneNumber, $message)
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        $login = env('KAZINFOTEH_USERNAME');
        $password = env('KAZINFOTEH_PASSWORD');
        $token = base64_encode("$login:$password");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://so.kazinfoteh.org/api/sms/send', [
            'from' => 'KiT_Notify',
            'to' => $formattedPhone,
            'text' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Ошибка отправки SMS через KazInfoTeh HTTPS: ' . $response->body());
        }
    }

    private function formatPhoneNumber($phoneNumber)
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber);

        if (substr($digits, 0, 1) === '8') {
            $digits = '7' . substr($digits, 1);
        }

        return substr($digits, 0, 11);
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
                'message' => 'Код верификации неверный либо истек'
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
        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token
        ]);

        return response()->json(['message' => 'FCM-токен обновлён']);
    }
}
