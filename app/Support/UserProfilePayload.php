<?php

namespace App\Support;

use App\Models\UserProfile;

class UserProfilePayload
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $prefix = ''): array
    {
        return [
            $prefix.'nickname' => ['nullable', 'string', 'max:80'],
            $prefix.'first_name' => ['nullable', 'string', 'max:80'],
            $prefix.'last_name' => ['nullable', 'string', 'max:80'],
            $prefix.'age' => ['nullable', 'integer', 'min:13', 'max:100'],
            $prefix.'gender' => ['nullable', 'string', 'max:50'],
            $prefix.'body_type' => ['nullable', 'string', 'max:50'],
            $prefix.'fitness_level' => ['nullable', 'string', 'max:50'],
            $prefix.'training_days' => ['nullable', 'string', 'max:50'],
            $prefix.'training_preference' => ['nullable', 'string', 'max:50'],
            $prefix.'main_fitness_goal' => ['nullable', 'string', 'max:80'],
            $prefix.'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            $prefix.'height_cm' => ['nullable', 'numeric', 'min:100', 'max:250'],
            $prefix.'primary_goal' => ['nullable', 'string', 'max:100'],
            $prefix.'focus_area' => ['nullable', 'string', 'max:100'],
            $prefix.'result_speed' => ['nullable', 'string', 'max:100'],
            $prefix.'intensity_preference' => ['nullable', 'string', 'max:100'],
            $prefix.'session_preference_minutes' => ['nullable', 'integer', 'min:10', 'max:180'],
            $prefix.'motivation' => ['nullable', 'string', 'max:500'],
            $prefix.'blockers' => ['nullable', 'string', 'max:500'],
            $prefix.'theme' => ['nullable', 'string', 'max:20'],
            $prefix.'onboarding_completed' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function filledAttributes(array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    public static function completedGoalCheckCount(?UserProfile $profile): int
    {
        if (! $profile) {
            return 0;
        }

        return collect([
            $profile->focus_area,
            $profile->session_preference_minutes,
            $profile->intensity_preference,
            $profile->result_speed,
            $profile->motivation,
            $profile->blockers,
        ])->filter(fn ($value) => $value !== null && $value !== '')->count();
    }
}
