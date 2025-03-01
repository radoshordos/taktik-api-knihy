<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
  * @uses HasFactory<\Database\Factories\RatingFactory>
 */
class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'score',
        'user_id',
    ];

    /**
     * @return MorphTo<Model, static>
     */
    public function rateable(): MorphTo
    {
        /** @var MorphTo<Model, static> $morph */
        $morph = $this->morphTo();
        return $morph;
    }

    public function setScoreAttribute(int $value): void
    {
        $this->attributes['score'] = max(1, min(5, $value));
    }
}
