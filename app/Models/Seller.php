<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'seller_type',
        'tax_number',
        'nic_number',
        'nic_front',
        'nic_back',
        'status',
        'rejection_reason',
        'user_id',
        'email_verified',
        'phone_verified',
        'agreement_accepted',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'agreement_accepted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessInformation()
    {
        return $this->hasOne(BusinessInformation::class);
    }

}
