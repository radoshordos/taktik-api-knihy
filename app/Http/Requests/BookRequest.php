<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        $bookId = ($this->book instanceof Book) ? $this->book->id : '';

        return [
            'title'          => 'required|string|max:255',
            'isbn'           => [
                'nullable',
                'string',
                'max:20',
                'unique:books,isbn,' . $bookId,
            ],
            'published_at'   => 'nullable|date',
            'description'    => 'nullable|string',
            'page_count'     => 'nullable|integer|min:1',
            'language'       => 'nullable|string|max:10',
            'author_ids'     => 'nullable|array',
            'author_ids.*'   => 'exists:authors,id',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ];
    }
}
