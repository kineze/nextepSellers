<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SystemData extends Model
{
    protected $table = 'system_data';

    protected $fillable = [
        'company_name',
        'address',
        'country',
        'phone_number',
        'fax',
        'year_start_month',
        'year_end_month',
        'logo',
    ];

    public function quarters(): HasMany
    {
        return $this->hasMany(SystemQuarter::class);
    }
}
