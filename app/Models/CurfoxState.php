<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurfoxState extends Model
{
     use HasFactory;

    protected $table = 'curfox_states';

    protected $fillable = [
        'ref_no',
        'name',
        'country_id',
        'has_child',
        'system_district_id',
    ];

    public function cities()
    {
        return $this->hasMany(CurfoxCity::class, 'state_id');
    }

    public function systemDistrict()
    {
        return $this->belongsTo(District::class, 'system_district_id');
    }
}
