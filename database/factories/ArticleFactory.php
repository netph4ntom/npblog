<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);
        $body = fake()->paragraphs(4, true);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'featured_media_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'body' => $body,
            'body_html' => '<p>'.str_replace("\n\n", '</p><p>', e($body)).'</p>',
            'status' => 'published',
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'is_featured' => false,
            'language' => 'id',
            'word_count' => str_word_count($body),
            'reading_time' => max(1, (int) ceil(str_word_count($body) / 200)),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now()->addDays(5),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
