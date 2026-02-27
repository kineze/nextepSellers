<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'seller_id',
        'bank_id',
        'bank',
        'account_no',
        'swift_code',
        'name',
        'branch',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
