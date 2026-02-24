<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'level_no',
        'level_name',
        'points',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function productLevels(): HasMany
    {
        return $this->hasMany(ProductLevel::class);
    }
}
