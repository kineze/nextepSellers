<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoyalExpressLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_name',
        'partner_user_id',
        'merchant_id',
        'merchant_business_id',
        'email',
        'token',
        'token_expiry',
        'first_name',
        'last_name',
        'role_name',
        'is_active',
        'city',
        'state',
    ];

    protected $casts = [
        'token_expiry' => 'datetime',
        'is_active' => 'boolean',
    ];
}
