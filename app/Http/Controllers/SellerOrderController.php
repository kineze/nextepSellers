<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Customer;
use App\Models\BulkOrderRequest;
use App\Models\Order;
use App\Models\RoyalExpressLogin;
use App\Models\Seller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
                'bulkOrderRequest:id,request_no,status',
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
            ->where(function ($q) {
                $q->whereNull('bulk_order_request_id')
                    ->orWhereHas('bulkOrderRequest', function ($bulkQ) {
                        $bulkQ->where('status', 'approved');
                    });
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

    public function adminRejectedOrders(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_basis' => ['nullable', 'in:order_date,rejected_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);
        $dateBasis = (string) ($validated['date_basis'] ?? 'rejected_date');
        $dateColumn = $dateBasis === 'order_date' ? 'order_datetime' : 'updated_at';

        $query = Order::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'bulkOrderRequest:id,request_no,status',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
            ])
            ->where('status', 'rejected')
            ->latest('updated_at')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate($dateColumn, '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate($dateColumn, '<=', $validated['date_to']);
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
                    })
                    ->orWhereHas('bulkOrderRequest', function ($bulkQuery) use ($search) {
                        $bulkQuery->where('request_no', 'like', '%' . $search . '%');
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
            'filters' => [
                'date_basis' => $dateBasis,
            ],
        ]);
    }

    public function adminBulkOrderRequests(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'status' => ['nullable', 'in:all,draft,submitted,approved,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $status = (string) ($validated['status'] ?? 'all');
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = BulkOrderRequest::query()
            ->with([
                'seller:id,first_name,last_name,email,phone',
                'orders:id,bulk_order_request_id,order_datetime,status,is_draft,total_collectable_amount,customer_name,phone,city_id',
                'orders.city:id,name_en',
            ])
            ->withSum('orders as total_collectable_amount', 'total_collectable_amount')
            ->latest('id');

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('request_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQ) use ($search) {
                        $sellerQ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('orders', function ($orderQ) use ($search) {
                        $orderQ->where('customer_name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $requests = $query->paginate($perPage);

        return response()->json([
            'requests' => collect($requests->items())->map(function (BulkOrderRequest $request) {
                $request->setAttribute('total_collectable_amount', (float) ($request->total_collectable_amount ?? 0));
                return $request;
            })->values(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function adminApproveBulkOrderRequest(BulkOrderRequest $bulkOrderRequest)
    {
        if ($bulkOrderRequest->status === 'approved') {
            return response()->json([
                'message' => 'Bulk order request already approved.',
            ]);
        }

        $bulkOrderRequest->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Bulk order request approved successfully.',
            'request' => [
                'id' => $bulkOrderRequest->id,
                'request_no' => $bulkOrderRequest->request_no,
                'status' => $bulkOrderRequest->status,
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
            'logs:id,order_id,user_id,event_type,from_status,to_status,note,created_at',
            'logs.user:id,name,email',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }

    public function adminOrderLogShow(Order $order, int $logId)
    {
        $log = $order->logs()
            ->select('id', 'order_id', 'user_id', 'event_type', 'from_status', 'to_status', 'changes', 'note', 'created_at')
            ->with('user:id,name,email')
            ->where('id', $logId)
            ->first();

        if (!$log) {
            return response()->json([
                'message' => 'Order log not found.',
            ], 404);
        }

        return response()->json([
            'log' => $log,
        ]);
    }

    public function adminDeliveryTimeline(Request $request, Order $order)
    {
        $validated = $request->validate([
            'refresh' => ['nullable', 'boolean'],
        ]);

        $refresh = (bool) ($validated['refresh'] ?? true);
        return response()->json($this->buildDeliveryTimelinePayload($order, $refresh));
    }

    public function sellerDeliveryTimeline(Request $request, Order $order)
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

        $validated = $request->validate([
            'refresh' => ['nullable', 'boolean'],
        ]);

        $refresh = (bool) ($validated['refresh'] ?? true);
        return response()->json($this->buildDeliveryTimelinePayload($order, $refresh));
    }

    private function buildDeliveryTimelinePayload(Order $order, bool $refresh = true): array
    {
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
                    return ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0);
                }

                return $bTs <=> $aTs;
            })
            ->values()
            ->map(function ($event) {
                unset($event['sort_at'], $event['sequence']);
                return $event;
            });

        return [
            'order_id' => $order->id,
            'current_status' => $order->status,
            'delivery_status' => $latestStatus ?: $order->delivery_status,
            'timeline' => $sorted,
            'courier_message' => $courierMessage,
        ];
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,draft,approved,confirmed,packed,shipped,completed,cancelled,rejected'],
            'payment_status' => ['nullable', 'in:all,pending,available,paid'],
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $status = $validated['status'] ?? 'all';
        $paymentStatus = $validated['payment_status'] ?? 'all';
        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->with([
                'bulkOrderRequest:id,request_no,status,seller_id,orders_count,created_at',
                'city:id,name_en',
                'customer:id,default_name,primary_phone,additional_phone,email,notes',
                'items:id,order_id,product_id,product_variant_id,quantity,price',
                'items.product:id,title,product_code',
                'items.variant:id,sku,attributes',
                'lotItems:id,order_id,lot_id,variant_id,barcode,status',
                'lotItems.lot:id,lot_number,manufactured_at,expires_at',
                'lotItems.variant:id,sku,attributes,product_id',
                'lotItems.variant.product:id,title,product_code',
                'logs:id,order_id,user_id,event_type,from_status,to_status,note,created_at',
                'logs.user:id,name,email',
            ])
            ->latest('order_datetime')
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
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
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('bulkOrderRequest', function ($bulkQuery) use ($search) {
                        $bulkQuery->where('request_no', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);
        $orderRows = collect($orders->items());
        $this->attachComputedMetrics($orderRows, (int) ($seller->seller_level_id ?? 0));
        [$paymentSummary, $availablePayments] = $this->buildSellerPaymentSnapshot((int) $seller->id);

        return response()->json([
            'orders' => $orderRows->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'payment_summary' => $paymentSummary,
            'available_payments' => $availablePayments,
        ]);
    }

    public function sellerShow(Request $request, Order $order)
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

        $order->load([
            'city:id,name_en,district_id',
            'city.district:id,name_en',
            'customer:id,default_name,primary_phone,additional_phone,email,notes',
            'items:id,order_id,product_id,product_variant_id,quantity,price',
            'items.product:id,title,product_code',
            'items.variant:id,sku,attributes,price',
            'dispatchNoteItems:id,dispatch_note_id,order_id,waybill_snapshot,collectable_amount_snapshot,item_remarks,created_at',
            'dispatchNoteItems.dispatchNote:id,ref_no,dispatch_date,dispatch_time,status',
            'logs:id,order_id,user_id,event_type,from_status,to_status,note,created_at',
            'logs.user:id,name,email',
        ]);

        $this->attachComputedMetrics(collect([$order]), (int) ($seller->seller_level_id ?? 0));

        return response()->json([
            'order' => $order,
        ]);
    }

    public function sellerOrderLogShow(Request $request, Order $order, int $logId)
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

        $log = $order->logs()
            ->select('id', 'order_id', 'user_id', 'event_type', 'from_status', 'to_status', 'changes', 'note', 'created_at')
            ->with('user:id,name,email')
            ->where('id', $logId)
            ->first();

        if (!$log) {
            return response()->json([
                'message' => 'Order log not found.',
            ], 404);
        }

        return response()->json([
            'log' => $log,
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

    public function resolveCities(Request $request)
    {
        $validated = $request->validate([
            'names' => ['required', 'array', 'min:1', 'max:500'],
            'names.*' => ['required', 'string', 'max:255'],
        ]);

        $normalizedNames = collect($validated['names'])
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->map(fn ($name) => mb_strtolower($name))
            ->unique()
            ->values();

        if ($normalizedNames->isEmpty()) {
            return response()->json([
                'matches' => [],
            ]);
        }

        $cities = City::query()
            ->select('id', 'name_en')
            ->whereIn(DB::raw('LOWER(name_en)'), $normalizedNames->all())
            ->orderBy('name_en')
            ->get();

        $matches = [];
        foreach ($cities as $city) {
            $key = mb_strtolower(trim((string) $city->name_en));
            if (!isset($matches[$key])) {
                $matches[$key] = [
                    'id' => (int) $city->id,
                    'name_en' => (string) $city->name_en,
                ];
            }
        }

        return response()->json([
            'matches' => $matches,
        ]);
    }

    public function customers(Request $request)
    {
        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $search = trim((string) $request->query('search', ''));
        $limit = max(5, min((int) $request->query('limit', 12), 30));

        $query = Customer::query()
            ->select([
                'id',
                'default_name',
                'primary_phone',
                'additional_phone',
                'email',
                'default_address',
                'city_id',
                'notes',
                'orders_count',
                'last_order_at',
            ])
            ->with('city:id,name_en')
            ->where('seller_id', $seller->id)
            ->latest('last_order_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('default_name', 'like', '%' . $search . '%')
                    ->orWhere('primary_phone', 'like', '%' . $search . '%')
                    ->orWhere('additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        return response()->json([
            'customers' => $query->limit($limit)->get()->map(fn (Customer $customer) => [
                'id' => (int) $customer->id,
                'name' => (string) ($customer->default_name ?? ''),
                'phone' => (string) ($customer->primary_phone ?? ''),
                'additional_phone' => $customer->additional_phone,
                'email' => $customer->email,
                'address' => (string) ($customer->default_address ?? ''),
                'city_id' => $customer->city_id ? (int) $customer->city_id : null,
                'city' => $customer->city?->name_en,
                'notes' => $customer->notes,
                'orders_count' => (int) ($customer->orders_count ?? 0),
                'last_order_at' => $customer->last_order_at?->toDateTimeString(),
            ])->values(),
        ]);
    }

    public function productOptions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $limit = max(5, min((int) $request->query('limit', 20), 100));
        $sellerLevelId = (int) ($request->user()?->seller?->seller_level_id ?? 0);

        $query = Product::query()
            ->select('id', 'title', 'product_code', 'delivery_fee', 'is_free_shipping', 'has_varients')
            ->with([
                'images' => function ($q) {
                    $q->select('id', 'product_id', 'path', 'is_primary')
                        ->orderByDesc('is_primary')
                        ->orderBy('id');
                },
                'varients' => function ($q) {
                    $q->select('id', 'product_id', 'sku', 'attributes', 'price', 'is_active')
                        ->where('is_active', true)
                        ->orderBy('id');
                },
                'productLevels' => function ($q) use ($sellerLevelId) {
                    $q->select('id', 'product_id', 'level_id', 'type', 'value')
                        ->where('level_id', $sellerLevelId);
                },
            ])
            ->where('is_active', true)
            ->whereHas('varients', function ($q) {
                $q->where('is_active', true);
            })
            ->orderBy('title');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('product_code', 'like', '%' . $search . '%');
            });
        }

        $products = $query->limit($limit)->get()->map(function (Product $product) {
            $image = $product->images->first();

            return [
                'id' => (int) $product->id,
                'title' => (string) $product->title,
                'product_code' => (string) ($product->product_code ?? ''),
                'image' => $image ? asset('storage/' . ltrim((string) $image->path, '/')) : null,
                'delivery_fee' => (float) ($product->delivery_fee ?? 0),
                'is_free_shipping' => (bool) ($product->is_free_shipping ?? false),
                'has_varients' => (bool) ($product->has_varients ?? false),
                'commission_rule' => $product->productLevels->first() ? [
                    'type' => (string) $product->productLevels->first()->type,
                    'value' => (float) $product->productLevels->first()->value,
                ] : null,
                'variants' => $product->varients->map(function ($variant) {
                    return [
                        'id' => (int) $variant->id,
                        'sku' => (string) ($variant->sku ?? ''),
                        'attributes' => is_array($variant->attributes) ? $variant->attributes : [],
                        'price' => (float) ($variant->price ?? 0),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_datetime' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,approved,confirmed,packed,shipped,completed,cancelled,rejected'],
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

        $order = $this->createSellerOrder($seller, $validated, null);

        return response()->json([
            'message' => 'Order submitted successfully.',
            'order_id' => $order->id,
            'status' => $order->status,
        ], 201);
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'orders' => ['required', 'array', 'min:1', 'max:300'],

            'orders.*.order_datetime' => ['nullable', 'date'],
            'orders.*.status' => ['nullable', 'in:draft,approved,confirmed,packed,shipped,completed,cancelled,rejected'],
            'orders.*.delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'orders.*.total_discount' => ['nullable', 'numeric', 'min:0'],
            'orders.*.commission_amount' => ['nullable', 'numeric', 'min:0'],

            'orders.*.customer' => ['required', 'array'],
            'orders.*.customer.name' => ['required', 'string', 'max:255'],
            'orders.*.customer.phone' => ['required', 'string', 'max:30'],
            'orders.*.customer.additional_phone' => ['nullable', 'string', 'max:30'],
            'orders.*.customer.email' => ['nullable', 'email', 'max:255'],
            'orders.*.customer.address' => ['required', 'string', 'max:5000'],
            'orders.*.customer.city_id' => ['required', 'integer', 'exists:cities,id'],
            'orders.*.customer.city' => ['nullable', 'string', 'max:255'],
            'orders.*.customer.notes' => ['nullable', 'string', 'max:2000'],

            'orders.*.items' => ['required', 'array', 'min:1'],
            'orders.*.items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'orders.*.items.*.product_variant_id' => ['nullable', 'integer', 'exists:varients,id'],
            'orders.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'orders.*.items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $seller = $request->user()?->seller;
        if (!$seller) {
            return response()->json([
                'message' => 'Seller profile not found for this user.',
            ], 422);
        }

        $bulkOrderRequest = null;
        $createdOrders = DB::transaction(function () use ($seller, $validated, &$bulkOrderRequest) {
            $bulkOrderRequest = BulkOrderRequest::create([
                'request_no' => $this->generateBulkRequestNo(),
                'seller_id' => (int) $seller->id,
                'status' => 'draft',
                'orders_count' => 0,
            ]);

            $orders = collect($validated['orders'])
                ->map(fn (array $orderPayload) => $this->createSellerOrder($seller, $orderPayload, (int) $bulkOrderRequest->id))
                ->values();

            $bulkOrderRequest->update([
                'orders_count' => $orders->count(),
            ]);

            return $orders;
        });

        return response()->json([
            'message' => $createdOrders->count() . ' orders submitted successfully.',
            'created_count' => $createdOrders->count(),
            'order_ids' => $createdOrders->pluck('id')->values(),
            'bulk_order_request' => [
                'id' => $bulkOrderRequest?->id,
                'request_no' => $bulkOrderRequest?->request_no,
                'status' => $bulkOrderRequest?->status,
            ],
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

    public function adminReject(Request $request, Order $order)
    {
        if (!$this->isDraftOrder($order)) {
            return response()->json([
                'message' => 'Only draft orders can be rejected.',
            ], 422);
        }

        $order->update([
            'status' => 'rejected',
            'is_draft' => false,
        ]);

        return response()->json([
            'message' => 'Order rejected successfully.',
            'order_id' => $order->id,
        ]);
    }

    public function adminUpdateDraftOrderCity(Request $request, Order $order)
    {
        $validated = $request->validate([
            'city_id' => ['required', 'integer', 'exists:cities,id'],
        ]);

        if (!$this->isDraftOrder($order)) {
            return response()->json([
                'message' => 'Only draft orders can be updated.',
            ], 422);
        }

        $order->update([
            'city_id' => (int) $validated['city_id'],
        ]);

        $order->load('city:id,name_en');

        return response()->json([
            'message' => 'Order city updated successfully.',
            'order' => [
                'id' => (int) $order->id,
                'city_id' => $order->city_id ? (int) $order->city_id : null,
                'city' => $order->city,
            ],
        ]);
    }

    public function adminCancelApproved(Request $request, Order $order)
    {
        $order = DB::transaction(function () use ($order) {
            $fresh = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($fresh->status !== 'approved' || $fresh->is_draft) {
                abort(422, 'Only approved orders can be cancelled from this list.');
            }

            $fresh->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'delivery_status' => $fresh->delivery_status ?: 'Manually Cancelled',
                'payment_status' => 'pending',
            ]);

            return $fresh;
        });

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order_id' => $order->id,
            'status' => $order->status,
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

    private function createSellerOrder(Seller $seller, array $payload, ?int $bulkOrderRequestId = null): Order
    {
        $customerData = $payload['customer'];
        $phone = $this->normalizePhone((string) ($customerData['phone'] ?? ''));
        $customerKey = 'phone:' . $phone;

        $cityId = !empty($customerData['city_id']) ? (int) $customerData['city_id'] : null;
        if (!$cityId && !empty($customerData['city'])) {
            $cityName = trim((string) $customerData['city']);
            $cityId = City::query()
                ->whereRaw('LOWER(name_en) = ?', [mb_strtolower($cityName)])
                ->value('id');
        }

        $items = collect($payload['items']);
        $netTotal = (float) $items->sum(fn ($item) => (float) $item['price'] * (int) $item['quantity']);
        $productIds = $items
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $deliveryCharge = 0.0;
        if ($productIds->isNotEmpty()) {
            $deliveryCharge = (float) Product::query()
                ->whereIn('id', $productIds->all())
                ->selectRaw('MAX(CASE WHEN is_free_shipping = 1 THEN 0 ELSE COALESCE(delivery_fee, 0) END) as max_delivery_fee')
                ->value('max_delivery_fee');
        }

        $totalDiscount = (float) ($payload['total_discount'] ?? 0);
        $commissionAmount = $this->calculateCommissionForItems(
            $items,
            (int) ($seller->seller_level_id ?? 0)
        );
        $collectable = $netTotal + $deliveryCharge - $totalDiscount;

        $customer = Customer::updateOrCreate(
            [
                'seller_id' => $seller->id,
                'customer_key' => $customerKey,
            ],
            [
                'primary_phone' => $phone,
                'additional_phone' => $this->nullableString($customerData['additional_phone'] ?? null),
                'email' => $this->nullableString($customerData['email'] ?? null),
                'default_name' => (string) ($customerData['name'] ?? ''),
                'default_address' => (string) ($customerData['address'] ?? ''),
                'city_id' => $cityId,
                'notes' => $this->nullableString($customerData['notes'] ?? null),
                'status' => 'active',
                'last_order_at' => now(),
            ]
        );

        $customer->increment('orders_count');

        $status = (string) ($payload['status'] ?? 'draft');

        $order = Order::create([
            'order_datetime' => $payload['order_datetime'] ?? now(),
            'status' => $status,
            'is_draft' => $status === 'draft',
            'net_total' => $netTotal,
            'total_collectable_amount' => $collectable,
            'delivery_charge' => $deliveryCharge,
            'total_discount' => $totalDiscount,
            'commission_amount' => $commissionAmount,
            'bulk_order_request_id' => $bulkOrderRequestId,
            'seller_id' => $seller->id,
            'customer_id' => $customer->id,
            'customer_name' => (string) ($customerData['name'] ?? ''),
            'address' => (string) ($customerData['address'] ?? ''),
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
    }

    private function generateBulkRequestNo(): string
    {
        do {
            $candidate = 'BOR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
            $exists = BulkOrderRequest::query()->where('request_no', $candidate)->exists();
        } while ($exists);

        return $candidate;
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

    private function attachComputedMetrics(Collection $orders, int $sellerLevelId): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            $computed = $this->calculateCommissionForItems(
                collect($order->items ?? []),
                $sellerLevelId
            );

            $order->setAttribute('computed_commission_amount', $computed);
            $order->setAttribute('computed_points_earned', $this->calculatePointsForOrder($order));
            $paymentStatus = strtolower(trim((string) ($order->payment_status ?? '')));
            $order->setAttribute('payment_status', in_array($paymentStatus, ['pending', 'available', 'paid'], true) ? $paymentStatus : 'pending');
        }
    }

    private function buildSellerPaymentSnapshot(int $sellerId): array
    {
        $pending = (float) Order::query()
            ->where('seller_id', $sellerId)
            ->where('payment_status', 'pending')
            ->sum('total_collectable_amount');

        $available = (float) Payment::query()
            ->where('seller_id', $sellerId)
            ->where('status', 'available')
            ->sum('amount');

        $paid = (float) Payment::query()
            ->where('seller_id', $sellerId)
            ->where('status', 'paid')
            ->sum('amount');

        $availablePayments = Payment::query()
            ->where('seller_id', $sellerId)
            ->where('status', 'available')
            ->with([
                'order:id,customer_name,phone,waybill_no,total_collectable_amount,payment_status,completed_at',
            ])
            ->latest('available_at')
            ->latest('id')
            ->limit(10)
            ->get();

        return [[
            'pending' => round($pending, 2),
            'available' => round($available, 2),
            'paid' => round($paid, 2),
        ], $availablePayments];
    }

    private function calculateCommissionForItems(Collection $items, int $sellerLevelId): float
    {
        if ($sellerLevelId <= 0 || $items->isEmpty()) {
            return 0.0;
        }

        $productIds = $items
            ->map(fn ($item) => (int) data_get($item, 'product_id'))
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return 0.0;
        }

        $levelRules = ProductLevel::query()
            ->where('level_id', $sellerLevelId)
            ->whereIn('product_id', $productIds->all())
            ->get(['product_id', 'type', 'value'])
            ->keyBy('product_id');

        if ($levelRules->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($items as $item) {
            $productId = (int) data_get($item, 'product_id');
            $qty = max(0, (int) data_get($item, 'quantity', 0));
            $price = (float) data_get($item, 'price', 0);

            if ($productId <= 0 || $qty <= 0 || $price < 0) {
                continue;
            }

            $rule = $levelRules->get($productId);
            if (!$rule) {
                continue;
            }

            if ($rule->type === 'percentage') {
                $total += ($price * ((float) $rule->value / 100)) * $qty;
            } else {
                $total += ((float) $rule->value) * $qty;
            }
        }

        return round($total, 2);
    }

    private function calculatePointsForOrder($order): int
    {
        $lkrPerPoint = (float) config('seller.lkr_per_point', 100);
        if ($lkrPerPoint <= 0) {
            $lkrPerPoint = 100;
        }

        $baseAmount = max(0, (float) ($order->net_total ?? 0) - (float) ($order->total_discount ?? 0));

        return (int) floor($baseAmount / $lkrPerPoint);
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
