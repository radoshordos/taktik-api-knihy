<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'surname'     => 'required|string|max:100',
            'birthdate'   => 'nullable|date',
            'biography'   => 'nullable|string',
            'nationality' => 'nullable|string|max:100',
        ];
    }
}
