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

    public function storeCheckIn(Request $request)
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'workouts_completed' => ['nullable', 'integer', 'min:0', 'max:20'],
            'active_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'calories_burned' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:500'],
            'completion_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $entryDate = Carbon::parse($data['entry_date'])->startOfDay();

        $entry = ProgressEntry::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'entry_date' => $entryDate,
            ],
            [
                'workouts_completed' => $data['workouts_completed'] ?? 0,
                'active_minutes' => $data['active_minutes'] ?? 0,
                'calories_burned' => $data['calories_burned'] ?? 0,
                'weight_kg' => $data['weight_kg'] ?? null,
                'completion_rate' => $data['completion_rate'] ?? 0,
            ],
        );

        return response()->json([
            'message' => $entry->wasRecentlyCreated ? 'Progress check-in created.' : 'Progress check-in updated.',
            'entry' => $this->mapProgressEntry($entry),
        ], $entry->wasRecentlyCreated ? 201 : 200);
    }

    private function mapProgressEntry(ProgressEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'entryDate' => $entry->entry_date->toDateString(),
            'workoutsCompleted' => $entry->workouts_completed,
            'activeMinutes' => $entry->active_minutes,
            'caloriesBurned' => $entry->calories_burned,
            'completionRate' => $entry->completion_rate,
            'weightKg' => $entry->weight_kg ? (float) $entry->weight_kg : null,
        ];
    }
}
