<?php

namespace App\Http\Resources;

use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rating
 */
class RatingResource extends JsonResource
{
    /**
     * @return array<string, Carbon|int|null>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'score'      => $this->score,
            'user_id'    => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
