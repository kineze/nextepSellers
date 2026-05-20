<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Level extends Model
{
    protected $fillable = [
        'level_no',
        'level_name',
        'points',
        'description',
        'icon_path',
        'is_default',
    ];

    protected $appends = [
        'icon_url',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function productLevels(): HasMany
    {
        return $this->hasMany(ProductLevel::class);
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? Storage::disk('public')->url($this->icon_path) : null;
    }
}
