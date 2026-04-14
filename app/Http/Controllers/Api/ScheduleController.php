<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
}
