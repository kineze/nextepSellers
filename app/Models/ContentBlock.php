<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'title',
        'description',
        'default_block',
    ];

    protected $casts = [
        'default_block' => 'boolean',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
