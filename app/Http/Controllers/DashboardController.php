<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getAdminDashboard(){


        return view('dashboards.admin.dashboard');
    }

    public function getSellerDashboard()
    {
        return view('dashboards.seller.dashboard');
    }

    public function getSellerProducts()
    {
        $products = Product::query()
            ->with([
                'category:id,name',
                'images:id,product_id,path,is_primary',
                'varients:id,product_id,price,is_active',
                'productLevels:id,product_id,level_id,type,value',
            ])
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('dashboards.seller.products', [
            'products' => $products,
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

    public function getSellerCheckout()
    {
        return view('dashboards.seller.checkout');
    }

    public function getRoyalExpress()
    {
        return view('dashboards.admin.settings.royalExpress');
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
