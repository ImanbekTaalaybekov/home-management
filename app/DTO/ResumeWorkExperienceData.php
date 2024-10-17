<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;

class ResumeWorkExperienceData extends Data
{
    public function __construct(
        #[Rule('required|string|min:2|max:255')]
        public string $company_name,
        #[Rule('required|string|min:2|max:80')]
        public string $role_name,
        #[Rule('required|date')]
        public string $start_date,
        #[Rule('required')]
        public int $resume_id,
        #[Rule('nullable|date')]
        public ?string $end_date = null,
        #[Rule('nullable|string|min:2|max:80')]
        public ?string $location = null,
        #[Rule('nullable|min:2|max:10000')]
        public ?string $additional_info = null
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
