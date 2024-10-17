<?php

namespace App\DTO;
use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;

class CompanyData extends Data
{
    public function __construct(
        #[Rule('required|string|unique|size:32')]
        public string $publish_key,
        #[Rule('required|string|min:2|max:255')]
        public string $name,
        #[Rule('required')]
        public int $city_id,
        #[Rule('required|string|min:2|max:50')]
        public string $phone,
        #[Rule('required|array')]
        public array $additional_contacts,
        #[Rule('string|min:2|max:10000')]
        public string $about,
        #[Rule('required|boolean')]
        public bool $published,
        #[Rule('required|string|min:2|max:255')]
        public string $responsible_person,
        #[Rule('required|string|min:2|max:255')]
        public string $email,
        #[Nullable, Rule('string|max:10000')]
        public ?string $admin_note = null,
        #[Nullable, DataCollectionOf(VacancyData::class)]
        public ?array $vacancies = null,
        #[Rule('nullable')]
        public ?int $user_id = null,
        #[Rule('nullable|string')]
        public ?string $image = null,
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
