<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PenaltyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'penalty',
        'description',
        'trigger_type',
        'effective_areas',
        'rules',
        'effective_percentage',
        'is_active',
    ];

    protected $casts = [
        'effective_areas' => 'array',
        'rules' => 'array',
        'effective_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function salesTargets(): BelongsToMany
    {
        return $this->belongsToMany(SalesTarget::class)->withTimestamps();
    }
}
