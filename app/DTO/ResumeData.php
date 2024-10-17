<?php

namespace App\DTO;
use App\Enums\CurrencyEnum;
use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\ResumeStatusEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;

class ResumeData extends Data
{
    public function __construct(
        #[Rule('required|string|min:2|max:80')]
        public string $fullname,
        #[Rule('required|string|min:6|max:50')]
        public string $phone,
        #[Rule('required|array')]
        public array $additional_contacts,
        #[Rule('required')]
        public int $city_id,
        #[Required, Enum(EmploymentTypeEnum::class)]
        public array $employment_types,
        #[Required,DataCollectionOf(ResumeEducationData::class)]
        public array $educations,
        #[Required,DataCollectionOf(ResumeWorkExperienceData::class)]
        public array $experiences,
        #[Nullable, Enum(ResumeStatusEnum::class)]
        public ?string $status = null,
        #[Nullable, Enum(DisabilityGroupEnum::class)]
        public ?int $disability_group = null,
        #[Nullable, Enum(DisabilityTypeEnum::class)]
        public ?string $disability_type = null,
        #[Rule('nullable|date')]
        public ?string $date_of_birth = null,
        #[Rule('nullable|string')]
        public ?string $image = null,
        #[Rule('nullable|decimal:10,2')]
        public ?float $desired_salary_amount = null,
        #[Rule('required_with:desired_salary_amount|Rule::enum(CurrencyEnum::class)|')]
        public ?string $desired_salary_currency = null,
        #[Rule('nullable|string|min:2|max:10000')]
        public ?string $additional_info = null,
    ) {
    }

    public static function normalizers(): array
    {
        return [
            ModelNormalizer::class,
            ArrayNormalizer::class,
        ];
    }
}
