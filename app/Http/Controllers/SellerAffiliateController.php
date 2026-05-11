<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerAffiliateController extends Controller
{
    public function show(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found.',
            ], 403);
        }

        $referredSellers = Seller::query()
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 'status', 'created_at')
            ->where('affiliate_seller_id', $seller->id)
            ->withCount('orders')
            ->latest('id')
            ->get();

        $commissionSummaryBySeller = AffiliateCommission::query()
            ->selectRaw('seller_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'available' THEN amount ELSE 0 END), 0) as available_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid_amount")
            ->where('affiliate_seller_id', $seller->id)
            ->groupBy('seller_id')
            ->get()
            ->keyBy('seller_id');

        $referredSellers = $referredSellers->map(function (Seller $item) use ($commissionSummaryBySeller) {
            $summary = $commissionSummaryBySeller->get((int) $item->id);

            return [
                'id' => (int) $item->id,
                'name' => trim((string) $item->first_name . ' ' . (string) $item->last_name) ?: 'Seller #' . $item->id,
                'email' => $item->email,
                'phone' => $item->phone,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'orders_count' => (int) ($item->orders_count ?? 0),
                'affiliate_total_amount' => (float) ($summary->total_amount ?? 0),
                'affiliate_available_amount' => (float) ($summary->available_amount ?? 0),
                'affiliate_paid_amount' => (float) ($summary->paid_amount ?? 0),
            ];
        })->values();

        $availableCommission = (float) AffiliateCommission::query()
            ->where('affiliate_seller_id', $seller->id)
            ->where('status', 'available')
            ->sum('amount');

        $paidCommission = (float) AffiliateCommission::query()
            ->where('affiliate_seller_id', $seller->id)
            ->where('status', 'paid')
            ->sum('amount');

        return response()->json([
            'seller' => [
                'id' => (int) $seller->id,
                'referral_code' => $seller->referral_code,
                'referral_path' => $seller->referral_code
                    ? route('sellerRegistration', ['ref' => $seller->referral_code], false)
                    : null,
            ],
            'stats' => [
                'total_referrals' => $referredSellers->count(),
                'active_referrals' => $referredSellers->where('status', 'approved')->count(),
                'orders_from_referrals' => (int) $referredSellers->sum('orders_count'),
                'available_commission_lkr' => round($availableCommission, 2),
                'paid_commission_lkr' => round($paidCommission, 2),
                'total_commission_lkr' => round($availableCommission + $paidCommission, 2),
            ],
            'referred_sellers' => $referredSellers,
        ]);
    }

    public function generate(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found.',
            ], 403);
        }

        $code = null;
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $candidate = strtoupper('NEX-' . Str::random(8));
            $exists = Seller::query()
                ->where('referral_code', $candidate)
                ->where('id', '!=', $seller->id)
                ->exists();

            if (!$exists) {
                $code = $candidate;
                break;
            }
        }

        if (!$code) {
            return response()->json([
                'message' => 'Unable to generate referral code. Please try again.',
            ], 422);
        }

        $seller->referral_code = $code;
        $seller->referral_link = route('sellerRegistration', ['ref' => $code]);
        $seller->save();

        return response()->json([
            'message' => 'Referral link generated successfully.',
            'seller' => [
                'id' => (int) $seller->id,
                'referral_code' => $seller->referral_code,
                'referral_path' => route('sellerRegistration', ['ref' => $seller->referral_code], false),
            ],
        ]);
    }
}
