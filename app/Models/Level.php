<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
