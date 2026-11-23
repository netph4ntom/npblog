<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.webp',
            'original_name' => fake()->word().'.jpg',
            'mime' => 'image/webp',
            'size' => fake()->numberBetween(10000, 500000),
            'width' => 1200,
            'height' => 800,
            'alt_text' => fake()->sentence(3),
        ];
    }
}
