<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RolePermissionController;

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

Route::middleware(['auth:sanctum', 'permission:Manage Inventory'])->group(function () {
    Route::get('/categories/tree', [CategoryController::class, 'tree']);
    Route::get('/categories/options', [CategoryController::class, 'options']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});
