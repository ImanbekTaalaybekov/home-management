<?php

namespace App\Http\Resources\Features;

use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Date\Date;

/**
 * @mixin Vacancy
 */
class VacancyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'responsibilities' => $this->responsibilities,
            'company' => new CompanyResource($this->company),
            'city' => new CityResource($this->city),
            'salary_from_amount_formatted' => $this->salary_from_amount_money?->format(),
            'salary_to_amount_formatted' => $this->salary_to_amount_money?->format(),
            'salary_from_amount' => $this->salary_from_amount,
            'salary_to_amount' => $this->salary_to_amount,
            'salary_currency' => $this->salary_currency,
            'employment_types' => $this->employment_types?->map(fn(EmploymentTypeEnum $v) => $v?->value),
            'include_disability_types' => $this->include_disability_types?->map(fn(DisabilityTypeEnum $v) => $v?->value),
            'experience_level' => $this->experience_level->value,
            'skills' => $this->skills,
            'has_favorite' => auth('sanctum')->check() && $this->favorites()->where('user_id', auth('sanctum')->id())->exists(),
            'images' => $this->getMedia('vacancy')->map(function ($media) {
                return [
                    'small' => $media->getUrl('small'),
                    'preview' => $media->getUrl('preview'),
                ];
            }),
            'address' => $this->address,
            'working_conditions' => $this->working_conditions,
            'activity' => $this->activity,
            'created_at' => Date::parse($this->created_at)->translatedFormat('j F Y'),
            'updated_at' => Date::parse($this->updated_at)->translatedFormat('j F Y'),
        ];
    }
}
