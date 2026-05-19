<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    private const ORDER_STATUSES = [
        'draft',
        'approved',
        'confirmed',
        'packed',
        'shipped',
        'completed',
        'cancelled',
        'rejected',
    ];

    public function orderCount(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
        ]);

        $query = Order::query();

        if (!empty($validated['date_from'])) {
            $query->whereDate('order_datetime', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('order_datetime', '<=', $validated['date_to']);
        }

        if (!empty($validated['seller_id'])) {
            $query->where('seller_id', (int) $validated['seller_id']);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('waybill_no', 'like', '%' . $search . '%')
                    ->orWhereHas('seller', function ($sellerQuery) use ($search) {
                        $sellerQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    });
            });
        }

        $counts = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = collect(self::ORDER_STATUSES)
            ->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();

        $completed = $statusCounts['completed'];
        $unsuccessful = $statusCounts['cancelled'] + $statusCounts['rejected'];
        $inTransit = $statusCounts['shipped'];
        $deliveryTotal = $completed + $unsuccessful;

        return response()->json([
            'total_orders' => array_sum($statusCounts),
            'status_counts' => $statusCounts,
            'delivery' => [
                'completed' => $completed,
                'unsuccessful' => $unsuccessful,
                'in_transit' => $inTransit,
                'success_ratio' => $deliveryTotal > 0 ? round(($completed / $deliveryTotal) * 100, 1) : 0,
            ],
        ]);
    }

    public function sellerBreakdown(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $offset = (int) ($validated['offset'] ?? 0);

        $query = Order::query()
            ->leftJoin('sellers', 'orders.seller_id', '=', 'sellers.id')
            ->leftJoin('business_informations', 'business_informations.seller_id', '=', 'sellers.id');

        if (!empty($validated['date_from'])) {
            $query->whereDate('orders.order_datetime', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('orders.order_datetime', '<=', $validated['date_to']);
        }

        if (!empty($validated['seller_id'])) {
            $query->where('orders.seller_id', (int) $validated['seller_id']);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('orders.id', $search)
                    ->orWhere('orders.customer_name', 'like', '%' . $search . '%')
                    ->orWhere('orders.phone', 'like', '%' . $search . '%')
                    ->orWhere('orders.additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('orders.waybill_no', 'like', '%' . $search . '%')
                    ->orWhere('sellers.first_name', 'like', '%' . $search . '%')
                    ->orWhere('sellers.last_name', 'like', '%' . $search . '%')
                    ->orWhere('sellers.email', 'like', '%' . $search . '%')
                    ->orWhere('sellers.phone', 'like', '%' . $search . '%')
                    ->orWhere('business_informations.business_name', 'like', '%' . $search . '%');
            });
        }

        $statusSelects = collect(self::ORDER_STATUSES)
            ->map(fn ($status) => "SUM(CASE WHEN orders.status = '{$status}' THEN 1 ELSE 0 END) as {$status}_orders")
            ->implode(', ');

        $summary = (clone $query)
            ->selectRaw('
                COUNT(*) as total_orders,
                COUNT(DISTINCT orders.seller_id) as seller_count,
                SUM(orders.net_total) as net_total,
                SUM(orders.total_collectable_amount) as collectable_total,
                SUM(orders.commission_amount) as commission_total
            ')
            ->first();

        $statusTotals = collect(self::ORDER_STATUSES)
            ->mapWithKeys(fn ($status) => [$status => 0])
            ->merge(
                (clone $query)
                    ->select('orders.status', DB::raw('COUNT(*) as total'))
                    ->groupBy('orders.status')
                    ->pluck('total', 'status')
                    ->map(fn ($total) => (int) $total)
                    ->all()
            )
            ->only(self::ORDER_STATUSES)
            ->all();

        $rowsQuery = (clone $query)
            ->selectRaw("
                orders.seller_id,
                sellers.first_name,
                sellers.last_name,
                sellers.email,
                sellers.phone,
                sellers.status as seller_status,
                business_informations.business_name,
                COUNT(*) as total_orders,
                {$statusSelects},
                SUM(orders.net_total) as net_total,
                SUM(orders.total_collectable_amount) as collectable_total,
                SUM(orders.commission_amount) as commission_total
            ")
            ->groupBy(
                'orders.seller_id',
                'sellers.first_name',
                'sellers.last_name',
                'sellers.email',
                'sellers.phone',
                'sellers.status',
                'business_informations.business_name'
            )
            ->orderByDesc('total_orders')
            ->orderBy('sellers.first_name');

        $mapSeller = function ($row, int $index) use ($offset) {
            $statusCounts = collect(self::ORDER_STATUSES)
                ->mapWithKeys(fn ($status) => [$status => (int) ($row->{$status . '_orders'} ?? 0)])
                ->all();

            $name = trim((string) $row->first_name . ' ' . (string) $row->last_name);

            return [
                'rank' => $offset + $index + 1,
                'seller_id' => (int) $row->seller_id,
                'seller_name' => $name !== '' ? $name : ((string) $row->email ?: 'Unknown seller'),
                'business_name' => $row->business_name,
                'email' => $row->email,
                'phone' => $row->phone,
                'seller_status' => $row->seller_status,
                'status_counts' => $statusCounts,
                'total_orders' => (int) $row->total_orders,
                'net_total' => round((float) $row->net_total, 2),
                'collectable_total' => round((float) $row->collectable_total, 2),
                'commission_total' => round((float) $row->commission_total, 2),
            ];
        };

        $sellers = (clone $rowsQuery)
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map($mapSeller)
            ->values();

        $chartSellers = (clone $rowsQuery)
            ->limit(10)
            ->get()
            ->map(function ($row, int $index) use ($mapSeller) {
                $seller = $mapSeller($row, $index);
                $seller['rank'] = $index + 1;
                return $seller;
            })
            ->values();

        $totalOrders = (int) ($summary->total_orders ?? 0);
        $sellerCount = (int) ($summary->seller_count ?? 0);
        $completed = (int) ($statusTotals['completed'] ?? 0);
        $unsuccessful = (int) (($statusTotals['cancelled'] ?? 0) + ($statusTotals['rejected'] ?? 0));
        $deliveryTotal = $completed + $unsuccessful;

        return response()->json([
            'statuses' => self::ORDER_STATUSES,
            'total_orders' => $totalOrders,
            'seller_count' => $sellerCount,
            'status_totals' => $statusTotals,
            'money_totals' => [
                'net_total' => round((float) ($summary->net_total ?? 0), 2),
                'collectable_total' => round((float) ($summary->collectable_total ?? 0), 2),
                'commission_total' => round((float) ($summary->commission_total ?? 0), 2),
            ],
            'delivery' => [
                'completed' => $completed,
                'unsuccessful' => $unsuccessful,
                'success_ratio' => $deliveryTotal > 0 ? round(($completed / $deliveryTotal) * 100, 1) : 0,
            ],
            'pagination' => [
                'per_page' => $perPage,
                'offset' => $offset,
                'returned' => $sellers->count(),
                'has_more' => ($offset + $sellers->count()) < $sellerCount,
            ],
            'chart_sellers' => $chartSellers,
            'sellers' => $sellers,
        ]);
    }

    public function productBreakdown(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'seller_id' => ['nullable', 'integer', 'exists:sellers,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $offset = (int) ($validated['offset'] ?? 0);

        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('sellers', 'orders.seller_id', '=', 'sellers.id')
            ->leftJoin('business_informations', 'business_informations.seller_id', '=', 'sellers.id');

        if (!empty($validated['date_from'])) {
            $query->whereDate('orders.order_datetime', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('orders.order_datetime', '<=', $validated['date_to']);
        }

        if (!empty($validated['seller_id'])) {
            $query->where('orders.seller_id', (int) $validated['seller_id']);
        }

        $search = trim((string) ($validated['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('orders.id', $search)
                    ->orWhere('orders.customer_name', 'like', '%' . $search . '%')
                    ->orWhere('orders.phone', 'like', '%' . $search . '%')
                    ->orWhere('orders.additional_phone', 'like', '%' . $search . '%')
                    ->orWhere('orders.waybill_no', 'like', '%' . $search . '%')
                    ->orWhere('products.title', 'like', '%' . $search . '%')
                    ->orWhere('products.product_code', 'like', '%' . $search . '%')
                    ->orWhere('sellers.first_name', 'like', '%' . $search . '%')
                    ->orWhere('sellers.last_name', 'like', '%' . $search . '%')
                    ->orWhere('sellers.email', 'like', '%' . $search . '%')
                    ->orWhere('sellers.phone', 'like', '%' . $search . '%')
                    ->orWhere('business_informations.business_name', 'like', '%' . $search . '%');
            });
        }

        $statusSelects = collect(self::ORDER_STATUSES)
            ->map(fn ($status) => "COUNT(DISTINCT CASE WHEN orders.status = '{$status}' THEN orders.id END) as {$status}_orders")
            ->implode(', ');

        $summary = (clone $query)
            ->selectRaw('
                COUNT(DISTINCT order_items.product_id) as product_count,
                SUM(order_items.quantity) as quantity_total,
                SUM(order_items.quantity * order_items.price) as product_total
            ')
            ->first();

        $statusRows = (clone $query)
            ->selectRaw('
                order_items.product_id,
                orders.status,
                COUNT(DISTINCT orders.id) as total
            ')
            ->groupBy('order_items.product_id', 'orders.status')
            ->get();

        $statusTotals = collect(self::ORDER_STATUSES)
            ->mapWithKeys(fn ($status) => [
                $status => (int) $statusRows
                    ->where('status', $status)
                    ->sum(fn ($row) => (int) $row->total),
            ])
            ->all();

        $rowsQuery = (clone $query)
            ->selectRaw("
                order_items.product_id,
                products.title,
                products.product_code,
                products.is_active,
                COUNT(DISTINCT orders.id) as total_orders,
                {$statusSelects},
                SUM(order_items.quantity) as total_quantity,
                SUM(order_items.quantity * order_items.price) as product_total
            ")
            ->groupBy(
                'order_items.product_id',
                'products.title',
                'products.product_code',
                'products.is_active'
            )
            ->orderByDesc('total_orders')
            ->orderBy('products.title');

        $mapProduct = function ($row, int $index) use ($offset) {
            $statusCounts = collect(self::ORDER_STATUSES)
                ->mapWithKeys(fn ($status) => [$status => (int) ($row->{$status . '_orders'} ?? 0)])
                ->all();

            return [
                'rank' => $offset + $index + 1,
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->title,
                'product_code' => $row->product_code,
                'is_active' => (bool) $row->is_active,
                'status_counts' => $statusCounts,
                'total_orders' => (int) $row->total_orders,
                'total_quantity' => (int) $row->total_quantity,
                'product_total' => round((float) $row->product_total, 2),
            ];
        };

        $products = (clone $rowsQuery)
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map($mapProduct)
            ->values();

        $chartProducts = (clone $rowsQuery)
            ->limit(10)
            ->get()
            ->map(function ($row, int $index) use ($mapProduct) {
                $product = $mapProduct($row, $index);
                $product['rank'] = $index + 1;
                return $product;
            })
            ->values();

        $completed = (int) ($statusTotals['completed'] ?? 0);
        $unsuccessful = (int) (($statusTotals['cancelled'] ?? 0) + ($statusTotals['rejected'] ?? 0));
        $deliveryTotal = $completed + $unsuccessful;
        $productCount = (int) ($summary->product_count ?? 0);

        return response()->json([
            'statuses' => self::ORDER_STATUSES,
            'total_orders' => (int) array_sum($statusTotals),
            'product_count' => $productCount,
            'status_totals' => $statusTotals,
            'quantity_total' => (int) ($summary->quantity_total ?? 0),
            'product_total' => round((float) ($summary->product_total ?? 0), 2),
            'delivery' => [
                'completed' => $completed,
                'unsuccessful' => $unsuccessful,
                'success_ratio' => $deliveryTotal > 0 ? round(($completed / $deliveryTotal) * 100, 1) : 0,
            ],
            'pagination' => [
                'per_page' => $perPage,
                'offset' => $offset,
                'returned' => $products->count(),
                'has_more' => ($offset + $products->count()) < $productCount,
            ],
            'chart_products' => $chartProducts,
            'products' => $products,
        ]);
    }
}
