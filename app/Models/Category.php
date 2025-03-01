<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @return BelongsToMany<Book, Category>
     */
    public function books(): BelongsToMany
    {
        /** @var BelongsToMany<Book, Category> $relation */
        $relation = $this->belongsToMany(Book::class);
        return $relation;
    }
}
