<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurfoxCity extends Model
{
    use HasFactory;

    protected $table = 'curfox_cities';

    protected $fillable = [
        'ref_no',
        'name',
        'postal_code',
        'state_id',
        'country_id',
        'zone_id',
        'default_warehouse_id',
        'is_active',
        'system_city_id',
    ];

    public function state()
    {
        return $this->belongsTo(CurfoxState::class, 'state_id');
    }

    public function systemCity()
    {
        return $this->belongsTo(City::class, 'system_city_id');
    }
}
