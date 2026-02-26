<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\RoyalExpressLoginController;
use App\Http\Controllers\SellerOrderController;
use App\Http\Controllers\DispatchNoteController;
use App\Http\Controllers\DeliveryFeeController;

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {

    Route::get('/roles', [RolePermissionController::class, 'index']);
    Route::post('/roles', [RolePermissionController::class, 'store']);
    Route::get('/roles/{role}', [RolePermissionController::class, 'show']);
    Route::put('/roles/{role}', [RolePermissionController::class, 'update']);
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy']);
    Route::get('/permissions', [RolePermissionController::class, 'permissions']);
    Route::post('/roles/{role}/sync', [RolePermissionController::class, 'syncPermissions']);

});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/role', [UserController::class, 'assignRole']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/users/{user}/block', [UserController::class, 'block']);
    Route::post('/users/{user}/unblock', [UserController::class, 'unblock']);
});

Route::middleware(['auth:sanctum', 'permission:Manage Sellers'])->group(function () {
    Route::get('/sellers', [SellerController::class, 'index']);
    Route::get('/sellers/approval-options', [SellerController::class, 'approvalOptions']);
    Route::get('/sellers/{seller}', [SellerController::class, 'show']);
    Route::post('/sellers/{seller}/image', [SellerController::class, 'uploadImage']);
    Route::post('/sellers/{seller}/block', [SellerController::class, 'block']);
    Route::get('/active-sellers', [SellerController::class, 'activeIndex']);
    Route::post('/active-sellers/{seller}/block', [SellerController::class, 'blockActiveSeller']);
    Route::post('/sellers/{seller}/unblock', [SellerController::class, 'unblock']);
    Route::post('/sellers/{seller}/approve', [SellerController::class, 'approve']);
    Route::post('/sellers/{seller}/reject', [SellerController::class, 'reject']);
});

Route::middleware(['auth:sanctum', 'permission:Manage Levels'])->group(function () {
    Route::get('/levels', [LevelController::class, 'index']);
    Route::post('/levels', [LevelController::class, 'store']);
    Route::put('/levels/{level}', [LevelController::class, 'update']);
    Route::delete('/levels/{level}', [LevelController::class, 'destroy']);
    Route::post('/levels/{level}/toggle-default', [LevelController::class, 'toggleDefault']);
});

