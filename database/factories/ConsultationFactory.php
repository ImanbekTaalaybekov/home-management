<?php

namespace Database\Factories;

use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \App\Models\Consultation::class;

    /**
     * @param array $attributes
     * @return Consultation
     */
    public function newModel(array $attributes = [])
    {
        return new Consultation($attributes);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
           'fullname' => fake()->name(),
           'disability_group' => Arr::random(DisabilityGroupEnum::cases())->value,
           'disability_type' => Arr::random(DisabilityTypeEnum::cases())->value,
           'question' => fake()->paragraph
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Consultation $consultation) {
            $consultation->copyMedia(storage_path('documents/renaissance.mp3'))->toMediaCollection('consultation');
        });
    }
}
