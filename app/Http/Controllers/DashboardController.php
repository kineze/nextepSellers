<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Level;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getAdminDashboard(){


        return view('dashboards.admin.dashboard');
    }

    public function adminAnalytics()
    {
        $today = Carbon::today();
        $from = (clone $today)->subDays(13)->startOfDay();
        $to = (clone $today)->endOfDay();

        $ordersBase = Order::query();
        $totals = [
            'orders_total' => (int) (clone $ordersBase)->count(),
            'completed_orders' => (int) (clone $ordersBase)->where('status', 'completed')->count(),
            'shipped_orders' => (int) (clone $ordersBase)->where('status', 'shipped')->count(),
            'cancelled_orders' => (int) (clone $ordersBase)->where('status', 'cancelled')->count(),
            'net_sales_total' => round((float) (clone $ordersBase)
                ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as total')
                ->value('total'), 2),
            'commission_total' => round((float) (clone $ordersBase)->sum('commission_amount'), 2),
        ];

        $payments = [
            'pending_commission' => round((float) Order::query()->where('payment_status', 'pending')->sum('commission_amount'), 2),
            'available_commission' => round((float) Order::query()->where('payment_status', 'available')->sum('commission_amount'), 2),
            'paid_commission' => round((float) Order::query()->where('payment_status', 'paid')->sum('commission_amount'), 2),
        ];

        $sellers = [
            'total' => (int) Seller::query()->count(),
            'approved' => (int) Seller::query()->where('status', 'approved')->count(),
            'pending' => (int) Seller::query()->where('status', 'pending')->count(),
            'blocked' => (int) Seller::query()->where('status', 'blocked')->count(),
            'rejected' => (int) Seller::query()->where('status', 'rejected')->count(),
        ];

        $invoices = [
            'total' => (int) Invoice::query()->count(),
            'draft' => (int) Invoice::query()->where('status', 'draft')->count(),
            'paid' => (int) Invoice::query()->where('status', 'paid')->count(),
            'cancelled' => (int) Invoice::query()->where('status', 'cancelled')->count(),
            'total_commission' => round((float) Invoice::query()->sum('total_commission_value'), 2),
        ];

        $trendRows = Order::query()
            ->selectRaw('DATE(order_datetime) as order_date')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as net_sales')
            ->selectRaw('COALESCE(SUM(commission_amount), 0) as commission')
            ->whereBetween('order_datetime', [$from, $to])
            ->groupBy(DB::raw('DATE(order_datetime)'))
            ->orderBy('order_date')
            ->get()
            ->keyBy('order_date');

        $trend = collect(range(0, 13))->map(function ($index) use ($today, $trendRows) {
            $date = (clone $today)->subDays(13 - $index)->toDateString();
            $row = $trendRows->get($date);

            return [
                'date' => $date,
                'orders_count' => (int) ($row->orders_count ?? 0),
                'completed_count' => (int) ($row->completed_count ?? 0),
                'net_sales' => round((float) ($row->net_sales ?? 0), 2),
                'commission' => round((float) ($row->commission ?? 0), 2),
            ];
        })->values();

        $topSellers = Seller::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->withCount([
                'orders as completed_orders_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->withSum('orders as total_commission_value', 'commission_amount')
            ->orderByDesc('total_commission_value')
            ->limit(8)
            ->get()
            ->map(function (Seller $seller) {
                return [
                    'id' => (int) $seller->id,
                    'first_name' => $seller->first_name,
                    'last_name' => $seller->last_name,
                    'email' => $seller->email,
                    'completed_orders_count' => (int) ($seller->completed_orders_count ?? 0),
                    'total_commission_value' => round((float) ($seller->total_commission_value ?? 0), 2),
                ];
            })
            ->values();

        return response()->json([
            'totals' => $totals,
            'payments' => $payments,
            'sellers' => $sellers,
            'invoices' => $invoices,
            'trend' => $trend,
            'top_sellers' => $topSellers,
        ]);
    }

    public function getSellerDashboard()
    {
        $seller = auth()->user()?->seller?->loadMissing('level');

        $currentPoints = (int) ($seller?->points ?? 0);
        $level = $seller?->level;
        $nextLevel = null;

        if ($level) {
            $nextLevel = Level::query()
                ->where('points', '>', (int) ($level->points ?? 0))
                ->orderBy('points')
                ->first(['id', 'level_no', 'level_name', 'points']);
        } else {
            $nextLevel = Level::query()
                ->orderBy('points')
                ->first(['id', 'level_no', 'level_name', 'points']);
        }

        $lkrPerPoint = (float) config('seller.lkr_per_point', 100);
        if ($lkrPerPoint <= 0) {
            $lkrPerPoint = 100;
        }

        $pendingBaseAmount = 0.0;
        if ($seller) {
            $pendingBaseAmount = (float) Order::query()
                ->where('seller_id', $seller->id)
                ->whereIn('status', ['draft', 'approved', 'confirmed', 'packed', 'shipped'])
                ->selectRaw('COALESCE(SUM(GREATEST(net_total - total_discount, 0)), 0) as pending_amount')
                ->value('pending_amount');
        }

        $pendingPoints = (int) floor($pendingBaseAmount / $lkrPerPoint);
        $projectedPoints = $currentPoints + $pendingPoints;

        $pointsToNextLevel = null;
        if ($nextLevel) {
            $pointsToNextLevel = max(0, (int) $nextLevel->points - $projectedPoints);
        }

        return view('dashboards.seller.dashboard', [
            'sellerStats' => [
                'level_name' => $level?->level_name,
                'level_no' => $level?->level_no,
                'current_points' => $currentPoints,
                'pending_points' => $pendingPoints,
                'projected_points' => $projectedPoints,
                'next_level_name' => $nextLevel?->level_name,
                'next_level_no' => $nextLevel?->level_no,
                'next_level_points' => $nextLevel?->points,
                'points_to_next_level' => $pointsToNextLevel,
                'lkr_per_point' => $lkrPerPoint,
            ],
        ]);
    }

    public function getSellerProducts(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;

        $productQuery = Product::query()
            ->with([
                'category:id,name',
                'images:id,product_id,path,is_primary',
                'varients:id,product_id,price,is_active',
                'productLevels:id,product_id,level_id,type,value',
            ])
            ->where('is_active', true);

        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }

        $products = $productQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->select('categories.id', 'categories.name')
            ->join('products', 'products.category_id', '=', 'categories.id')
            ->where('categories.is_active', true)
            ->where('products.is_active', true)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();

        return view('dashboards.seller.products', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
        ]);
    }

    public function getSellerProductShow(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        return view('dashboards.seller.product-show', [
            'productId' => $product->id,
        ]);
    }

    public function getSellerProductData(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load([
            'category:id,name',
            'images:id,product_id,path,is_primary',
            'varients:id,product_id,sku,attributes,price,stock_quantity,reorder_level,is_active',
            'productLevels.level:id,level_no,level_name,points',
        ]);

        $seller = auth()->user()?->seller;

        return response()->json([
            'product' => $product,
            'seller' => [
                'seller_level_id' => $seller?->seller_level_id,
                'points' => (int) ($seller?->points ?? 0),
            ],
        ]);
    }

    public function getSellerInventory()
    {
        return redirect()->route('sellerProducts');
    }

    public function getSellerOrders()
    {
        return view('dashboards.seller.orders');
    }

    public function getSellerPayments()
    {
        return view('dashboards.seller.payments');
    }

    public function getSellerProfileManager()
    {
        return view('dashboards.seller.profile');
    }

    public function getSellerOrderShow(Order $order)
    {
        $seller = auth()->user()?->seller;
        if (!$seller || (int) $order->seller_id !== (int) $seller->id) {
            abort(404);
        }

        return view('dashboards.seller.order-show', [
            'orderId' => $order->id,
        ]);
    }

    public function getSellerCheckout()
    {
        return view('dashboards.seller.checkout');
    }

    public function getRoyalExpress()
    {
        return view('dashboards.admin.settings.royalExpress');
    }

    public function getDeliveryFees()
    {
        return view('dashboards.admin.settings.deliveryFees');
    }

    public function getAdminDraftOrders()
    {
        return view('dashboards.admin.orders.draftOrders');
    }

    public function getAdminApprovedOrders()
    {
        return view('dashboards.admin.orders.approvedOrders');
    }

    public function getAdminPackedOrders()
    {
        return view('dashboards.admin.orders.packedOrders');
    }

    public function getAdminShippedOrders()
    {
        return view('dashboards.admin.orders.shippedOrders');
    }

    public function getAdminCompletedOrders()
    {
        return view('dashboards.admin.orders.completedOrders');
    }

    public function getAdminCancelledOrders()
    {
        return view('dashboards.admin.orders.cancelledOrders');
    }

    public function getAdminDispatchNotes()
    {
        return view('dashboards.admin.orders.dispatchNotes');
    }

    public function getAdminDispatchNoteShow($dispatchNote)
    {
        return view('dashboards.admin.orders.dispatchNoteShow', [
            'dispatchNoteId' => (int) $dispatchNote,
        ]);
    }

    public function getAdminFinancePendingPayments()
    {
        return view('dashboards.admin.finance.pendingPayments');
    }

    public function getAdminFinanceAvailablePayments()
    {
        return view('dashboards.admin.finance.availablePayments');
    }

    public function getAdminFinanceInvoices()
    {
        return view('dashboards.admin.finance.invoices');
    }

    public function getAdminFinancePaymentManager()
    {
        return view('dashboards.admin.finance.paymentManager');
    }

    public function getAdminOrderShow(Order $order)
    {
        return view('dashboards.admin.orders.showOrder', [
            'orderId' => $order->id,
        ]);
    }

    public function getGrns()
    {
        return view('dashboards.admin.settings.grns');
    }

    public function getLots()
    {
        return view('dashboards.admin.settings.lots');
    }
}
