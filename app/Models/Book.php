<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'published_at',
        'description',
        'page_count',
        'language',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    /**
     * @return BelongsToMany<Author, Book>
     */
    public function authors(): BelongsToMany
    {
        /** @var BelongsToMany<Author, Book> $relation */
        $relation = $this->belongsToMany(Author::class);
        return $relation;
    }

    /**
     * @return BelongsToMany<Category, Book>
     */
    public function categories(): BelongsToMany
    {
        /** @var BelongsToMany<Category, Book> $relation */
        $relation = $this->belongsToMany(Category::class);
        return $relation;
    }

    /**
     * @return MorphMany<Rating, Book>
     */
    public function ratings(): MorphMany
    {
        /** @var MorphMany<Rating, Book> $relation */
        $relation = $this->morphMany(Rating::class, 'rateable');
        return $relation;
    }

    /**
     * @return MorphMany<Comment, Book>
     */
    public function comments(): MorphMany
    {
        /** @var MorphMany<Comment, Book> $relation */
        $relation = $this->morphMany(Comment::class, 'commentable');
        return $relation;
    }
}
