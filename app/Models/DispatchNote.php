<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no',
        'dispatch_date',
        'dispatch_time',
        'remarks',
        'created_by',
        'net_total',
        'delivery_total',
        'total_collectable',
        'status',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'dispatch_time' => 'datetime:H:i',
        'net_total' => 'decimal:2',
        'delivery_total' => 'decimal:2',
        'total_collectable' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(DispatchNoteItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

