<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressEntry;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        $todaySession = WorkoutSchedule::with('workout')
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_for', [$todayStart, $todayEnd])
            ->orderBy('scheduled_for')
            ->first();

        $featuredPrograms = Workout::query()
            ->where('is_featured', true)
            ->orderBy('title')
            ->take(3)
            ->get();

        $weekEntries = ProgressEntry::query()
            ->where('user_id', $user->id)
            ->whereBetween('entry_date', [Carbon::today()->subDays(6), Carbon::today()])
            ->orderBy('entry_date')
            ->get();

        return response()->json([
            'header' => [
                'appName' => 'GymFlow',
                'memberName' => $user->name,
                'notificationCount' => 2,
            ],
            'setup' => [
                'completed' => (bool) $user->profile?->onboarding_completed,
                'questionsCompleted' => 6,
                'questionsTotal' => 6,
            ],
            'todaySession' => $todaySession ? [
                'title' => $todaySession->workout->title,
                'dateLabel' => $todaySession->scheduled_for->format('D, F j'),
                'timeLabel' => $todaySession->scheduled_for->format('g:ia').' - '.$todaySession->scheduled_for->copy()->addMinutes($todaySession->workout->duration_minutes)->format('g:ia'),
                'difficulty' => $todaySession->workout->difficulty,
                'durationMinutes' => $todaySession->workout->duration_minutes,
                'imageUrl' => $todaySession->workout->image_url,
                'location' => $todaySession->location,
            ] : null,
            'promoCard' => [
                'title' => 'Spring Membership Sale',
                'subtitle' => 'Get Fit This Spring!',
                'badge' => '50% OFF',
            ],
            'featuredPrograms' => $featuredPrograms->map(fn (Workout $workout) => [
                'id' => $workout->id,
                'title' => $workout->title,
                'description' => $workout->description,
                'difficulty' => $workout->difficulty,
                'durationMinutes' => $workout->duration_minutes,
                'exercisesCount' => $workout->exercises_count,
                'caloriesBurned' => $workout->calories_burned,
                'category' => $workout->category,
                'imageUrl' => $workout->image_url,
                'sourceType' => $workout->source_type,
                'coachName' => $workout->coach_name,
                'isFeatured' => $workout->is_featured,
                'tags' => $workout->tags ?? [],
            ]),
            'progressSummary' => [
                'workoutsThisWeek' => $weekEntries->sum('workouts_completed'),
                'activeMinutesThisWeek' => $weekEntries->sum('active_minutes'),
                'averageCompletionRate' => (int) round($weekEntries->avg('completion_rate') ?? 0),
            ],
        ]);
    }
}
