<?php

namespace App\Http\Requests;

use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultationRequest extends FormRequest
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
            'fullname' => 'required|string|min:2|max:255',
            'disability_group' => ['required', Rule::enum(DisabilityGroupEnum::class)],
            'disability_type' => ['required', Rule::enum(DisabilityTypeEnum::class)],
            'question' => 'required|string|min:5|max:10000',
            'files' => 'nullable|file|mimes:mp3,acc,mp4,mov,docx,xlsx,xls|max:15360',
        ];
    }
}
