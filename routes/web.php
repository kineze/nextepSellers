<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\LearningContentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GenaralController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\LabelSettingController;
use App\Http\Controllers\SystemDataController;
use App\Http\Controllers\PenaltyTypeController;

Route::controller(GenaralController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/learning-materials', 'learningMaterials')->name('learningMaterials');
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
        Route::get('/orders/bulk-requests', 'getAdminBulkOrderRequests')->name('adminBulkOrderRequests');
        Route::get('/orders/approved', 'getAdminApprovedOrders')->name('adminApprovedOrders');
        Route::get('/orders/packed', 'getAdminPackedOrders')->name('adminPackedOrders');
        Route::get('/orders/shipped', 'getAdminShippedOrders')->name('adminShippedOrders');
        Route::get('/orders/completed', 'getAdminCompletedOrders')->name('adminCompletedOrders');
        Route::get('/orders/cancelled', 'getAdminCancelledOrders')->name('adminCancelledOrders');
        Route::get('/orders/rejected', 'getAdminRejectedOrders')->name('adminRejectedOrders');
        Route::get('/orders/dispatch-notes', 'getAdminDispatchNotes')->middleware('permission:Manage Dispatch Management')->name('adminDispatchNotes');
        Route::get('/orders/dispatch-notes/{dispatchNote}', 'getAdminDispatchNoteShow')->whereNumber('dispatchNote')->middleware('permission:Manage Dispatch Management')->name('adminDispatchNoteShow');
        Route::get('/finance/pending-payments', 'getAdminFinancePendingPayments')->middleware('permission:Manage Finance')->name('adminFinancePendingPayments');
        Route::get('/finance/available-payments', 'getAdminFinanceAvailablePayments')->middleware('permission:Manage Finance')->name('adminFinanceAvailablePayments');
        Route::get('/finance/affiliate-payments', 'getAdminFinanceAffiliatePayments')->middleware('permission:Manage Finance')->name('adminFinanceAffiliatePayments');
        Route::get('/finance/invoices', 'getAdminFinanceInvoices')->middleware('permission:Manage Finance')->name('adminFinanceInvoices');
        Route::get('/finance/payment-manager', 'getAdminFinancePaymentManager')->middleware('permission:Manage Finance')->name('adminFinancePaymentManager');
        Route::get('/reports/order-count', 'getAdminOrderCountReport')->middleware('permission:Access Reports')->name('adminReportsOrderCount');
        Route::get('/reports/seller-breakdown', 'getAdminSellerBreakdownReport')->middleware('permission:Access Reports')->name('adminReportsSellerBreakdown');
        Route::get('/reports/product-breakdown', 'getAdminProductBreakdownReport')->middleware('permission:Access Reports')->name('adminReportsProductBreakdown');
        Route::get('/reports/finance', 'getAdminFinanceReport')->middleware('permission:Access Reports')->name('adminReportsFinance');
        Route::get('/orders/{order}', 'getAdminOrderShow')->name('adminOrderShow');
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
        Route::get('/seller/orders/create', 'getSellerOrderCreate')->name('sellerOrderCreate');
        Route::get('/seller/bulk-orders', 'getSellerBulkOrders')->name('sellerBulkOrders');
        Route::get('/seller/affiliate', 'getSellerAffiliate')->name('sellerAffiliate');
        Route::post('/seller/affiliate/generate', 'postSellerAffiliateGenerate')->name('sellerAffiliateGenerate');
        Route::get('/seller/orders/{order}', 'getSellerOrderShow')->whereNumber('order')->name('sellerOrderShow');
        Route::get('/seller/payments', 'getSellerPayments')->name('sellerPayments');
        Route::get('/seller/profile', 'getSellerProfileManager')->name('sellerProfileManager');
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

    Route::controller(BankController::class)->group(function () {
        Route::get('/banks', 'indexView')->name('banks');
    });

    Route::controller(LabelSettingController::class)->group(function () {
        Route::get('/label-settings', 'indexView')->name('labelSettings');
    });

    Route::controller(PenaltyTypeController::class)->group(function () {
        Route::get('/penalty-types', 'indexView')->name('penaltyTypes');
    });

    Route::controller(SystemDataController::class)->group(function () {
        Route::get('/system-data', 'indexView')->name('systemData');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/royal-express', 'getRoyalExpress')->name('royalExpress');
        Route::get('/delivery-fees', 'getDeliveryFees')->name('deliveryFees');
        Route::get('/manual-delivery-status-fetcher', 'getManualDeliveryStatusFetcher')->name('manualDeliveryStatusFetcher');
    });
});

Route::middleware(['permission:Manage Webhooks and API', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/webhooks/delivery', 'getDeliveryWebhook')->name('deliveryWebhook');
    });
});

Route::middleware(['permission:Manage Learning', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::controller(LearningContentController::class)->group(function () {
        Route::get('/learning/content-manager', 'indexView')->name('learningContentManager');
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
        Route::get('/return-list', 'getReturnList')->name('returnList');
    });

});
