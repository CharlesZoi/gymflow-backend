<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Support\UserProfilePayload;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private const DEFAULT_NOTIFICATION_SETTINGS = [
        'newWorkoutPlanAlerts' => true,
        'workoutReminders' => true,
        'progressUpdates' => true,
        'promotions' => false,
    ];

    public function show(Request $request)
    {
        return response()->json([
            'profile' => $request->user()->load('profile')->profile
                ? UserProfileResource::make($request->user()->profile)
                : null,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate($this->profileValidationRules());

        $profile = $request->user()->profile()->updateOrCreate([], $data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => UserProfileResource::make($profile),
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
            'profile' => UserProfileResource::make($profile),
        ]);
    }

    public function notificationSettings(Request $request)
    {
        $profile = $request->user()->profile()->firstOrCreate();

        return response()->json([
            'notificationSettings' => $profile->notification_settings ?? self::DEFAULT_NOTIFICATION_SETTINGS,
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $data = $request->validate([
            'newWorkoutPlanAlerts' => ['required', 'boolean'],
            'workoutReminders' => ['required', 'boolean'],
            'progressUpdates' => ['required', 'boolean'],
            'promotions' => ['required', 'boolean'],
        ]);

        $profile = $request->user()->profile()->updateOrCreate([], [
            'notification_settings' => $data,
        ]);

        return response()->json([
            'message' => 'Notification settings updated.',
            'notificationSettings' => $profile->notification_settings,
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $data = $request->validate([
            'avatar_url' => ['required', 'url', 'max:2048'],
        ]);

        $profile = $request->user()->profile()->updateOrCreate([], [
            'avatar_url' => $data['avatar_url'],
        ]);

        return response()->json([
            'message' => 'Avatar updated.',
            'profile' => UserProfileResource::make($profile),
        ]);
    }

    private function profileValidationRules(): array
    {
        return UserProfilePayload::rules();
    }
}
