<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'website',
        'tax_number',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'general_details',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
