<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimalCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class, 'category_id');
    }

    public function vaccines(): HasMany
    {
        return $this->hasMany(Vaccine::class);
    }
}
