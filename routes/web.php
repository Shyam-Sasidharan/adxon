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
    Route::get('/leads', [AdminController::class, 'leads'])->name('admin.leads');
    Route::post('/leads', [AdminController::class, 'saveLead'])->name('admin.leads.save');
    Route::patch('/leads/{lead}/stage', [AdminController::class, 'updateLeadStage'])->name('admin.leads.stage');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('admin.invoices');
    Route::post('/invoices', [AdminController::class, 'saveInvoice'])->name('admin.invoices.save');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'saveUser'])->name('admin.users.save');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])->name('admin.reports.export');
    Route::get('/search', [AdminController::class, 'search'])->name('admin.search');
    Route::get('/enquiries', [AdminController::class, 'enquiries'])->name('admin.enquiries');
    Route::delete('/enquiries/{index}', [AdminController::class, 'deleteEnquiry'])->name('admin.enquiries.delete');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::get('/{collection}', [AdminController::class, 'collection'])->name('admin.collection');
    Route::post('/{collection}', [AdminController::class, 'saveCollection'])->name('admin.collection.save');
});
