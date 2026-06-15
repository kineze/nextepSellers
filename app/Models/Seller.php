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
        'affiliate_seller_id',
        'seller_level_id',
        'points',
        'first_success_order_date',
        'dilivery_score',
        'is_restrict',
        'referral_code',
        'referral_link',
        'email_verified',
        'phone_verified',
        'agreement_accepted',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'agreement_accepted' => 'boolean',
        'points' => 'integer',
        'first_success_order_date' => 'date',
        'dilivery_score' => 'integer',
        'is_restrict' => 'boolean',
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

    public function affiliateSeller()
    {
        return $this->belongsTo(self::class, 'affiliate_seller_id');
    }

    public function referredSellers()
    {
        return $this->hasMany(self::class, 'affiliate_seller_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function penalties()
    {
        return $this->hasMany(SellerPenalty::class);
    }

    public function salesTargetResults()
    {
        return $this->hasMany(SellerSalesTarget::class);
    }

    public function recalculateDeliveryScore(): int
    {
        $completedOrders = $this->orders()
            ->where('status', 'completed')
            ->count();

        $cancelledOrders = $this->orders()
            ->where('status', 'cancelled')
            ->count();

        $totalDeliveryAttempts = $completedOrders + $cancelledOrders;
        $score = $totalDeliveryAttempts > 0
            ? (int) round(($completedOrders / $totalDeliveryAttempts) * 100)
            : 100;

        $this->forceFill([
            'dilivery_score' => $score,
        ])->saveQuietly();

        return $score;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function affiliateCommissionsEarned()
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_seller_id');
    }

    public function affiliateCommissionsFromReferrals()
    {
        return $this->hasMany(AffiliateCommission::class, 'seller_id');
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