Route::middleware(['auth:sanctum', 'permission:Manage System Configuration'])->group(function () {
    Route::get('/attributes', [AttributeController::class, 'index']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);

    Route::get('/delivery-fees', [DeliveryFeeController::class, 'index']);
    Route::post('/delivery-fees', [DeliveryFeeController::class, 'store']);
    Route::put('/delivery-fees/{deliveryFee}', [DeliveryFeeController::class, 'update']);
    Route::delete('/delivery-fees/{deliveryFee}', [DeliveryFeeController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permission:Manage Inventory'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/create-options', [ProductController::class, 'createOptions']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products/upload-image', [ProductController::class, 'uploadImage']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::post('/products/{product}/toggle-active', [ProductController::class, 'toggleActive']);

    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::get('/suppliers/product-options', [SupplierController::class, 'productOptions']);
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy']);
    Route::post('/suppliers/{supplier}/link-product', [SupplierController::class, 'linkProduct']);
    Route::delete('/suppliers/{supplier}/linked-products/{supplierProduct}', [SupplierController::class, 'unlinkProduct']);

    Route::get('/categories/tree', [CategoryController::class, 'tree']);
    Route::get('/categories/options', [CategoryController::class, 'options']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/grns/options', [GrnController::class, 'options']);
    Route::get('/grns', [GrnController::class, 'index']);
    Route::post('/grns', [GrnController::class, 'store']);
    Route::get('/grns/{grn}', [GrnController::class, 'show']);
    Route::put('/grns/{grn}', [GrnController::class, 'update']);
    Route::delete('/grns/{grn}', [GrnController::class, 'destroy']);
    Route::post('/grns/{grn}/post', [GrnController::class, 'post']);
    Route::post('/grns/{grn}/unpost', [GrnController::class, 'unpost']);

    Route::get('/lots/filter', [LotController::class, 'filterLots']);
    Route::get('/lots/{lot}/items', [LotController::class, 'items']);
    Route::post('/lot-items/{lotItem}/mark-damaged', [LotController::class, 'markDamaged']);
    Route::post('/lots/{lot}/add-items', [LotController::class, 'addItems']);
    Route::post('/lots/{lot}/adjust', [LotController::class, 'adjust']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/royal-express/logins', [RoyalExpressLoginController::class, 'index']);
    Route::post('/royal-express/logins', [RoyalExpressLoginController::class, 'store']);
    Route::delete('/royal-express/logins/{id}', [RoyalExpressLoginController::class, 'destroy']);
    Route::put('/royal-express/logins/{login}/location', [RoyalExpressLoginController::class, 'updateLocation']);

    Route::post('/royal-express/login', [RoyalExpressLoginController::class, 'loginAndSave']);
    Route::get('/royal-express/businesses', [RoyalExpressLoginController::class, 'businesses']);
    Route::post('/royal-express/fetch-cities-states', [RoyalExpressLoginController::class, 'fetchCitiesStates']);

    Route::get('/districts', [RoyalExpressLoginController::class, 'districtsIndex']);
    Route::get('/system-cities', [RoyalExpressLoginController::class, 'systemCities']);

    Route::get('/curfox-states/preview', [RoyalExpressLoginController::class, 'statesPreview']);
    Route::post('/curfox-states/sync-matched', [RoyalExpressLoginController::class, 'statesSyncMatched']);
    Route::post('/curfox-states/manual-match', [RoyalExpressLoginController::class, 'statesManualMatch']);
    Route::post('/curfox-states/create-and-match', [RoyalExpressLoginController::class, 'statesCreateAndMatch']);

    Route::get('/curfox-cities/stats', [RoyalExpressLoginController::class, 'cityStats']);
    Route::get('/curfox-cities/preview', [RoyalExpressLoginController::class, 'cityPreview']);
    Route::post('/curfox-cities/sync-matched', [RoyalExpressLoginController::class, 'citySyncMatched']);
    Route::post('/curfox-cities/manual-match', [RoyalExpressLoginController::class, 'cityManualMatch']);
    Route::post('/curfox-cities/create-and-match', [RoyalExpressLoginController::class, 'cityCreateAndMatch']);
    Route::post('/curfox-cities/auto-create-sync', [RoyalExpressLoginController::class, 'cityAutoCreateSync']);
    Route::get('/curfox-cities/orphans', [RoyalExpressLoginController::class, 'cityOrphans']);
    Route::delete('/curfox-cities/orphans', [RoyalExpressLoginController::class, 'cityDeleteOrphans']);

    Route::get('/admin/orders/draft', [SellerOrderController::class, 'adminDraftOrders']);
    Route::get('/admin/orders/{order}', [SellerOrderController::class, 'adminShow'])->whereNumber('order');
    Route::get('/admin/orders/{order}/delivery-timeline', [SellerOrderController::class, 'adminDeliveryTimeline'])->whereNumber('order');
    Route::get('/admin/orders/filter-options', [SellerOrderController::class, 'adminOrderFilterOptions']);
    Route::post('/admin/orders/{order}/approve', [SellerOrderController::class, 'adminApprove']);
    Route::post('/admin/orders/bulk-approve', [SellerOrderController::class, 'adminBulkApprove']);
    Route::get('/admin/orders/approved', [DispatchNoteController::class, 'adminApprovedOrders']);
    Route::get('/admin/orders/packed', [DispatchNoteController::class, 'adminPackedOrders']);
    Route::get('/admin/orders/shipped', [DispatchNoteController::class, 'adminShippedOrders']);
    Route::get('/admin/orders/completed', [DispatchNoteController::class, 'adminCompletedOrders']);
    Route::get('/admin/orders/cancelled', [DispatchNoteController::class, 'adminCancelledOrders']);
    Route::post('/admin/dispatch-notes/from-approved', [DispatchNoteController::class, 'createFromApproved'])->middleware('permission:Manage Dispatch Management');
    Route::get('/admin/dispatch-notes', [DispatchNoteController::class, 'adminDispatchNotes'])->middleware('permission:Manage Dispatch Management');
    Route::post('/admin/shipping/scan-waybill', [DispatchNoteController::class, 'scanWaybillForShipping'])->middleware('permission:Manage Dispatch Management');
    Route::get('/admin/dispatch-notes/{dispatchNote}', [DispatchNoteController::class, 'adminDispatchNoteShow'])->whereNumber('dispatchNote')->middleware('permission:Manage Dispatch Management');
    Route::post('/admin/dispatch-notes/{dispatchNote}/validate-lot-barcode', [DispatchNoteController::class, 'validateLotBarcode'])->whereNumber('dispatchNote')->middleware('permission:Manage Dispatch Management');
    Route::post('/admin/dispatch-notes/{dispatchNote}/link-lot-items', [DispatchNoteController::class, 'linkLotItems'])->whereNumber('dispatchNote')->middleware('permission:Manage Dispatch Management');
    Route::post('/admin/dispatch-notes/{dispatchNote}/ship', [DispatchNoteController::class, 'ship'])->whereNumber('dispatchNote')->middleware('permission:Manage Dispatch Management');
    Route::get('/admin/dispatch-notes/seller-options', [DispatchNoteController::class, 'sellerOptions'])->middleware('permission:Manage Dispatch Management');
});

Route::middleware(['auth:sanctum', 'role:Seller'])->group(function () {
    Route::get('/seller/cities', [SellerOrderController::class, 'cities']);
    Route::get('/seller/orders', [SellerOrderController::class, 'index']);
    Route::post('/seller/orders', [SellerOrderController::class, 'store']);
});
