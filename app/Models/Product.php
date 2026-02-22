<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'small_description',
        'long_description',
        'category_id',
        'product_code',
        'is_active',
        'has_varients',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_varients' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function varients(): HasMany
    {
        return $this->hasMany(Varient::class);
    }
}
