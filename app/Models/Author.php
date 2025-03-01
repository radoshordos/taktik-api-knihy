<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'birthdate',
        'biography',
        'nationality',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    /**
     * @return BelongsToMany<Book, Author>
     */
    public function books(): BelongsToMany
    {
        /** @var BelongsToMany<Book, Author> $relation */
        $relation = $this->belongsToMany(Book::class);
        return $relation;
    }

    /**
     * @return MorphMany<Rating, Author>
     */
    public function ratings(): MorphMany
    {
        /** @var MorphMany<Rating, Author> $relation */
        $relation = $this->morphMany(Rating::class, 'rateable');
        return $relation;
    }

    /**
     * @return MorphMany<Comment, Author>
     */
    public function comments(): MorphMany
    {
        /** @var MorphMany<Comment, Author> $relation */
        $relation = $this->morphMany(Comment::class, 'commentable');
        return $relation;
    }
}
