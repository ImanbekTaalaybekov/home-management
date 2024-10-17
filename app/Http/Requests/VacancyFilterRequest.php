<?php

namespace App\Http\Requests;

use App\Enums\CurrencyEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\ExperienceLevelEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VacancyFilterRequest extends FilterRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter.disability_type' => 'nullable|string',
            'filter.salary_from_amount' => 'nullable|numeric',
            'filter.salary_to_amount' => 'nullable|numeric',
            'filter.salary_currency' => ['required_with:filter.salary_from_amount, filter.salary_to_amount', Rule::in(CurrencyEnum::cases())],
            'filter.activity' => 'nullable|string',
            'filter.employment_types' => ['nullable', Rule::in(EmploymentTypeEnum::cases())],
            'filter.experience_level' => ['nullable', Rule::in(ExperienceLevelEnum::cases())],
            'filter.created_at' => 'nullable|string',
            ... parent::rules()
        ];
    }

    public function filteredData(): array
    {
        $filter = $this->input('filter', []);

        return [
         ...$filter,
         ...parent::filteredData()
       ];
    }
}
