<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'source' => ['nullable', Rule::in(['system', 'community'])],
            'difficulty' => ['nullable', Rule::in(['All', 'Beginner', 'Intermediate', 'Advanced'])],
            'search' => ['nullable', 'string', 'max:120'],
        ]);
        $query = Workout::query()->orderByDesc('is_featured')->orderBy('title');

        if ($request->filled('source')) {
            $query->where('source_type', $filters['source']);
        }

        if ($request->filled('difficulty') && $request->string('difficulty')->lower() !== 'all') {
            $query->whereRaw('lower(difficulty) = ?', [strtolower($filters['difficulty'])]);
        }

        if ($request->filled('search')) {
            $search = $filters['search'];

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
            'workouts' => $workouts->map(fn (Workout $workout) => $this->mapWorkout($workout)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'difficulty' => ['required', Rule::in(['Beginner', 'Intermediate', 'Advanced'])],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'exercises_count' => ['required', 'integer', 'min:1', 'max:200'],
            'calories_burned' => ['required', 'integer', 'min:0', 'max:5000'],
            'category' => ['required', 'string', 'max:80'],
            'coach_name' => ['nullable', 'string', 'max:120'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
        ]);

        $workout = Workout::create([
            ...$data,
            'user_id' => $request->user()->id,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
            'source_type' => 'user',
            'is_featured' => false,
        ]);

        return response()->json([
            'message' => 'Workout created.',
            'workout' => $this->mapWorkout($workout),
        ], 201);
    }

    public function show(Request $request, Workout $workout)
    {
        abort_unless($workout->source_type !== 'user' || $workout->user_id === $request->user()->id, 404);

        return response()->json([
            'workout' => $this->mapWorkout($workout),
        ]);
    }

    private function mapWorkout(Workout $workout): array
    {
        return [
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
        ];
    }
}
