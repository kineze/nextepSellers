<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GenaralController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SellerRegistrationController;

Route::controller(GenaralController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/home', 'home')->name('home');
    Route::get('/redirect-dashboard', 'dashboardRedirect')->name('dashboardRedirect');
    Route::get('/setdashboard', 'setDashboard')->name('setDashboard');
    Route::get('/dashboard', 'setDashboard')->name('dashboard');
    Route::get('/blocked', 'blocked')->name('blocked');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacyPolicy');
    Route::get('/seller-registration', 'sellerRegistration')->name('sellerRegistration');
    Route::get('/learn-more', 'learnMore')->name('learnMore');
});

Route::post('/seller-registration', [SellerRegistrationController::class, 'store'])
    ->name('sellerRegistration.store');
Route::post('/seller-registration/email-otp/send', [SellerRegistrationController::class, 'sendEmailOtp'])
    ->name('sellerRegistration.emailOtp.send');
Route::post('/seller-registration/email-otp/verify', [SellerRegistrationController::class, 'verifyEmailOtp'])
    ->name('sellerRegistration.emailOtp.verify');

Route::prefix('admin')->middleware(['auth:sanctum', 'permission:Access Admin Dashboard', config('jetstream.auth_session'), 'verified',])->group(function () {
    
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'getAdminDashboard')->name('adminDashboard');
        Route::get('/orders/draft', 'getAdminDraftOrders')->name('adminDraftOrders');
    });
 
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/seller/dashboard', 'getSellerDashboard')->name('sellerDashboard');
        Route::get('/seller/products', 'getSellerProducts')->name('sellerProducts');
        Route::get('/seller/products/{product}', 'getSellerProductShow')->name('sellerProducts.show');
        Route::get('/seller/products/{product}/data', 'getSellerProductData')->name('sellerProducts.data');
        Route::get('/seller/inventory', 'getSellerInventory')->name('sellerInventory');
        Route::get('/seller/checkout', 'getSellerCheckout')->name('sellerCheckout');
        Route::get('/seller/orders', 'getSellerOrders')->name('sellerOrders');
    });
});


Route::middleware(['permission:Manage Settings', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(UserController::class)->group(function () {
        Route::get('/system-users', 'sysUsers')->name('sysUsers');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles-and-permission', 'roleManagement')->name('roleManagement');
    });

});

Route::middleware(['permission:Manage System Configuration', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::controller(AttributeController::class)->group(function () {
        Route::get('/attributes', 'indexView')->name('attributes');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/royal-express', 'getRoyalExpress')->name('royalExpress');
    });
});

Route::middleware(['permission:Manage Sellers', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(SellerController::class)->group(function () {
        Route::get('/seller-registrations', 'indexView')->name('sellerRegistrations');
        Route::get('/active-sellers', 'activeSellersView')->name('activeSellers');
        Route::get('/active-sellers/{seller}/profile', 'profileView')->name('sellerProfile');
    });

});

Route::middleware(['permission:Manage Levels', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(LevelController::class)->group(function () {
        Route::get('/levels', 'indexView')->name('levels');
    });

});

Route::middleware(['permission:Manage Inventory', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'indexView')->name('products');
        Route::get('/products/create', 'createView')->name('products.create');
        Route::get('/products/{product}/edit', 'editView')->name('products.edit');
    });

    Route::controller(SupplierController::class)->group(function () {
        Route::get('/suppliers', 'indexView')->name('suppliers');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'indexView')->name('categories');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/grns', 'getGrns')->name('grns');
        Route::get('/lots', 'getLots')->name('lots');
    });

});
