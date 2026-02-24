<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProductVarient extends Model
{
    protected $fillable = [
        'supplier_product_id',
        'varient_id',
        'supplier_sku',
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

    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }

    public function varient(): BelongsTo
    {
        return $this->belongsTo(Varient::class);
    }
}
