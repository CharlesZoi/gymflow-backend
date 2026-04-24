<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'profile' => $this->mapProfile($request->user()->load('profile')->profile),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate($this->profileValidationRules());

        $profile = $request->user()->profile()->updateOrCreate([], $data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $this->mapProfile($profile),
        ]);
    }

    public function completeOnboarding(Request $request)
    {
        $rules = $this->profileValidationRules();
        unset($rules['onboarding_completed']);

        $data = $request->validate($rules);
        $data['onboarding_completed'] = true;

        $profile = $request->user()->profile()->updateOrCreate([], $data);

        return response()->json([
            'message' => 'Onboarding completed.',
            'profile' => $this->mapProfile($profile),
        ]);
    }

    private function profileValidationRules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:80'],
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'age' => ['nullable', 'integer', 'min:13', 'max:100'],
            'gender' => ['nullable', 'string', 'max:50'],
            'body_type' => ['nullable', 'string', 'max:50'],
            'fitness_level' => ['nullable', 'string', 'max:50'],
            'training_days' => ['nullable', 'string', 'max:50'],
            'training_preference' => ['nullable', 'string', 'max:50'],
            'main_fitness_goal' => ['nullable', 'string', 'max:80'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
            'height_cm' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'primary_goal' => ['nullable', 'string', 'max:100'],
            'focus_area' => ['nullable', 'string', 'max:100'],
            'result_speed' => ['nullable', 'string', 'max:100'],
            'intensity_preference' => ['nullable', 'string', 'max:100'],
            'session_preference_minutes' => ['nullable', 'integer', 'min:10', 'max:180'],
            'motivation' => ['nullable', 'string', 'max:500'],
            'blockers' => ['nullable', 'string', 'max:500'],
            'theme' => ['nullable', 'string', 'max:20'],
            'onboarding_completed' => ['nullable', 'boolean'],
        ];
    }

    private function mapProfile($profile): array
    {
        return [
            'nickname' => $profile?->nickname,
            'firstName' => $profile?->first_name,
            'lastName' => $profile?->last_name,
            'age' => $profile?->age,
            'gender' => $profile?->gender,
            'bodyType' => $profile?->body_type,
            'fitnessLevel' => $profile?->fitness_level,
            'trainingDays' => $profile?->training_days,
            'trainingPreference' => $profile?->training_preference,
            'mainFitnessGoal' => $profile?->main_fitness_goal,
            'weightKg' => $profile?->weight_kg ? (float) $profile->weight_kg : null,
            'heightCm' => $profile?->height_cm ? (float) $profile->height_cm : null,
            'primaryGoal' => $profile?->primary_goal,
            'focusArea' => $profile?->focus_area,
            'resultSpeed' => $profile?->result_speed,
            'intensityPreference' => $profile?->intensity_preference,
            'sessionPreferenceMinutes' => $profile?->session_preference_minutes,
            'motivation' => $profile?->motivation,
            'blockers' => $profile?->blockers,
            'theme' => $profile?->theme,
            'onboardingCompleted' => (bool) $profile?->onboarding_completed,
        ];
    }
}
