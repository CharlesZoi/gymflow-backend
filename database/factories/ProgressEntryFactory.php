<?php

namespace Database\Factories;

use App\Models\ProgressEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressEntry>
 */
class ProgressEntryFactory extends Factory
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
            'entry_date' => fake()->dateTimeBetween('-14 days', 'today'),
            'workouts_completed' => fake()->numberBetween(0, 2),
            'active_minutes' => fake()->numberBetween(0, 90),
            'calories_burned' => fake()->numberBetween(0, 650),
            'weight_kg' => fake()->randomFloat(1, 52, 92),
            'completion_rate' => fake()->numberBetween(35, 100),
        ];
    }
}
