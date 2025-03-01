<?php

namespace App\Http\Resources;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Author
 */
class AuthorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'surname'        => $this->surname,
            'full_name'      => $this->name . ' ' . $this->surname,
            'birthdate'      => $this->birthdate,
            'biography'      => $this->biography,
            'nationality'    => $this->nationality,
            'books'          => BookResource::collection($this->whenLoaded('books')),
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
