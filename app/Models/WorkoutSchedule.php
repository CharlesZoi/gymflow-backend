<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_id',
        'scheduled_for',
        'location',
        'trainer_name',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }
}
