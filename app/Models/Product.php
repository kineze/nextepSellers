<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title',
        'small_description',
        'long_description',
        'product_video',
        'category_id',
        'product_code',
        'is_active',
        'isbestseller',
        'rating',
        'rating_user_count',
        'has_varients',
        'delivery_fee',
        'is_free_shipping',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'isbestseller' => 'boolean',
        'rating' => 'decimal:1',
        'rating_user_count' => 'integer',
        'has_varients' => 'boolean',
        'delivery_fee' => 'decimal:2',
        'is_free_shipping' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (is_null($product->rating)) {
                $product->rating = random_int(40, 50) / 10;
            }

            if (is_null($product->rating_user_count)) {
                $product->rating_user_count = random_int(1, 999);
            }
        });
    }

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
