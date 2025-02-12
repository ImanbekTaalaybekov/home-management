<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ValidPasswordResetToken implements ValidationRule
{
    protected string $token;

    public function __construct($token)
    {
        if ($token === null) {
            throw new \InvalidArgumentException("Token can not be null.");
        }
        $this->token = $token;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($value === null || $this->token === null) {
            $fail("Invalid token");
            return;
        }

        $passwordResetTokens = DB::table('password_reset_tokens')
            ->where('email', '=', $value)
            ->orderByDesc('created_at')
            ->first();

        if ($passwordResetTokens === null) {
            $fail("Invalid token validation");
            return;
        }

        if ($passwordResetTokens->token === null) {
            $fail("Token not found");
            return;
        }

        if (!Hash::check($this->token, $passwordResetTokens->token)) {
            $fail("The token is invalid.");
        }
    }

    public function message()
    {
        return 'The token is invalid.';
    }

}
