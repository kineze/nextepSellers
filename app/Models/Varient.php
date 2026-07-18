<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Varient extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price',
        'reseller_price',
        'maximum_selling_price',
        'stock_quantity',
        'reorder_level',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'reseller_price' => 'decimal:2',
        'maximum_selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierVarients(): HasMany
    {
        return $this->hasMany(SupplierProductVarient::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class, 'variant_id');
    }

    public function lotItems(): HasMany
    {
        return $this->hasMany(LotItem::class, 'variant_id');
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'variant_id');
    }
}
