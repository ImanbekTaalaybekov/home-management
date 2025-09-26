<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserStaticSeeder extends Seeder
{
    public function run(): void
    {
        $rawUsers = include database_path('seeders/users_seed_array.php');

        foreach ($rawUsers as $data) {
            $user = [
                'personal_account'         => $this->clean($data['personal_account'] ?? null),
                'login'                    => $this->clean($data['login'] ?? null),
                'phone_number'             => $this->clean($data['phone_number'] ?? null),
                'name'                     => $this->clean($data['name'] ?? null),
                'password'                 => $this->clean($data['password'] ?? null),
                'block_number'             => $this->clean($data['block_number'] ?? null),
                'apartment_number'         => $this->clean($data['apartment_number'] ?? null),
                'non_residential_premises' => $this->clean($data['non_residential_premises'] ?? null),
                'residential_complex_id'   => $this->clean($data['residential_complex_id'] ?? null),
                'fcm_token'                => $this->clean($data['fcm_token'] ?? null),
                'role'                     => $this->clean($data['role'] ?? null),
            ];

            if (!empty($user['password']) && !str_starts_with((string)$user['password'], '$2y$')) {
                $user['password'] = Hash::make((string)$user['password']);
            }

            User::updateOrCreate(
                ['personal_account' => $user['personal_account']],
                $user
            );
        }
    }

    private function clean($value)
    {
        if ($value === null) return null;
        if ($value === '') return null;
        if (is_string($value) && strtolower($value) === 'nan') return null;
        if (is_float($value) && is_nan($value)) return null;

        return $value;
    }
}
