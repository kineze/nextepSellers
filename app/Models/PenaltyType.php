<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'penalty',
        'description',
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
}
