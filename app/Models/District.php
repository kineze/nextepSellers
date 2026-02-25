<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['province_id', 'name_en', 'name_si', 'name_ta'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function curfoxStates()
    {
        return $this->hasMany(CurfoxState::class, 'system_district_id');
    }
}
