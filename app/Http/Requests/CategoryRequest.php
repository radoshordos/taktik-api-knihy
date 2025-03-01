<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $categoryId = ($this->category instanceof Category) ? $this->category->id : '';

        return [
            'name' => 'required|string|max:100|' . Rule::unique('categories')->ignore($categoryId),
            'description' => 'nullable|string',
        ];
    }
}
