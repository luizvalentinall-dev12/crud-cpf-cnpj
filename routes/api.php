<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\CustomSanctumAuth;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/user', [AuthController::class, 'user'])->name('user');

    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');

    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
});
