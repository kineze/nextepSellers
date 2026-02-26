<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_note_id',
        'order_id',
        'waybill_snapshot',
        'collectable_amount_snapshot',
        'item_remarks',
    ];

    protected $casts = [
        'collectable_amount_snapshot' => 'decimal:2',
    ];

    public function dispatchNote()
    {
        return $this->belongsTo(DispatchNote::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

