<?php

namespace App\DTO;
use App\Enums\CurrencyEnum;
use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\ExperienceLevelEnum;
use App\Enums\ResumeStatusEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;

class VacancyData extends Data
{
    public function __construct(
        #[Required, StringType, Min(2), Max(80)]
        public string $title,
        #[Required, StringType, Min(6), Max(10000)]
        public string $description,
        #[Required, StringType, Min(6), Max(10000)]
        public string $requirements,
        #[Required, StringType, Min(6), Max(10000)]
        public string $responsibilities,
        #[Required]
        public int $company_id,
        #[Required, Enum(ResumeStatusEnum::class)]
        public string $status,
        #[Required]
        public array $employment_types,
        #[Required]
        public array $include_disability_types,
        #[Enum(ExperienceLevelEnum::class)]
        public string $experience_level,
        #[Required, ArrayType]
        public array $skills,
        #[Required, StringType, Min(2), Max(80)]
        public string $address,
        #[Required, StringType, Min(6), Max(10000)]
        public string $working_conditions,
        #[Required, StringType, Min(4), Max(10000)]
        public string $activity,
        #[Nullable, ArrayType]
        public ?array $images = null,
        #[Nullable, Numeric]
        public ?float $salary_from_amount = null,
        #[Nullable, Numeric]
        public ?float $salary_to_amount = null,
        #[RequiredWith(['salary_from_amount', 'salary_to_amount']), Enum(CurrencyEnum::class)]
        public ?string $salary_currency = null,
        #[Nullable, StringType, Min(2), Max(10000)]
        public ?string $additional_info = null,
        #[Required]
        public ?int $city_id = null,
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
