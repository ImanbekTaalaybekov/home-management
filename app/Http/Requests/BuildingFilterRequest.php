<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuildingFilterRequest extends FilterRequest
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
            'filter.category_id' => 'nullable|integer|exists:building_categories,id',
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
