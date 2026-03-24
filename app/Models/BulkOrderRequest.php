<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BulkOrderRequest extends Model
{
    protected $fillable = [
        'request_no',
        'seller_id',
        'status',
        'orders_count',
    ];

    protected $casts = [
        'orders_count' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
