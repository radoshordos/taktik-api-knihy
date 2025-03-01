<?php

namespace App\Http\Resources;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'isbn'           => $this->isbn,
            'published_at'   => $this->published_at,
            'description'    => $this->description,
            'page_count'     => $this->page_count,
            'language'       => $this->language,
            'authors'        => AuthorResource::collection($this->whenLoaded('authors')),
            'categories'     => CategoryResource::collection($this->whenLoaded('categories')),
            'ratings'        => RatingResource::collection($this->whenLoaded('ratings')),
            'comments'       => CommentResource::collection($this->whenLoaded('comments')),
            'average_rating' => $this->whenLoaded('ratings', function () {
                return $this->ratings->avg('score');
            }),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
