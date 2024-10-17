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
            'email' => 'test@test.com',
            'password' => Hash::make('pass'),
        ]);

        $this->call([
            AlkorExceptionSeeder::class,
        ]);
    }


}
