<?php

namespace Database\Factories;

use App\Models\WorkoutSchedule;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSchedule>
 */
class WorkoutScheduleFactory extends Factory
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
            'workout_id' => Workout::factory(),
            'scheduled_for' => fake()->dateTimeBetween('-2 days', '+5 days'),
            'location' => fake()->randomElement(['Main Floor', 'Studio A', 'Functional Zone']),
            'trainer_name' => fake()->randomElement(['Coach Mia', 'Coach Alex', 'Self-guided']),
            'status' => fake()->randomElement(['scheduled', 'completed', 'missed']),
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }
}
