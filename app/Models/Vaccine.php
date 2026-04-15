<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccine extends Model
{
    protected $fillable = [
        'animal_category_id', 'name', 'doses_count', 'interval_days', 'is_lifetime',
    ];

    protected function casts(): array
    {
        return [
            'is_lifetime' => 'boolean',
            'doses_count' => 'integer',
            'interval_days' => 'integer',
        ];
    }

    public function animalCategory(): BelongsTo
    {
        return $this->belongsTo(AnimalCategory::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(VaccineSchedule::class);
    }
}
