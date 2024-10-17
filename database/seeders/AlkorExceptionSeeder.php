<?php

namespace Database\Seeders;

use App\Models\AlkorException;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlkorExceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AlkorException::create([
            'name' => 'Плазма Стар',
        ]);

        AlkorException::create([
            'name' => 'Лекарь',
        ]);

        AlkorException::create([
            'name' => 'Мир и Нур',
        ]);

        AlkorException::create([
            'name' => 'Раат Фарм г. Ош',
        ]);

        AlkorException::create([
            'name' => 'Тенгис, а/с, г. Ош',
        ]);
    }
}
