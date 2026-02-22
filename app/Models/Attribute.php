<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];
}
