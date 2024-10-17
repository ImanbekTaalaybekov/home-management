<?php

namespace App\Http\Requests;

use App\Enums\CurrencyEnum;
use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResumeFilterRequest extends FilterRequest
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
            'filter.desired_salary_amount' => 'nullable|numeric',
            'filter.desired_salary_currency' => ['required_with:filter.desired_salary_amount', Rule::in(CurrencyEnum::cases())],
            'filter.employment_types' => ['nullable', Rule::in(EmploymentTypeEnum::cases())],
            'filter.date_of_birth' => 'nullable|numeric',
            'filter.disability_group' => ['nullable', Rule::in(DisabilityGroupEnum::cases())],
            'filter.disability_type' => ['nullable', Rule::in(DisabilityTypeEnum::cases())],
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
