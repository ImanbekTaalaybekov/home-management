<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \App\Models\Company::class;

    /**
     * @param array $attributes
     * @return Company
     */
    public function newModel(array $attributes = [])
    {
        return new Company($attributes);
    }

    public function definition(): array
    {
        return [
          'publish_key' => Str::random(10),
          'name' => fake()->name(),
          'user_id' => User::all()->random()->id,
          'city_id' => City::all()->random()->id,
          'phone' => fake()->phoneNumber(),
          'additional_contacts' => [
                [
                    "label" => fake()->name(),
                    "value" => fake()->name()
                ]
          ],
          'about' => fake()->paragraph,
          'admin_note' => fake()->paragraph,
          'published' =>  (bool)rand(0, 1),
          'responsible_person' => fake()->name(),
          'email' => fake()->unique()->safeEmail()
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Company $company) {
            $company->copyMedia(storage_path('images/image_test.jpg'))->toMediaCollection('company_logo');
        });
    }
}
