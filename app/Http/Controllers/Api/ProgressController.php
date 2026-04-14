<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $entries = ProgressEntry::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('entry_date')
            ->get();

        $cutoff = Carbon::today()->subDays(6);
        $lastSevenDays = $entries->filter(
            fn (ProgressEntry $entry) => $entry->entry_date->greaterThanOrEqualTo($cutoff)
        );
        $latestWeight = optional($entries->last())->weight_kg;
        $startingWeight = optional($entries->first())->weight_kg;

        return response()->json([
            'summary' => [
                'workoutsCompleted' => $entries->sum('workouts_completed'),
                'activeMinutes' => $entries->sum('active_minutes'),
                'caloriesBurned' => $entries->sum('calories_burned'),
                'currentWeightKg' => $latestWeight ? (float) $latestWeight : null,
                'weightChangeKg' => ($latestWeight && $startingWeight) ? round((float) $latestWeight - (float) $startingWeight, 1) : null,
                'averageCompletionRate' => (int) round($entries->avg('completion_rate') ?? 0),
            ],
            'history' => $lastSevenDays->values()->map(fn (ProgressEntry $entry) => [
                'date' => $entry->entry_date->toDateString(),
                'label' => $entry->entry_date->format('D'),
                'workoutsCompleted' => $entry->workouts_completed,
                'activeMinutes' => $entry->active_minutes,
                'caloriesBurned' => $entry->calories_burned,
                'completionRate' => $entry->completion_rate,
                'weightKg' => $entry->weight_kg ? (float) $entry->weight_kg : null,
            ]),
        ]);
    }
}
