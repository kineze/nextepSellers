<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'waybill_no',
        'status_key',
        'status',
        'is_matched',
        'processed_result',
        'processed_at',
    ];

    protected $casts = [
        'is_matched' => 'boolean',
        'processed_result' => 'array',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
