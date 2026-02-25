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
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\RoyalExpressLoginController;

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
});
