<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\WorkoutSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $upcoming = WorkoutSchedule::with('workout')
            ->where('user_id', $user->id)
            ->where('scheduled_for', '>=', Carbon::now()->startOfDay())
            ->orderBy('scheduled_for')
            ->get();

        $recent = WorkoutSchedule::with('workout')
            ->where('user_id', $user->id)
            ->where('scheduled_for', '<', Carbon::now()->startOfDay())
            ->latest('scheduled_for')
            ->take(5)
            ->get();

        return response()->json([
            'upcoming' => $upcoming->map(fn (WorkoutSchedule $item) => $this->mapScheduleItem($item)),
            'recent' => $recent->map(fn (WorkoutSchedule $item) => $this->mapScheduleItem($item)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->scheduleRules());
        $workout = Workout::query()->whereKey($data['workout_id'])->firstOrFail();

        $schedule = WorkoutSchedule::create([
            'user_id' => $request->user()->id,
            'workout_id' => $workout->id,
            'scheduled_for' => Carbon::parse($data['scheduled_for']),
            'location' => $data['location'] ?? null,
            'trainer_name' => $data['trainer_name'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
            'notes' => $data['notes'] ?? null,
        ])->load('workout');

        return response()->json([
            'message' => 'Schedule created.',
            'schedule' => $this->mapScheduleItem($schedule),
        ], 201);
    }

    public function update(Request $request, WorkoutSchedule $schedule)
    {
        $this->authorizeSchedule($request, $schedule);

        $data = $request->validate($this->scheduleRules(requireWorkout: false));
        if (isset($data['scheduled_for'])) {
            $data['scheduled_for'] = Carbon::parse($data['scheduled_for']);
        }

        $schedule->update($data);

        return response()->json([
            'message' => 'Schedule updated.',
            'schedule' => $this->mapScheduleItem($schedule->refresh()->load('workout')),
        ]);
    }

    public function cancel(Request $request, WorkoutSchedule $schedule)
    {
        return $this->setStatus($request, $schedule, 'cancelled');
    }

    public function complete(Request $request, WorkoutSchedule $schedule)
    {
        return $this->setStatus($request, $schedule, 'completed');
    }

    private function setStatus(Request $request, WorkoutSchedule $schedule, string $status)
    {
        $this->authorizeSchedule($request, $schedule);
        $schedule->update(['status' => $status]);

        return response()->json([
            'message' => 'Schedule updated.',
            'schedule' => $this->mapScheduleItem($schedule->refresh()->load('workout')),
        ]);
    }

    private function mapScheduleItem(WorkoutSchedule $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->workout->title,
            'scheduledFor' => $item->scheduled_for->toIso8601String(),
            'dateLabel' => $item->scheduled_for->format('D, M j'),
            'timeLabel' => $item->scheduled_for->format('g:ia'),
            'durationMinutes' => $item->workout->duration_minutes,
            'difficulty' => $item->workout->difficulty,
            'trainerName' => $item->trainer_name,
            'location' => $item->location,
            'status' => $item->status,
            'notes' => $item->notes,
        ];
    }

    private function scheduleRules(bool $requireWorkout = true): array
    {
        return [
            'workout_id' => [$requireWorkout ? 'required' : 'sometimes', 'integer', 'exists:workouts,id'],
            'scheduled_for' => [$requireWorkout ? 'required' : 'sometimes', 'date'],
            'location' => ['nullable', 'string', 'max:120'],
            'trainer_name' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['scheduled', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function authorizeSchedule(Request $request, WorkoutSchedule $schedule): void
    {
        abort_unless($schedule->user_id === $request->user()->id, 404);
    }
}
