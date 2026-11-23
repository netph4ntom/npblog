<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition(): array
    {
        return [
            'from_slug' => Str::slug(fake()->unique()->sentence(4)),
            'article_id' => Article::factory(),
        ];
    }
}
