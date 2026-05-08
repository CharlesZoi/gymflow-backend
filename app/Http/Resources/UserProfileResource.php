<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nickname' => $this->nickname,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'bodyType' => $this->body_type,
            'fitnessLevel' => $this->fitness_level,
            'trainingDays' => $this->training_days,
            'trainingPreference' => $this->training_preference,
            'mainFitnessGoal' => $this->main_fitness_goal,
            'weightKg' => $this->weight_kg !== null ? (float) $this->weight_kg : null,
            'heightCm' => $this->height_cm !== null ? (float) $this->height_cm : null,
            'primaryGoal' => $this->primary_goal,
            'focusArea' => $this->focus_area,
            'resultSpeed' => $this->result_speed,
            'intensityPreference' => $this->intensity_preference,
            'sessionPreferenceMinutes' => $this->session_preference_minutes,
            'motivation' => $this->motivation,
            'blockers' => $this->blockers,
            'theme' => $this->theme,
            'avatarUrl' => $this->avatar_url,
            'notificationSettings' => $this->notification_settings,
            'membershipPlan' => $this->membership_plan,
            'membershipRenewsOn' => $this->membership_renews_on?->toDateString(),
            'onboardingCompleted' => (bool) $this->onboarding_completed,
        ];
    }
}
