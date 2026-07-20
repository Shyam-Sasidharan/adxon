<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enquiry', [HomeController::class, 'storeEnquiry'])->name('enquiry.store');

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/login', [AdminController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/enquiries', [AdminController::class, 'enquiries'])->name('admin.enquiries');
    Route::delete('/enquiries/{index}', [AdminController::class, 'deleteEnquiry'])->name('admin.enquiries.delete');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::get('/{collection}', [AdminController::class, 'collection'])->name('admin.collection');
    Route::post('/{collection}', [AdminController::class, 'saveCollection'])->name('admin.collection.save');
});
