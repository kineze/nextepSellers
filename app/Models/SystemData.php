<?php

namespace App\Models;

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
        'logo',
    ];
}
