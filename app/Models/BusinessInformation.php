<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInformation extends Model
{
    protected $table = 'business_informations';

    protected $fillable = [
        'seller_id',
        'business_name',
        'business_registration_number',
        'business_type',
        'business_registered_date',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'country',
        'business_registration_document',
    ];

    protected $casts = [
        'business_registered_date' => 'date',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
