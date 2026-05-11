<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'waybill_no',
        'raw_data',
        'status_key',
        'status',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];
}
