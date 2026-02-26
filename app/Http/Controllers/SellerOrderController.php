<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\Order;
use App\Models\RoyalExpressLogin;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SellerOrderController extends Controller
{
    public function adminOrderFilterOptions()
    {
        $sellers = Seller::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($seller) {
                $name = trim(($seller->first_name ?? '') . ' ' . ($seller->last_name ?? ''));
                return [
                    'id' => $seller->id,
                    'label' => $name !== '' ? $name : ($seller->email ?: ('Seller #' . $seller->id)),
                ];
            })
            ->values();

        return response()->json([
            'sellers' => $sellers,
        ]);
    }

    public function adminDraftOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
            ])
            ->where(function ($q) {
                $q->where('status', 'draft')
                    ->orWhere('is_draft', true);
            })
            ->latest('order_datetime')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('order_datetime', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('order_datetime', '<=', $validated['date_to']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'orders' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function adminShow(Order $order)
    {
        $order->load([
            'seller:id,first_name,last_name,email,phone',
            'city:id,name_en,district_id',
            'city.district:id,name_en',
            'customer:id,default_name,primary_phone,additional_phone,email,notes',
            'items:id,order_id,product_id,product_variant_id,quantity,price',
            'items.product:id,title,product_code',
            'items.variant:id,sku,attributes,price',
            'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks,created_at',
            'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function adminDeliveryTimeline(Request $request, Order $order)
    {
        $validated = $request->validate([
            'refresh' => ['nullable', 'boolean'],
        ]);

        $refresh = (bool) ($validated['refresh'] ?? true);

        $timeline = collect();
        $latestStatus = null;
        $waybill = trim((string) ($order->waybill_no ?? ''));

        $courierMessage = null;
        if ($waybill === '') {
            $courierMessage = 'Waybill is not assigned for this order yet.';
        } elseif ($refresh) {
            [$trackingRows, $trackingError] = $this->fetchRoyalExpressTracking($waybill);
            if ($trackingError) {
                $courierMessage = $trackingError;
            } else {
                foreach (collect($trackingRows)->values() as $index => $row) {
                    $statusName = data_get($row, 'status.name')
                        ?? data_get($row, 'status')
                        ?? data_get($row, 'delivery_status')
                        ?? 'Tracking update';

                    $location = data_get($row, 'city.name')
                        ?? data_get($row, 'city')
                        ?? data_get($row, 'location')
                        ?? data_get($row, 'hub_name');

                    $remark = data_get($row, 'remark') ?? data_get($row, 'remarks') ?? data_get($row, 'message');
                    $details = collect([$location, $remark])->filter()->implode(' | ');

                    $eventAt = data_get($row, 'updated_at')
                        ?? data_get($row, 'created_at')
                        ?? data_get($row, 'event_at')
                        ?? data_get($row, 'event_time')
                        ?? data_get($row, 'scanned_at');

                    $timestamp = $this->toIsoDateTime($eventAt);
                    $timeline->push([
                        'title' => (string) $statusName,
                        'source' => 'courier',
                        'details' => $details !== '' ? $details : null,
                        'at' => $timestamp,
                        'sort_at' => $timestamp,
                        'sequence' => $index,
                    ]);
                }

                $latestStatus = data_get($trackingRows, '0.status.name')
                    ?? data_get($trackingRows, '0.status')
                    ?? data_get($trackingRows, '0.delivery_status');
            }
        }

        $sorted = $timeline
            ->sort(function ($a, $b) {
                $aTs = !empty($a['sort_at']) ? strtotime((string) $a['sort_at']) : PHP_INT_MIN;
                $bTs = !empty($b['sort_at']) ? strtotime((string) $b['sort_at']) : PHP_INT_MIN;

                if ($aTs === $bTs) {
                    // Preserve insertion order when timestamps are equal/missing.
                    return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
                }

                // Latest first.
                return $bTs <=> $aTs;
            })
            ->values()
            ->map(function ($event) {
                unset($event['sort_at'], $event['sequence']);
                return $event;
            });

        return response()->json([
            'order_id' => $order->id,
            'current_status' => $order->status,
            'delivery_status' => $latestStatus ?: $order->delivery_status,
            'timeline' => $sorted,
            'courier_message' => $courierMessage,
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,draft,approved,confirmed,packed,shipped,completed,cancelled'],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $status = $validated['status'] ?? 'all';
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->with([
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
            ])
            ->latest('order_datetime')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'orders' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function cities(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $limit = max(5, min((int) $request->query('limit', 15), 50));

        $query = City::query()
            ->select('id', 'name_en')
            ->orderBy('name_en');

        if ($search !== '') {
            $query->where('name_en', 'like', '%' . $search . '%');
        }

        return response()->json([
            'cities' => $query->limit($limit)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_datetime' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,approved,confirmed,packed,shipped,completed,cancelled'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'total_discount' => ['nullable', 'numeric', 'min:0'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],

            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'customer.additional_phone' => ['nullable', 'string', 'max:30'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.address' => ['required', 'string', 'max:5000'],
            'customer.city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'customer.city' => ['nullable', 'string', 'max:255'],
            'customer.notes' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:varients,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $customerData = $validated['customer'];
        $phone = $this->normalizePhone($customerData['phone']);
        $customerKey = 'phone:' . $phone;

        $cityId = !empty($customerData['city_id']) ? (int) $customerData['city_id'] : null;
        if (!$cityId && !empty($customerData['city'])) {
            $cityName = trim((string) $customerData['city']);
            $cityId = City::query()
                ->whereRaw('LOWER(name_en) = ?', [mb_strtolower($cityName)])
                ->value('id');
        }

        $items = collect($validated['items']);
        $netTotal = $items->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $deliveryCharge = (float) ($validated['delivery_charge'] ?? 0);
        $totalDiscount = (float) ($validated['total_discount'] ?? 0);
        $commissionAmount = (float) ($validated['commission_amount'] ?? 0);
        $collectable = $netTotal + $deliveryCharge - $totalDiscount;

        $order = DB::transaction(function () use (
            $seller,
            $customerKey,
            $customerData,
            $cityId,
            $validated,
            $items,
            $netTotal,
            $deliveryCharge,
            $totalDiscount,
            $commissionAmount,
            $collectable,
            $phone
        ) {
            $customer = Customer::updateOrCreate(
                [
                    'seller_id' => $seller->id,
                    'customer_key' => $customerKey,
                ],
                [
                    'primary_phone' => $phone,
                    'additional_phone' => $this->nullableString($customerData['additional_phone'] ?? null),
                    'email' => $this->nullableString($customerData['email'] ?? null),
                    'default_name' => $customerData['name'],
                    'default_address' => $customerData['address'],
                    'city_id' => $cityId,
                    'notes' => $this->nullableString($customerData['notes'] ?? null),
                    'status' => 'active',
                    'last_order_at' => now(),
                ]
            );

            $customer->increment('orders_count');

            $order = Order::create([
                'order_datetime' => $validated['order_datetime'] ?? now(),
                'status' => $validated['status'] ?? 'draft',
                'is_draft' => ($validated['status'] ?? 'draft') === 'draft',
                'net_total' => $netTotal,
                'total_collectable_amount' => $collectable,
                'delivery_charge' => $deliveryCharge,
                'total_discount' => $totalDiscount,
                'commission_amount' => $commissionAmount,
                'seller_id' => $seller->id,
                'customer_id' => $customer->id,
                'customer_name' => $customerData['name'],
                'address' => $customerData['address'],
                'phone' => $phone,
                'additional_phone' => $this->nullableString($customerData['additional_phone'] ?? null),
                'city_id' => $cityId,
            ]);

            $order->items()->createMany(
                $items->map(fn ($item) => [
                    'product_id' => (int) $item['product_id'],
                    'product_variant_id' => !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                ])->all()
            );

            return $order;
        });

        return response()->json([
            'message' => 'Order submitted successfully.',
            'order_id' => $order->id,
            'status' => $order->status,
        ], 201);
    }

    public function requestWaybill(Request $request, Order $order)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        if ((int) $order->seller_id !== (int) $seller->id) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $order->loadMissing([
            'city.curfoxCities.state',
            'city.district',
            'items.product:id,title',
        ]);

        if ($order->waybill_no && !$order->is_draft && $order->status === 'approved') {
            return response()->json([
                'message' => 'Waybill already assigned for this order.',
                'waybill' => $order->waybill_no,
                'order' => $order,
            ]);
        }

        if (!$order->is_draft && $order->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft orders can request a waybill.',
            ], 422);
        }

        [$waybill, $errorResponse] = $this->requestRoyalExpressWaybill($order);
        if ($errorResponse) {
            return $errorResponse;
        }

        $order->fill([
            'waybill_no' => $waybill,
            'status' => 'approved',
            'is_draft' => false,
        ])->save();

        $order->load([
            'city:id,name_en',
            'customer:id,default_name,primary_phone,additional_phone,email,notes',
            'items:id,order_id,product_id,product_variant_id,quantity,price',
            'items.product:id,title,product_code',
            'items.variant:id,sku,attributes',
        ]);

        return response()->json([
            'message' => 'Waybill requested successfully and order approved.',
            'waybill' => $order->waybill_no,
            'order' => $order,
        ]);
    }

    public function adminApprove(Request $request, Order $order)
    {
        if (!$this->isDraftOrder($order)) {
            return response()->json([
                'message' => 'Only draft orders can be approved.',
            ], 422);
        }

        $order->loadMissing([
            'city.curfoxCities.state',
            'city.district',
            'items.product:id,title',
        ]);

        [$waybill, $errorResponse] = $this->requestRoyalExpressWaybill($order);
        if ($errorResponse) {
            return $errorResponse;
        }

        $order->update([
            'waybill_no' => $waybill,
            'status' => 'approved',
            'is_draft' => false,
        ]);

        return response()->json([
            'message' => 'Order approved successfully.',
            'order_id' => $order->id,
            'waybill' => $order->waybill_no,
        ]);
    }

    public function adminBulkApprove(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $orders = Order::query()
            ->whereIn('id', $validated['order_ids'])
            ->with([
                'city.curfoxCities.state',
                'city.district',
                'items.product:id,title',
            ])
            ->get();

        $approved = [];
        $failed = [];

        foreach ($orders as $order) {
            if (!$this->isDraftOrder($order)) {
                $failed[] = [
                    'order_id' => $order->id,
                    'reason' => 'Order is not in draft status.',
                ];
                continue;
            }

            [$waybill, $errorResponse] = $this->requestRoyalExpressWaybill($order);
            if ($errorResponse) {
                $failed[] = [
                    'order_id' => $order->id,
                    'reason' => $errorResponse->getData(true)['message'] ?? 'Approval failed.',
                ];
                continue;
            }

            $order->update([
                'waybill_no' => $waybill,
                'status' => 'approved',
                'is_draft' => false,
            ]);

            $approved[] = [
                'order_id' => $order->id,
                'waybill' => $waybill,
            ];
        }

        return response()->json([
            'message' => count($approved) . ' order(s) approved, ' . count($failed) . ' failed.',
            'approved' => $approved,
            'failed' => $failed,
        ]);
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\s+/', '', trim($value)) ?? trim($value);
    }

    private function toIsoDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildDispatchDateTime($dispatchDate, $dispatchTime, $fallback = null)
    {
        try {
            if (!empty($dispatchDate) && !empty($dispatchTime)) {
                $datePart = Carbon::parse($dispatchDate)->toDateString();
                $timePart = Carbon::parse($dispatchTime)->format('H:i:s');

                return Carbon::parse($datePart . ' ' . $timePart);
            }

            if (!empty($dispatchDate)) {
                return Carbon::parse($dispatchDate);
            }

            if (!empty($dispatchTime)) {
                return Carbon::parse($dispatchTime);
            }
        } catch (\Throwable $e) {
            // Fallback below if either piece has unexpected shape.
        }

        return $fallback;
    }

    private function nullableString($value): ?string
    {
        $str = trim((string) ($value ?? ''));
        return $str === '' ? null : $str;
    }

    private function isDraftOrder(Order $order): bool
    {
        return $order->status === 'draft' || (bool) $order->is_draft;
    }

    private function fetchRoyalExpressTracking(string $waybill): array
    {
        $baseUrl = rtrim((string) config('services.royal_express.base_url'), '/');
        $tenant = (string) config('services.royal_express.tenant');

        if ($baseUrl === '' || $tenant === '') {
            return [[], 'Royal Express configuration is missing.'];
        }

        $login = RoyalExpressLogin::query()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->latest('id')
            ->first();

        if (!$login) {
            return [[], 'No active Royal Express login found.'];
        }

        if ($login->token_expiry && now()->greaterThan($login->token_expiry)) {
            return [[], 'Royal Express token expired. Please login again.'];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $login->token,
                'Content-Type' => 'application/json',
                'X-tenant' => $tenant,
            ])->retry(1, 200)->get($baseUrl . '/api/public/merchant/order/tracking-info', [
                'waybill_number' => $waybill,
            ]);

            if (!$response->successful()) {
                return [[], $response->json('message') ?? 'Failed to fetch courier tracking.'];
            }

            $rows = $response->json('data');
            if (!is_array($rows)) {
                return [[], 'Courier tracking response did not contain timeline data.'];
            }

            return [$rows, null];
        } catch (\Throwable $e) {
            return [[], 'Courier tracking request failed.'];
        }
    }

    private function requestRoyalExpressWaybill(Order $order): array
    {
        $baseUrl = rtrim((string) config('services.royal_express.base_url'), '/');
        $tenant = (string) config('services.royal_express.tenant');

        if ($baseUrl === '' || $tenant === '') {
            return [null, response()->json([
                'message' => 'Royal Express configuration is missing.',
            ], 500)];
        }

        $login = RoyalExpressLogin::query()
            ->where('is_active', true)
            ->whereNotNull('token')
            ->latest('id')
            ->first();

        if (!$login) {
            return [null, response()->json([
                'message' => 'No active Royal Express login found.',
            ], 422)];
        }

        if (empty($login->merchant_business_id)) {
            return [null, response()->json([
                'message' => 'Merchant business ID missing in Royal Express login.',
            ], 422)];
        }

        if ($login->token_expiry && now()->greaterThan($login->token_expiry)) {
            return [null, response()->json([
                'message' => 'Royal Express token expired. Please login again.',
            ], 422)];
        }

        $curfoxCity = $order->city?->curfoxCities?->first();
        $destinationCity = $curfoxCity?->name ?: ($order->city?->name_en ?: 'Colombo 01');
        $destinationState = $curfoxCity?->state?->name
            ?: ($order->city?->district?->name_en ?: 'Colombo');

        $description = $order->items
            ->pluck('product.title')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        if ($description === '') {
            $description = 'Order items';
        }
        if (mb_strlen($description) > 250) {
            $description = mb_substr($description, 0, 247) . '...';
        }

        $body = [
            'general_data' => [
                'merchant_business_id' => $login->merchant_business_id,
                'origin_city_name' => $login->city ?: 'Colombo 01',
                'origin_state_name' => $login->state ?: 'Colombo',
            ],
            'order_data' => [[
                'order_no' => (string) $order->id,
                'customer_name' => (string) ($order->customer_name ?: 'Customer'),
                'customer_address' => (string) ($order->address ?: 'Address'),
                'customer_phone' => (string) ($order->phone ?: ''),
                'customer_secondary_phone' => $order->additional_phone ?: null,
                'destination_city_name' => $destinationCity,
                'destination_state_name' => $destinationState,
                'cod' => (int) round((float) ($order->total_collectable_amount ?? 0)),
                'weight' => 1.0,
                'description' => $description,
                'remark' => '',
            ]],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $login->token,
            'Content-Type' => 'application/json',
            'X-tenant' => $tenant,
        ])->retry(2, 200)->post($baseUrl . '/api/public/merchant/order/single', $body);

        if ($response->clientError()) {
            return [null, response()->json([
                'message' => $response->json('message') ?? 'Royal Express validation failed.',
                'errors' => $response->json('errors') ?? null,
                'raw' => $response->json(),
            ], 422)];
        }

        if (!$response->successful()) {
            return [null, response()->json([
                'message' => 'Royal Express API error.',
                'status' => $response->status(),
                'raw' => $response->json(),
            ], $response->status())];
        }

        $waybillData = $response->json('data');
        $waybill = is_array($waybillData) ? ($waybillData[0] ?? null) : null;

        if (!$waybill) {
            return [null, response()->json([
                'message' => 'Royal Express did not return a waybill number.',
                'raw' => $response->json(),
            ], 422)];
        }

        return [(string) $waybill, null];
    }
}
