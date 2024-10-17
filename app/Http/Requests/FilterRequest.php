<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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
            'filter.city_id' => 'nullable|integer|exists:cities,id',
            'search' => 'nullable|string',
            'filter.favorite' => 'nullable|boolean',
        ];
    }

    public function filteredData(): array
    {
        $filter = $this->input('filter', []);

        return array_intersect_key($filter, array_flip([
            'page',
            'city_id',
            'limit',
            'search',
            'favorite',
        ]));
    }
}
