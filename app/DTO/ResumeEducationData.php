<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;

class ResumeEducationData extends Data
{
    public function __construct(
        #[Rule('required|string|min:2|max:255')]
        public string $institution_name,
        #[Rule('required|string|min:2|max:255')]
        public string $specialty,
        #[Rule('required|date')]
        public string $end_year,
        #[Rule('required')]
        public int $resume_id,
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
