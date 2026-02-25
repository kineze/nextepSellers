<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CurfoxCity;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name_en', 'name_si', 'name_ta',
        'sub_name_en', 'sub_name_si', 'sub_name_ta', 
        'postcode', 'latitude', 'longitude'
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function curfoxCities()
    {
        return $this->hasMany(CurfoxCity::class, 'system_city_id');
    }
}
