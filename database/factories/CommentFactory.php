<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'text'             => $this->faker->text(),
            'user_id'          => $this->faker->randomNumber(),
            'commentable_id'   => $this->faker->randomNumber(),
            'commentable_type' => $this->faker->word(),
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),
        ];
    }
}
