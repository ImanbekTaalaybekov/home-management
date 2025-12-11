<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ResidentialComplex;
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
        ResidentialComplex::create([
        'name' => 'Test Complex',
        'address' => 'some address',
        'client_id' => '1',
        ]);
        User::create([
            'name' => 'Test User',
            'personal_account' => '12345',
            'phone_number' => '123456789',
            'password' => Hash::make('pass'),
            'residential_complex_id' => 1,
            'block_number' => 'B1',
            'apartment_number' => '101'
        ]);

        Admin::create([
            'name' => 'Test Admin',
            'username' => 'admin',
            'password' => '1234',
            'role' => 'admin',
        ]);

        $this->call([
            UserSeeder::class
        ]);
    }
}
