<?php

namespace Database\Factories;

use App\Models\UserProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'onboarding_completed' => true,
            'age' => fake()->numberBetween(21, 42),
            'gender' => fake()->randomElement(['female', 'male', 'non-binary']),
            'weight_kg' => fake()->randomFloat(1, 52, 92),
            'height_cm' => fake()->randomFloat(1, 150, 190),
            'primary_goal' => fake()->randomElement(['Lose weight', 'Build strength', 'Improve endurance']),
            'focus_area' => fake()->randomElement(['Full body', 'Upper body', 'Core']),
            'result_speed' => fake()->randomElement(['Balanced', 'Fast paced']),
            'intensity_preference' => fake()->randomElement(['Moderate', 'High']),
            'session_preference_minutes' => fake()->randomElement([30, 45, 60]),
            'motivation' => fake()->sentence(),
            'blockers' => fake()->sentence(),
            'theme' => 'dark',
        ];
    }
}
