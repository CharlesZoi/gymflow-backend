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
        $data = $request->validate([
            'age' => ['nullable', 'integer', 'min:13', 'max:100'],
            'gender' => ['nullable', 'string', 'max:50'],
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
        ]);

        $profile = $request->user()->profile()->updateOrCreate([], $data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $this->mapProfile($profile),
        ]);
    }

    private function mapProfile($profile): array
    {
        return [
            'age' => $profile?->age,
            'gender' => $profile?->gender,
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
