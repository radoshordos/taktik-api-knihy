<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'surname'     => $this->faker->word(),
            'birthdate'   => Carbon::now(),
            'biography'   => $this->faker->word(),
            'nationality' => $this->faker->word(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
