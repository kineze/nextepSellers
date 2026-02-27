<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
    ];

    public function bankDetails()
    {
        return $this->hasMany(BankDetail::class);
    }
}
