<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;

    private static array $logSnapshots = [];
    private const LOGGABLE_FIELDS = [
        'order_datetime',
        'status',
        'is_draft',
        'waybill_no',
        'net_total',
        'total_collectable_amount',
        'delivery_charge',
        'total_discount',
        'commission_amount',
        'points_awarded',
        'delivery_status',
        'payment_status',
        'is_damaged',
        'packed_at',
        'shipped_at',
        'completed_at',
        'points_awarded_at',
        'cancelled_at',
    ];

    protected $fillable = [
        'order_datetime',
        'status',
        'is_draft',
        'net_total',
        'total_collectable_amount',
        'delivery_charge',
        'total_discount',
        'commission_amount',
        'points_awarded',
        'waybill_no',
        'packed_at',
        'shipped_at',
        'completed_at',
        'points_awarded_at',
        'cancelled_at',
        'is_damaged',
        'delivery_status',
        'payment_status',
        'invoice_id',
        'bulk_order_request_id',
        'seller_id',
        'customer_id',
        'customer_name',
        'address',
        'phone',
        'additional_phone',
        'city_id',
    ];

    protected $casts = [
        'order_datetime' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_draft' => 'boolean',
        'is_damaged' => 'boolean',
        'net_total' => 'decimal:2',
        'total_collectable_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'points_awarded' => 'integer',
        'points_awarded_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function dispatchNoteItems()
    {
        return $this->hasMany(DispatchNoteItem::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class)->latest('id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function affiliateCommission()
    {
        return $this->hasOne(AffiliateCommission::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bulkOrderRequest()
    {
        return $this->belongsTo(BulkOrderRequest::class);
    }

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            $meaningful = $order->extractMeaningfulValues($order->getAttributes());
            $changes = $order->buildChangePairs([], $meaningful);

            if (empty($changes)) {
                return;
            }

            $order->writeOrderLog(
                eventType: 'created',
                fromStatus: null,
                toStatus: $order->status,
                changes: $changes,
                note: 'Order created'
            );
        });

        static::updating(function (Order $order) {
            self::$logSnapshots[spl_object_id($order)] = [
                'original' => $order->getOriginal(),
                'dirty' => $order->getDirty(),
            ];
        });

        static::updated(function (Order $order) {
            $key = spl_object_id($order);
            $snapshot = self::$logSnapshots[$key] ?? null;
            unset(self::$logSnapshots[$key]);

            if (!$snapshot) {
                return;
            }

            $dirty = collect($snapshot['dirty'] ?? [])
                ->except(['updated_at'])
                ->only(self::LOGGABLE_FIELDS)
                ->toArray();

            if (empty($dirty)) {
                return;
            }

            $original = $snapshot['original'] ?? [];
            $fromStatus = array_key_exists('status', $original) ? $original['status'] : null;
            $toStatus = $order->status;
            $statusChanged = array_key_exists('status', $dirty) && $fromStatus !== $toStatus;

            $changes = $order->buildChangePairs($original, $dirty);

            if (empty($changes)) {
                return;
            }

            $order->writeOrderLog(
                eventType: $statusChanged ? 'status_changed' : 'updated',
                fromStatus: $statusChanged ? $fromStatus : null,
                toStatus: $statusChanged ? $toStatus : null,
                changes: $changes,
                note: $statusChanged ? 'Order status changed' : 'Order updated'
            );
        });
    }

    private function writeOrderLog(
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?array $changes,
        ?string $note
    ): void {
        try {
            $this->logs()->create([
                'user_id' => Auth::id(),
                'event_type' => $eventType,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changes' => $changes,
                'note' => $note,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function extractMeaningfulValues(array $attributes): array
    {
        return collect($attributes)
            ->only(self::LOGGABLE_FIELDS)
            ->toArray();
    }

    private function buildChangePairs(array $original, array $current): array
    {
        $changes = [];
        foreach ($current as $field => $newValue) {
            if (!in_array($field, self::LOGGABLE_FIELDS, true)) {
                continue;
            }

            $oldValue = $original[$field] ?? null;
            if ($oldValue == $newValue && array_key_exists($field, $original)) {
                continue;
            }

            $changes[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $changes;
    }
}
