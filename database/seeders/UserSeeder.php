<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['personal_account' => '101495933', 'name' => 'МОЛДАХМЕТОВ Ж'],
            ['personal_account' => '101495984', 'name' => 'ИСКАИРОВА ЛЯЗЗАТ'],
            ['personal_account' => '101496000', 'name' => 'АМАНБАЕВА САУЛЕ'],
            ['personal_account' => '101496018', 'name' => 'КАСЫМБАЕВА НУРГУЛЬ'],
            ['personal_account' => '101496026', 'name' => 'АЛИШЕВ ЕРСЕН'],
            ['personal_account' => '101496034', 'name' => 'НАБИЕВА АЙСУЛУ'],
            ['personal_account' => '101496042', 'name' => 'КОНЫРАТОВА АЙЖАН'],
            ['personal_account' => '101496085', 'name' => 'МАДЪЯРҰЛЫ АЛИХАН'],
            ['personal_account' => '101496093', 'name' => 'ПАЛЫМБЕТОВ БАҒЛАН'],
            ['personal_account' => '101496107', 'name' => 'КОЖАГЕЛЬДИЕВ ГАНИ'],
            ['personal_account' => '101496131', 'name' => 'ЖУМАБИКЕ С'],
            ['personal_account' => '101496140', 'name' => 'ЕСТЕМЕСОВ АЙДЫН'],
            ['personal_account' => '101496158', 'name' => 'КУАНГАЛИЕВ КАНАТ'],
            ['personal_account' => '101496174', 'name' => 'БЕРИМОВА ГУЛБАНУ'],
            ['personal_account' => '101496204', 'name' => 'МУРАТ АҚТОЛҚЫН'],
            ['personal_account' => '101496212', 'name' => 'КАСЫМБЕКОВ АСЕТ'],
            ['personal_account' => '101496220', 'name' => 'РАХИМОВ Б'],
            ['personal_account' => '101496239', 'name' => 'БАКБОЛАТУЛЫ НУРГАЛИ'],
            ['personal_account' => '101496247', 'name' => 'БОҚАСОВА АЯУЛЫ'],
        ];

        foreach ($users as $user) {
            User::create([
                'personal_account' => $user['personal_account'],
                'name' => $user['name'],
                'password' => Hash::make('1234'),
                'residential_complex_id' => 1,
                'block_number' => rand(1, 100),
                'apartment_number' => rand(1, 100),
                'phone_number' => 1234,
                'fcm_token' => null,
            ]);
        }
    }
}