<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id',
    ];

    /**
     * @return MorphTo<Model, Comment>
     */
    public function commentable(): MorphTo
    {
        /** @var MorphTo<Model, Comment> */
        return $this->morphTo();
    }
}
