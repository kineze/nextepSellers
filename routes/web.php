<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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
