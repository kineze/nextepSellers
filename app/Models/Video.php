<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'content_block_id',
        'title',
        'embedded_link',
        'description',
    ];

    public function contentBlock()
    {
        return $this->belongsTo(ContentBlock::class);
    }
}
