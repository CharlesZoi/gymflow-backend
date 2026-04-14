<?php

namespace Database\Seeders;

use App\Models\ProgressEntry;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate([
            'email' => 'demo@gymflow.app',
        ], [
            'name' => 'Demo Member',
            'password' => Hash::make('password123'),
        ]);

        UserProfile::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'onboarding_completed' => true,
            'age' => 29,
            'gender' => 'female',
            'weight_kg' => 64.5,
            'height_cm' => 168.0,
            'primary_goal' => 'Build strength',
            'focus_area' => 'Full body',
            'result_speed' => 'Balanced',
            'intensity_preference' => 'Moderate',
            'session_preference_minutes' => 45,
            'motivation' => 'Stay consistent and feel stronger every week.',
            'blockers' => 'Busy workdays and inconsistent routine.',
            'theme' => 'dark',
        ]);

        $workouts = collect([
            [
                'title' => 'Full Body Blast',
                'slug' => 'full-body-blast',
                'description' => 'A balanced full body session that covers strength, cardio, and core in one efficient workout.',
                'difficulty' => 'Beginner',
                'duration_minutes' => 45,
                'exercises_count' => 10,
                'calories_burned' => 350,
                'category' => 'Strength',
                'source_type' => 'system',
                'coach_name' => 'FitCoach',
                'image_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=900&q=80',
                'is_featured' => true,
                'tags' => ['Gym Floor', 'Dumbbells', 'Starter'],
            ],
            [
                'title' => 'Morning Mobility Reset',
                'slug' => 'morning-mobility-reset',
                'description' => 'Low-impact mobility work for better recovery, posture, and movement quality.',
                'difficulty' => 'Beginner',
                'duration_minutes' => 20,
                'exercises_count' => 8,
                'calories_burned' => 120,
                'category' => 'Mobility',
                'source_type' => 'system',
                'coach_name' => 'Coach Mia',
                'image_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=900&q=80',
                'is_featured' => true,
                'tags' => ['Stretch', 'Recovery', 'Quick Session'],
            ],
            [
                'title' => 'Chest & Triceps Builder',
                'slug' => 'chest-triceps-builder',
                'description' => 'Push-focused upper body training designed for progressive overload and clean form.',
                'difficulty' => 'Intermediate',
                'duration_minutes' => 50,
                'exercises_count' => 9,
                'calories_burned' => 410,
                'category' => 'Strength',
                'source_type' => 'system',
                'coach_name' => 'Coach Alex',
                'image_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=900&q=80',
                'is_featured' => true,
                'tags' => ['Upper Body', 'Bench', 'Gym Floor'],
            ],
            [
                'title' => 'Community HIIT Burn',
                'slug' => 'community-hiit-burn',
                'description' => 'Fast-paced interval work from the community library when you want a sweaty challenge.',
                'difficulty' => 'Advanced',
                'duration_minutes' => 30,
                'exercises_count' => 12,
                'calories_burned' => 380,
                'category' => 'Conditioning',
                'source_type' => 'community',
                'coach_name' => 'Member Coach',
                'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=900&q=80',
                'is_featured' => false,
                'tags' => ['HIIT', 'Cardio', 'Fast Pace'],
            ],
        ])->map(fn (array $workout) => Workout::updateOrCreate([
            'slug' => $workout['slug'],
        ], $workout));

        $todayWorkout = $workouts->firstWhere('slug', 'chest-triceps-builder');
        $featuredWorkout = $workouts->firstWhere('slug', 'full-body-blast');

        WorkoutSchedule::query()->delete();

        WorkoutSchedule::create([
            'user_id' => $user->id,
            'workout_id' => $todayWorkout->id,
            'scheduled_for' => Carbon::today()->setTime(11, 0),
            'location' => 'Main Floor',
            'trainer_name' => 'Coach Alex',
            'status' => 'scheduled',
            'notes' => 'Focus on controlled tempo and full range of motion.',
        ]);

        WorkoutSchedule::create([
            'user_id' => $user->id,
            'workout_id' => $featuredWorkout->id,
            'scheduled_for' => Carbon::tomorrow()->setTime(18, 30),
            'location' => 'Studio A',
            'trainer_name' => 'FitCoach',
            'status' => 'scheduled',
            'notes' => 'Bring a towel and water bottle.',
        ]);

        WorkoutSchedule::create([
            'user_id' => $user->id,
            'workout_id' => $workouts->firstWhere('slug', 'morning-mobility-reset')->id,
            'scheduled_for' => Carbon::yesterday()->setTime(7, 30),
            'location' => 'Recovery Zone',
            'trainer_name' => 'Coach Mia',
            'status' => 'completed',
            'notes' => 'Recovery session completed before work.',
        ]);

        ProgressEntry::query()->delete();

        collect(range(0, 6))->each(function (int $offset) use ($user): void {
            $date = Carbon::today()->subDays(6 - $offset);

            ProgressEntry::create([
                'user_id' => $user->id,
                'entry_date' => $date,
                'workouts_completed' => $offset % 2 === 0 ? 1 : 0,
                'active_minutes' => $offset % 2 === 0 ? 45 + ($offset * 3) : 20 + ($offset * 2),
                'calories_burned' => $offset % 2 === 0 ? 320 + ($offset * 15) : 120 + ($offset * 10),
                'weight_kg' => 64.5 - ($offset * 0.1),
                'completion_rate' => 68 + ($offset * 4),
            ]);
        });
    }
}
