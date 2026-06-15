<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerSalesTarget extends Model
{
    protected $fillable = [
        'seller_id',
        'sales_target_id',
        'year',
        'frequency',
        'target_type',
        'period_number',
        'period_label',
        'period_start_date',
        'period_end_date',
        'target_value',
        'actual_value',
        'achievement_percentage',
        'is_achieved',
        'penalty_applied',
        'assessed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'period_number' => 'integer',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'is_achieved' => 'boolean',
        'penalty_applied' => 'boolean',
        'assessed_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function salesTarget(): BelongsTo
    {
        return $this->belongsTo(SalesTarget::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(SellerPenalty::class);
    }
}
