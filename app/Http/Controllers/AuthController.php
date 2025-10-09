<?php

namespace App\Http\Controllers;

use App\Models\FcmUserToken;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'login'   => 'required|string',
            'password'=> 'required|string',
            'device'  => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверные данные для входа в систему'], 401);
        }

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new UserResource($user),
        ]);
    }

    public function requestPhoneVerification(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $user = $request->user();
        $normalizedPhone = $this->normalizePhone($request->phone_number);

        if (!$normalizedPhone) {
            return response()->json([
                'message' => 'Некорректный номер телефона',
            ], 422);
        }

        $exists = User::where('phone_number', $normalizedPhone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Этот номер уже используется другим пользователем',
            ], 422);
        }

        $expiresAt = now()->addMinutes(5);

        $code = random_int(1000, 9999);
        $this->sendSms($normalizedPhone, "Ваш код подтверждения: {$code}. Сообщение от ZD Home");

        VerificationCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => $expiresAt]
        );

        Cache::put($this->pendingPhoneCacheKey($user->id), $normalizedPhone, $expiresAt);

        return response()->json([
            'message'     => 'SMS code sent',
            'expires_at'  => $expiresAt->toIso8601String(),
        ]);
    }

    public function confirmPhoneVerification(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'Код верификации неверный либо истёк'], 401);
        }

        $pendingPhone = Cache::get($this->pendingPhoneCacheKey($user->id));
        if (!$pendingPhone) {
            return response()->json(['message' => 'Не найден ожидающий подтверждения номер. Запросите код ещё раз.'], 422);
        }

        $exists = User::where('phone_number', $pendingPhone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Этот номер уже используется другим пользователем'], 422);
        }

        $user->phone_number = $pendingPhone;
        $user->save();

        $verificationCode->delete();
        Cache::forget($this->pendingPhoneCacheKey($user->id));

        return response()->json([
            'message' => 'Номер телефона успешно подтверждён и сохранён',
            'user'    => new UserResource($user),
        ]);
    }

    private function pendingPhoneCacheKey(int $userId): string
    {
        return "phone_update:{$userId}";
    }

    private function normalizePhone(?string $value): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        if ($v === '') return null;

        $v = preg_replace('/[^\d+]/', '', $v);

        if (substr($v, 0, 1) !== '+') {
            $v = '+' . preg_replace('/\D/', '', $v);
        } else {
            $v = '+' . preg_replace('/\D/', '', substr($v, 1));
        }

        $digits = substr($v, 1, 15);

        return $digits === '' ? null : ('+' . $digits);
    }

    private function sendSms($phoneNumber, $message)
    {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        $response = Http::get('http://kazinfoteh.org:9507/api', [
            'action'      => 'sendmessage',
            'username'    => env('KAZINFOTEH_USERNAME'),
            'password'    => env('KAZINFOTEH_PASSWORD'),
            'recipient'   => $formattedPhone,
            'messagetype' => 'SMS:TEXT',
            'originator'  => 'ZD Home',
            'messagedata' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Ошибка отправки SMS через KazInfoTeh: ' . $response->body());
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
            'code'    => 'required',
        ]);

        $verificationCode = VerificationCode::where('user_id', $request->user_id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'Код верификации неверный либо истек'], 401);
        }

        $verificationCode->delete();
        $user  = User::find($request->user_id);
        $token = $user->createToken($request->device ?? 'device')->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new UserResource($user),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'login'                  => 'required|string|max:255|unique:users,login',
            'personal_account'       => 'required|string|unique:users,personal_account',
            'phone_number'           => 'required|string|unique:users,phone_number',
            'password'               => ['required', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'device'                 => 'required|string',
            'residential_complex_id' => 'nullable|integer|exists:residential_complexes,id',
            'block_number'           => 'nullable|string|max:255',
            'apartment_number'       => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name'                   => $request->name,
            'login'                  => $request->login,
            'personal_account'       => $request->personal_account,
            'phone_number'           => $request->phone_number,
            'password'               => Hash::make($request->password),
            'residential_complex_id' => $request->residential_complex_id,
            'block_number'           => $request->block_number,
            'apartment_number'       => $request->apartment_number,
        ]);

        $token = $user->createToken($request->device)->plainTextToken;

        return response()->json([
            'auth_token' => $token,
            'user'       => new UserResource($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('residentialComplex');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function update(Request $request)
    {
        $input = $request->validate([
            'name'                   => 'nullable|string|max:255',
            'password'               => ['nullable', 'string', 'min:6', 'regex:/^[a-zA-Z0-9]{6,}$/'],
            'residential_complex_id' => 'nullable|integer|exists:residential_complexes,id',
            'block_number'           => 'nullable|string|max:255',
            'apartment_number'       => 'nullable|string|max:255',
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