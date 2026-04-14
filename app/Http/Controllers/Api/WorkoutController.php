<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Workout::query()->orderByDesc('is_featured')->orderBy('title');

        if ($request->filled('source')) {
            $query->where('source_type', $request->string('source')->value());
        }

        if ($request->filled('difficulty') && $request->string('difficulty')->lower() !== 'all') {
            $query->whereRaw('lower(difficulty) = ?', [strtolower($request->string('difficulty')->value())]);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->value();

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $workouts = $query->get();

        return response()->json([
            'filters' => [
                'sources' => ['system', 'community'],
                'difficulties' => ['All', 'Beginner', 'Intermediate', 'Advanced'],
            ],
            'workouts' => $workouts->map(fn (Workout $workout) => [
                'id' => $workout->id,
                'title' => $workout->title,
                'description' => $workout->description,
                'difficulty' => $workout->difficulty,
                'durationMinutes' => $workout->duration_minutes,
                'exercisesCount' => $workout->exercises_count,
                'caloriesBurned' => $workout->calories_burned,
                'category' => $workout->category,
                'sourceType' => $workout->source_type,
                'coachName' => $workout->coach_name,
                'imageUrl' => $workout->image_url,
                'isFeatured' => $workout->is_featured,
                'tags' => $workout->tags ?? [],
            ]),
        ]);
    }
}
