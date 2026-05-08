<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class GymFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_member_can_log_in_and_receive_a_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'profile',
                ],
            ]);
    }

    public function test_authenticated_member_can_fetch_completed_frame_resources(): void
    {
        $this->seed();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ])->json();

        $headers = [
            'Authorization' => 'Bearer '.$loginResponse['token'],
        ];

        $this->withHeaders($headers)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('header.appName', 'GymFlow');

        $this->withHeaders($headers)
            ->getJson('/api/v1/workouts')
            ->assertOk()
            ->assertJsonStructure([
                'filters',
                'workouts',
            ]);
    }

    public function test_auth_endpoints_return_camel_case_profile_payloads(): void
    {
        $this->seed();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.profile.firstName', null)
            ->assertJsonPath('user.profile.weightKg', 64.5)
            ->assertJsonMissingPath('user.profile.first_name')
            ->assertJsonMissingPath('user.profile.weight_kg');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$loginResponse->json('token'),
        ])
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.profile.focusArea', 'Full body')
            ->assertJsonMissingPath('user.profile.focus_area');
    }

    public function test_register_accepts_full_completed_frontend_profile_payload(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Frame Complete',
            'email' => 'frame.complete@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
            'profile' => [
                'nickname' => 'Frame',
                'first_name' => 'Frame',
                'last_name' => 'Complete',
                'age' => 31,
                'gender' => 'female',
                'body_type' => 'Regular',
                'fitness_level' => 'Intermediate',
                'training_days' => '4-5 days',
                'training_preference' => 'Strength training',
                'main_fitness_goal' => 'Build Muscle',
                'weight_kg' => 62.5,
                'height_cm' => 168,
                'primary_goal' => 'Build strength',
                'focus_area' => 'Upper Body',
                'result_speed' => 'Balanced',
                'intensity_preference' => 'Moderate',
                'session_preference_minutes' => 45,
                'motivation' => 'Strength improvement',
                'blockers' => 'Not enough time',
                'theme' => 'dark',
                'onboarding_completed' => true,
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.profile.firstName', 'Frame')
            ->assertJsonPath('user.profile.weightKg', 62.5)
            ->assertJsonPath('user.profile.focusArea', 'Upper Body')
            ->assertJsonPath('user.profile.onboardingCompleted', true)
            ->assertJsonMissingPath('user.profile.first_name');
    }

    public function test_dashboard_setup_progress_is_computed_from_goal_check_fields(): void
    {
        $user = User::factory()->create();
        UserProfile::factory()->for($user)->create([
            'onboarding_completed' => false,
            'focus_area' => 'Full Body',
            'session_preference_minutes' => 45,
            'intensity_preference' => null,
            'result_speed' => null,
            'motivation' => null,
            'blockers' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('setup.completed', false)
            ->assertJsonPath('setup.questionsCompleted', 2)
            ->assertJsonPath('setup.questionsTotal', 6);
    }

    public function test_authenticated_member_can_persist_profile_setup_and_goal_check_answers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'age' => 29,
                'gender' => 'Female',
                'weight_kg' => 72.5,
                'height_cm' => 178,
                'focus_area' => 'Upper Body',
                'session_preference_minutes' => 45,
                'intensity_preference' => 'High Intensity',
                'result_speed' => 'Fast Progress',
                'motivation' => 'Strength improvement',
                'blockers' => 'Not enough time',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('profile.age', 29)
            ->assertJsonPath('profile.gender', 'Female')
            ->assertJsonPath('profile.weightKg', 72.5)
            ->assertJsonPath('profile.heightCm', 178)
            ->assertJsonPath('profile.focusArea', 'Upper Body')
            ->assertJsonPath('profile.sessionPreferenceMinutes', 45)
            ->assertJsonPath('profile.intensityPreference', 'High Intensity')
            ->assertJsonPath('profile.resultSpeed', 'Fast Progress')
            ->assertJsonPath('profile.motivation', 'Strength improvement')
            ->assertJsonPath('profile.blockers', 'Not enough time');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('setup.questionsCompleted', 6);
    }

    public function test_workout_filters_reject_unknown_values(): void
    {
        $this->seed();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'demo@gymflow.app',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ])->json();

        $headers = [
            'Authorization' => 'Bearer '.$loginResponse['token'],
        ];

        $this->withHeaders($headers)
            ->getJson('/api/v1/workouts?source=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source');

        $this->withHeaders($headers)
            ->getJson('/api/v1/workouts?difficulty=Expert')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('difficulty');
    }

    public function test_authenticated_member_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'old-password',
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password updated.');

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));
    }

    public function test_authenticated_member_can_delete_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/account', [
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Account deleted.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_authenticated_member_can_manage_notification_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile/notification-settings', [
                'newWorkoutPlanAlerts' => false,
                'workoutReminders' => true,
                'progressUpdates' => true,
                'promotions' => false,
            ])
            ->assertOk()
            ->assertJsonPath('notificationSettings.newWorkoutPlanAlerts', false)
            ->assertJsonPath('notificationSettings.workoutReminders', true)
            ->assertJsonPath('notificationSettings.progressUpdates', true)
            ->assertJsonPath('notificationSettings.promotions', false);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile/notification-settings')
            ->assertOk()
            ->assertJsonPath('notificationSettings.promotions', false);
    }

    public function test_authenticated_member_can_list_and_revoke_linked_devices(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current-device');
        $oldToken = $user->createToken('old-phone');

        $this->withHeader('Authorization', 'Bearer '.$currentToken->plainTextToken)
            ->getJson('/api/v1/devices')
            ->assertOk()
            ->assertJsonCount(2, 'devices')
            ->assertJsonPath('devices.0.name', 'current-device');

        $this->withHeader('Authorization', 'Bearer '.$currentToken->plainTextToken)
            ->deleteJson('/api/v1/devices/'.$oldToken->accessToken->id)
            ->assertOk()
            ->assertJsonPath('message', 'Device revoked.');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldToken->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $currentToken->accessToken->id]);
    }

    public function test_authenticated_member_can_create_workout_and_schedule_it(): void
    {
        $user = User::factory()->create();

        $workoutResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workouts', [
                'title' => 'Morning Strength',
                'description' => 'Upper body strength session',
                'difficulty' => 'Beginner',
                'duration_minutes' => 35,
                'exercises_count' => 6,
                'calories_burned' => 250,
                'category' => 'Strength',
                'tags' => ['upper body', 'home'],
            ])
            ->assertCreated()
            ->assertJsonPath('workout.title', 'Morning Strength')
            ->assertJsonPath('workout.sourceType', 'user');

        $workoutId = $workoutResponse->json('workout.id');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/schedule', [
                'workout_id' => $workoutId,
                'scheduled_for' => '2026-05-10T09:00:00+08:00',
                'location' => 'Home gym',
                'trainer_name' => 'Coach Jelie',
                'notes' => 'Bring resistance bands',
            ])
            ->assertCreated()
            ->assertJsonPath('schedule.title', 'Morning Strength')
            ->assertJsonPath('schedule.location', 'Home gym');
    }

    public function test_authenticated_member_can_update_cancel_and_complete_schedule(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();
        $schedule = WorkoutSchedule::factory()->for($user)->for($workout)->create([
            'status' => 'scheduled',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/schedule/'.$schedule->id, [
                'scheduled_for' => '2026-05-11T10:30:00+08:00',
                'status' => 'scheduled',
                'notes' => 'Updated notes',
            ])
            ->assertOk()
            ->assertJsonPath('schedule.notes', 'Updated notes');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/schedule/'.$schedule->id.'/complete')
            ->assertOk()
            ->assertJsonPath('schedule.status', 'completed');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/schedule/'.$schedule->id.'/cancel')
            ->assertOk()
            ->assertJsonPath('schedule.status', 'cancelled');
    }

    public function test_authenticated_member_can_upsert_progress_check_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/progress/check-ins', [
                'entry_date' => '2026-05-08',
                'workouts_completed' => 1,
                'active_minutes' => 45,
                'calories_burned' => 320,
                'weight_kg' => 63.5,
                'completion_rate' => 100,
            ])
            ->assertCreated()
            ->assertJsonPath('entry.entryDate', '2026-05-08')
            ->assertJsonPath('entry.weightKg', 63.5);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/progress/check-ins', [
                'entry_date' => '2026-05-08',
                'workouts_completed' => 2,
                'active_minutes' => 60,
                'calories_burned' => 400,
                'completion_rate' => 95,
            ])
            ->assertOk()
            ->assertJsonPath('entry.workoutsCompleted', 2)
            ->assertJsonPath('entry.completionRate', 95);
    }

    public function test_member_can_complete_password_reset_with_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@gymflow.app',
            'password' => Hash::make('old-password'),
        ]);
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@gymflow.app',
            'token' => $token,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Password reset successfully.');

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));
    }

    public function test_authenticated_member_can_update_avatar_and_fetch_membership(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile/avatar', [
                'avatar_url' => 'https://cdn.gymflow.app/avatars/jelie.png',
            ])
            ->assertOk()
            ->assertJsonPath('profile.avatarUrl', 'https://cdn.gymflow.app/avatars/jelie.png');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/membership')
            ->assertOk()
            ->assertJsonPath('membership.plan', 'Free Plan')
            ->assertJsonPath('membership.status', 'active');
    }

    public function test_authenticated_member_can_fetch_workout_detail(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create([
            'title' => 'Detailed Strength',
            'tags' => ['strength', 'upper body'],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/workouts/'.$workout->id)
            ->assertOk()
            ->assertJsonPath('workout.title', 'Detailed Strength')
            ->assertJsonPath('workout.tags.0', 'strength');
    }
}
