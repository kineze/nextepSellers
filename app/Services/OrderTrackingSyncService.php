<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RoyalExpressLogin;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderTrackingSyncService
{
    public function validateTrackingContext(): array
    {
        $baseUrl = rtrim((string) config('services.royal_express.base_url'), '/');
        $tenant = (string) config('services.royal_express.tenant');

        if ($baseUrl === '' || $tenant === '') {
            return [
                'ready' => false,
                'message' => 'Royal Express configuration is missing.',
            ];
        }

        $login = RoyalExpressLogin::query()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->latest('id')
            ->first();

        if (!$login) {
            return [
                'ready' => false,
                'message' => 'No active Royal Express login found.',
            ];
        }

        if ($login->token_expiry && now()->greaterThan($login->token_expiry)) {
            return [
                'ready' => false,
                'message' => 'Royal Express token expired. Please login again.',
            ];
        }

        return [
            'ready' => true,
            'message' => null,
        ];
    }

    public function syncOrderById(int $orderId): array
    {
        $context = $this->resolveTrackingContext();
        if (!$context['ready']) {
            Log::warning('Tracking sync skipped: context not ready', [
                'order_id' => $orderId,
                'error' => $context['message'] ?? 'Unknown context error',
            ]);
            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => $context['message'],
            ];
        }

        $order = Order::query()->find($orderId);
        if (!$order || empty($order->waybill_no)) {
            Log::warning('Tracking sync skipped: order missing or waybill missing', [
                'order_id' => $orderId,
            ]);
            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $context['token'],
                'Content-Type' => 'application/json',
                'X-tenant' => $context['tenant'],
            ])->retry(2, 200)->get($context['base_url'] . '/api/public/merchant/order/tracking-info', [
                'waybill_number' => $order->waybill_no,
            ]);
        } catch (\Throwable $e) {
            Log::error('Tracking API request exception', [
                'order_id' => (int) $order->id,
                'waybill_no' => (string) $order->waybill_no,
                'error' => $e->getMessage(),
            ]);

            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => 'Tracking API request exception.',
            ];
        }

        if (!$response->successful()) {
            Log::warning('Tracking API request failed', [
                'order_id' => (int) $order->id,
                'waybill_no' => (string) $order->waybill_no,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => $response->json('message') ?: 'Tracking API request failed.',
            ];
        }

        $rows = $response->json('data');
        if (!is_array($rows) || empty($rows)) {
            Log::warning('Tracking API returned empty or invalid data', [
                'order_id' => (int) $order->id,
                'waybill_no' => (string) $order->waybill_no,
            ]);

            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => null,
            ];
        }

        $latestStatus = data_get($rows, '0.status.name')
            ?? data_get($rows, '0.status')
            ?? data_get($rows, '0.delivery_status');

        if (!is_string($latestStatus) || trim($latestStatus) === '') {
            Log::warning('Tracking API returned row without status', [
                'order_id' => (int) $order->id,
                'waybill_no' => (string) $order->waybill_no,
                'first_row' => $rows[0] ?? null,
            ]);

            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => null,
            ];
        }

        $localStatus = $this->mapLocalStatus($latestStatus);

        $fresh = Order::query()->find($order->id);
        if (!$fresh) {
            return [
                'updated' => false,
                'completed' => false,
                'cancelled' => false,
                'points_awarded' => 0,
                'level_upgraded' => false,
                'error' => null,
            ];
        }

        $changed = false;
        $completed = false;
        $cancelled = false;
        if ($fresh->delivery_status !== $latestStatus) {
            $fresh->delivery_status = $latestStatus;
            $changed = true;
        }

        if ($localStatus === 'completed' && $fresh->status !== 'completed') {
            $fresh->status = 'completed';
            $fresh->completed_at = now();
            $fresh->cancelled_at = null;
            $completed = true;
            $changed = true;
        } elseif ($localStatus === 'cancelled' && $fresh->status !== 'cancelled') {
            $fresh->status = 'cancelled';
            $fresh->cancelled_at = now();
            $cancelled = true;
            $changed = true;
        }

        // When delivered, payment becomes available unless it's already marked as paid.
        if ($localStatus === 'completed' || $fresh->status === 'completed') {
            $currentPaymentStatus = strtolower(trim((string) ($fresh->payment_status ?? '')));
            if ($currentPaymentStatus !== 'paid' && $currentPaymentStatus !== 'available') {
                $fresh->payment_status = 'available';
                $changed = true;
            }
        }

        if ($changed) {
            $fresh->save();
        }

        $this->syncPaymentLedger($fresh);

        $pointsAwarded = 0;
        $levelUpgraded = false;
        if ($fresh->status === 'completed') {
            $award = $this->awardPointsAndUpgradeLevel($fresh->id);
            $pointsAwarded = (int) ($award['points'] ?? 0);
            $levelUpgraded = (bool) ($award['level_upgraded'] ?? false);
        }

        return [
            'updated' => $changed,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'points_awarded' => $pointsAwarded,
            'level_upgraded' => $levelUpgraded,
            'error' => null,
        ];
    }

    private function mapLocalStatus(string $courierStatus): ?string
    {
        $status = Str::upper(trim($courierStatus));

        $completedStatuses = [
            'DELIVERED',
            'PARTIALLY DELIVERED',
            'COMPLETED',
        ];

        if (in_array($status, $completedStatuses, true)) {
            return 'completed';
        }

        $cancelledStatuses = [
            'CANCELLED',
            'FAILED TO DELIVER',
            'RETURN TO CLIENT',
            'RETURNED TO MERCHANT',
            'RECEIVED FAILED ORDER',
            'UNDELIVERED',
        ];

        if (in_array($status, $cancelledStatuses, true)) {
            return 'cancelled';
        }

        return null;
    }

    private function awardPointsAndUpgradeLevel(int $orderId): array
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::query()
                ->lockForUpdate()
                ->find($orderId);

            if (!$order || $order->status !== 'completed') {
                return [
                    'awarded' => false,
                    'points' => 0,
                    'level_upgraded' => false,
                ];
            }

            if ($order->points_awarded_at !== null) {
                return [
                    'awarded' => false,
                    'points' => 0,
                    'level_upgraded' => false,
                ];
            }

            $seller = Seller::query()
                ->lockForUpdate()
                ->find($order->seller_id);

            if (!$seller) {
                $order->points_awarded = 0;
                $order->points_awarded_at = now();
                $order->save();

                return [
                    'awarded' => false,
                    'points' => 0,
                    'level_upgraded' => false,
                ];
            }

            $points = $this->calculatePointsForOrder($order);
            $oldLevelId = (int) ($seller->seller_level_id ?? 0);

            if ($points > 0) {
                $seller->points = max(0, (int) $seller->points) + $points;
            }

            $newLevel = Level::query()
                ->where('points', '<=', (int) $seller->points)
                ->orderByDesc('points')
                ->orderByDesc('level_no')
                ->first();

            $levelUpgraded = false;
            if ($newLevel && (int) $newLevel->id !== $oldLevelId) {
                $seller->seller_level_id = (int) $newLevel->id;
                $levelUpgraded = true;
            }

            $seller->save();

            $order->points_awarded = $points;
            $order->points_awarded_at = now();
            $order->save();

            return [
                'awarded' => $points > 0,
                'points' => $points,
                'level_upgraded' => $levelUpgraded,
            ];
        });
    }

    private function calculatePointsForOrder(Order $order): int
    {
        $lkrPerPoint = (float) config('seller.lkr_per_point', 100);
        if ($lkrPerPoint <= 0) {
            $lkrPerPoint = 100;
        }

        $baseAmount = max(0, (float) ($order->net_total ?? 0) - (float) ($order->total_discount ?? 0));

        return (int) floor($baseAmount / $lkrPerPoint);
    }

    private function syncPaymentLedger(Order $order): void
    {
        $paymentStatus = strtolower(trim((string) ($order->payment_status ?? 'pending')));
        if (!in_array($paymentStatus, ['available', 'paid'], true)) {
            return;
        }

        $amount = (float) ($order->total_collectable_amount ?? 0);
        $availableAt = $order->completed_at ?? now();
        $paidAt = $paymentStatus === 'paid' ? ($order->updated_at ?? now()) : null;

        Payment::query()->updateOrCreate(
            ['order_id' => (int) $order->id],
            [
                'seller_id' => (int) $order->seller_id,
                'amount' => $amount,
                'status' => $paymentStatus,
                'available_at' => $availableAt,
                'paid_at' => $paidAt,
            ]
        );
    }

    private function resolveTrackingContext(): array
    {
        $baseUrl = rtrim((string) config('services.royal_express.base_url'), '/');
        $tenant = (string) config('services.royal_express.tenant');

        if ($baseUrl === '' || $tenant === '') {
            return [
                'ready' => false,
                'message' => 'Royal Express configuration is missing.',
            ];
        }

        $login = RoyalExpressLogin::query()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->latest('id')
            ->first();

        if (!$login) {
            return [
                'ready' => false,
                'message' => 'No active Royal Express login found.',
            ];
        }

        if ($login->token_expiry && now()->greaterThan($login->token_expiry)) {
            return [
                'ready' => false,
                'message' => 'Royal Express token expired. Please login again.',
            ];
        }

        return [
            'ready' => true,
            'message' => null,
            'base_url' => $baseUrl,
            'tenant' => $tenant,
            'token' => (string) $login->token,
        ];
    }
}
