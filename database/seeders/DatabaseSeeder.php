<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'personal_account' => '12345',
            'phone_number' => '123456789',
            'password' => Hash::make('pass'),
            'residential_complex_id' => 1,
            'block_number' => 'B1',
            'apartment_number' => '101'
        ]);

        $this->call([
            UserSeeder::class
        ]);
    }
}
