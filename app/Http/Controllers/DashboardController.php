<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Level;
use App\Models\Category;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getAdminDashboard(){


        return view('dashboards.admin.dashboard');
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
