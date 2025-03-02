<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title'        => $this->faker->word(),
            'isbn'         => $this->faker->unique()->isbn13(),
            'published_at' => $this->faker->date(),
            'description'  => $this->faker->text(),
            'page_count'   => $this->faker->randomNumber(),
            'language'     => $this->faker->word(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ];
    }
}
