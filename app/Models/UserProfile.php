<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /** @use HasFactory<\Database\Factories\UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nickname',
        'first_name',
        'last_name',
        'onboarding_completed',
        'age',
        'gender',
        'body_type',
        'fitness_level',
        'training_days',
        'training_preference',
        'main_fitness_goal',
        'weight_kg',
        'height_cm',
        'primary_goal',
        'focus_area',
        'result_speed',
        'intensity_preference',
        'session_preference_minutes',
        'motivation',
        'blockers',
        'theme',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_completed' => 'boolean',
            'weight_kg' => 'decimal:1',
            'height_cm' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
