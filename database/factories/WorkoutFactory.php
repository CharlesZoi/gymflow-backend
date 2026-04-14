<?php

namespace Database\Factories;

use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->randomElement([
            'Full Body Blast',
            'Morning Mobility Reset',
            'Chest and Triceps Builder',
            'Leg Day Power',
            'Core Ignite',
            'HIIT Fat Burn',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(10, 999),
            'description' => fake()->sentence(12),
            'difficulty' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'duration_minutes' => fake()->randomElement([20, 30, 45, 50, 60]),
            'exercises_count' => fake()->numberBetween(6, 14),
            'calories_burned' => fake()->numberBetween(180, 480),
            'category' => fake()->randomElement(['Strength', 'Mobility', 'Fat Loss', 'Conditioning']),
            'source_type' => fake()->randomElement(['system', 'community']),
            'coach_name' => fake()->randomElement(['FitCoach', 'Coach Mia', 'Coach Alex']),
            'image_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=900&q=80',
            'is_featured' => fake()->boolean(35),
            'tags' => fake()->randomElements(['Core', 'Mobility', 'Dumbbells', 'Quick Session', 'Gym Floor'], fake()->numberBetween(2, 4)),
        ];
    }
}
