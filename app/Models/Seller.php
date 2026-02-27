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
        'seller_image',
        'seller_type',
        'tax_number',
        'nic_number',
        'nic_front',
        'nic_back',
        'status',
        'rejection_reason',
        'user_id',
        'seller_level_id',
        'points',
        'email_verified',
        'phone_verified',
        'agreement_accepted',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'agreement_accepted' => 'boolean',
        'points' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessInformation()
    {
        return $this->hasOne(BusinessInformation::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'seller_level_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class);
    }

}
