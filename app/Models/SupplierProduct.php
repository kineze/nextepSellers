<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierProduct extends Model
{
    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_product_code',
        'cost',
        'lead_days',
        'moq',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'lead_days' => 'integer',
        'moq' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierVarients(): HasMany
    {
        return $this->hasMany(SupplierProductVarient::class);
    }
}
