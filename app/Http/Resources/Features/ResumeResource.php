<?php

namespace App\Http\Resources\Features;

use App\Enums\EmploymentTypeEnum;
use App\Http\Resources\UserResource;
use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Date\Date;

/**
 * @mixin Resume
 */
class ResumeResource extends JsonResource
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
            'fullname' => $this->fullname,
            'phone' => $this->phone,
            'additional_contacts' => $this->additional_contacts,
            'user' => new UserResource($this->user, hideEmail: true),
            'city' => new CityResource($this->city),
            'desired_salary' => $this->desired_salary_amount,
            'desired_salary_formatted'=> $this->desired_salary_money?->format(),
            'desired_salary_currency' => $this->desired_salary_currency,
            'employment_types' => $this->employment_types?->map(fn(EmploymentTypeEnum $v) => $v?->value),
            'date_of_birth' => $this->date_of_birth,
            'disability_group' => $this->disability_group->value,
            'disability_type' => $this->disability_type->value,
            'additional_info' => $this->additional_info,
            'status' => $this->status->value,
            'avatar' => $this->getMedia('resume_avatar')->map(function ($media) {
                return [
                    'small' => $media->getUrl('small'),
                    'preview' => $media->getUrl('preview'),
                ];
            })->first(),
            'has_favorite' => auth('sanctum')->check() && $this->favorites()->where('user_id', auth('sanctum')->id())->exists(),
            'created_at' => Date::parse($this->created_at)->translatedFormat('j F Y'),
            'updated_at' => Date::parse($this->updated_at)->translatedFormat('j F Y'),
        ];
    }
}
