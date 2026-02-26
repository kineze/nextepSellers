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
        'delivery_fee',
        'is_free_shipping',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_varients' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'is_free_shipping' => 'boolean',
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

    public function productLevels(): HasMany
    {
        return $this->hasMany(ProductLevel::class);
    }

    public function commissions(): HasMany
    {
        return $this->productLevels();
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
