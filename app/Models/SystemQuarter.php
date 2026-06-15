<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemQuarter extends Model
{
    protected $fillable = [
        'system_data_id',
        'quarter_number',
        'name',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
        'start_label',
        'end_label',
    ];
}
