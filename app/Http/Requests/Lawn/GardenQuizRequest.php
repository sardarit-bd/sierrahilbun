<?php

namespace App\Http\Requests\Lawn;

use Illuminate\Foundation\Http\FormRequest;

class GardenQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'garden_types'   => ['required', 'array', 'min:1'],
            'garden_types.*' => ['required', 'string', 'in:flowers,vegetables,trees_shrubs'],
            'garden_size'    => ['required', 'string', 'in:xs,sm,l'],
        ];
    }

    public function messages(): array
    {
        return [
            'garden_types.required'   => 'Please select at least one garden type.',
            'garden_types.min'        => 'Please select at least one garden type.',
            'garden_types.*.in'       => 'Invalid garden type selected.',
            'garden_size.required'    => 'Please select a garden size.',
            'garden_size.in'          => 'Invalid garden size selected.',
        ];
    }
}