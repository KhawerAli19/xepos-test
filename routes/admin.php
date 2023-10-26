<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Auth\PassResetController;
use App\Http\Controllers\Admin\Company\CompanyController;
use App\Http\Controllers\Admin\Employees\EmployeesController;

Route::name('auth.')->prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
Route::name('password.')->prefix('password')->group(function () {
    Route::post('/verify', [PassResetController::class, 'create'])->name('verify');
    Route::post('password/validate-token/{token}', [PassResetController::class, 'verify_code'])->name('token');
    Route::post('password/change', [PassResetController::class, 'update_password'])->name('update');
});

Route::group(['middleware' => 'auth:admin'], function () {
    Route::get('home', [AdminController::class, 'home'])->name('home');
    Route::post('account/logout', [AccountController::class, 'logout'])->name('account.logout');
    Route::put('account_update', [AccountController::class, 'update'])->name('account.update');

    Route::resources([
        'employees' => EmployeesController::class,
        'companies' => CompanyController::class,
    ]);
});
