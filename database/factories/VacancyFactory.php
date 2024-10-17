<?php

namespace Database\Factories;

use App\Enums\CurrencyEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\ExperienceLevelEnum;
use App\Enums\ResumeStatusEnum;
use App\Models\City;
use App\Models\Company;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \App\Models\Vacancy::class;

    /**
     * @param array $attributes
     * @return Vacancy
     */
    public function newModel(array $attributes = [])
    {
        return new Vacancy($attributes);
    }

    public function definition(): array
    {
        $employments = EmploymentTypeEnum::cases();
        $disability_types = DisabilityTypeEnum::cases();
        return [
            'title' => fake()->name(),
            'description' => fake()->paragraph,
            'requirements' => fake()->paragraph,
            'responsibilities' => fake()->paragraph,
            'company_id' => Company::all()->random()->id,
            'status' => Arr::random(ResumeStatusEnum::cases())->value,
            'city_id' => City::all()->random()->id,
            'salary_currency' => Arr::random(CurrencyEnum::cases())->value,
            'salary_from_amount' => fake()->numberBetween(10000, 20000),
            'salary_to_amount' => fake()->numberBetween(30000, 100000),
            'employment_types' => collect($employments)->random(rand(1, count($employments)))->toArray(),
            'include_disability_types' => collect($disability_types)->random(rand(1, count($disability_types)))->toArray(),
            'experience_level' => Arr::random(ExperienceLevelEnum::cases())->value,
            'skills' => array_map(fn() => fake()->name(), array_fill(0, rand(1,10), null)),
            'address' => fake()->sentence(3),
            'working_conditions' => fake()->paragraph,
            'activity' => fake()->sentence(rand(1,10))
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Vacancy $vacancy) {
            $vacancy->copyMedia(storage_path('images/image_test.jpg'))->toMediaCollection('vacancy');
        });
    }
}
