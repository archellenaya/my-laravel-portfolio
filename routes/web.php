<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/download-cv', [HomeController::class, 'download_cv']);

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/verify-code', [\App\Http\Controllers\LoginCodeController::class, 'show'])->name('login.code');
        Route::post('/verify-code', [\App\Http\Controllers\LoginCodeController::class, 'verify'])->middleware('throttle:10,1')->name('login.code.verify');
        Route::post('/resend-code', [\App\Http\Controllers\LoginCodeController::class, 'resend'])->middleware('throttle:3,1')->name('login.code.resend');
        Route::post('/cancel-code', [\App\Http\Controllers\LoginCodeController::class, 'cancel'])->name('login.code.cancel');
        Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
        Route::get('/forgot-password', [AdminAuthController::class, 'forgotForm'])->name('password.request');
        Route::post('/forgot-password', [AdminAuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', [AdminAuthController::class, 'resetForm'])->name('password.reset');
        Route::post('/reset-password', [AdminAuthController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
    });
    Route::middleware(['auth', 'can:manage-content', \App\Http\Middleware\RequireEmailCode::class])->group(function () {
        Route::get('/', [CmsController::class, 'index'])->name('admin.index');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/content/{section}', [CmsController::class, 'listing'])->name('admin.list');
        Route::get('/content/{section}/create', [CmsController::class, 'create'])->name('admin.create');
        Route::post('/content/{section}', [CmsController::class, 'save'])->name('admin.store');
        Route::get('/content/{section}/{id}/edit', [CmsController::class, 'edit'])->whereNumber('id')->name('admin.edit');
        Route::put('/content/{section}/{id}', [CmsController::class, 'save'])->whereNumber('id')->name('admin.update');
        Route::delete('/content/{section}/{id}', [CmsController::class, 'destroy'])->whereNumber('id')->name('admin.delete');
    });
});
