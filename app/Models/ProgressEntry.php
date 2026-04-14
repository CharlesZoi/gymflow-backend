<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressEntry extends Model
{
    /** @use HasFactory<\Database\Factories\ProgressEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_date',
        'workouts_completed',
        'active_minutes',
        'calories_burned',
        'weight_kg',
        'completion_rate',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'weight_kg' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
