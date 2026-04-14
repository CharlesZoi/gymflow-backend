<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workout extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'difficulty',
        'duration_minutes',
        'exercises_count',
        'calories_burned',
        'category',
        'source_type',
        'coach_name',
        'image_url',
        'is_featured',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkoutSchedule::class);
    }
}
