<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatformController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/enquiry', [HomeController::class, 'storeEnquiry'])->name('enquiry.store');
Route::get('/insights/{kind}/{index}', [HomeController::class, 'detail'])->whereIn('kind', ['portfolio', 'blogs'])->whereNumber('index')->name('site.detail');

Route::prefix('admin')->group(function () {
    Route::get('/account/integrations', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('admin.integrations');
    Route::put('/account/integrations/{type}', [\App\Http\Controllers\IntegrationController::class, 'update'])->name('admin.integrations.update');
    Route::patch('/account/integrations/{type}/status', [\App\Http\Controllers\IntegrationController::class, 'status'])->name('admin.integrations.status');
    Route::delete('/account/integrations/{type}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->name('admin.integrations.destroy');
    Route::get('/account/integrations', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('admin.integrations');
    Route::put('/account/integrations/{type}', [\App\Http\Controllers\IntegrationController::class, 'update'])->name('admin.integrations.update');
    Route::patch('/account/integrations/{type}/status', [\App\Http\Controllers\IntegrationController::class, 'status'])->name('admin.integrations.status');
    Route::delete('/account/integrations/{type}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->name('admin.integrations.destroy');
    foreach (['campaigns', 'clients', 'analytics', 'seo', 'social', 'ads', 'messages', 'preferences'] as $module) {
        Route::get('/workspace/'.$module, [PlatformController::class, 'index'])->defaults('module', $module)->name('admin.platform.'.$module);
    }
    Route::post('/workspace/campaigns', [PlatformController::class, 'saveCampaign'])->name('admin.campaigns.save');
    Route::patch('/workspace/campaigns/{campaign}', [PlatformController::class, 'campaignStatus'])->name('admin.campaigns.status');
    Route::post('/workspace/clients', [PlatformController::class, 'saveClient'])->name('admin.clients.save');
    Route::post('/workspace/metrics/{channel}', [PlatformController::class, 'saveMetrics'])->name('admin.metrics.save');
    Route::post('/workspace/preferences', [PlatformController::class, 'savePreferences'])->name('admin.preferences.save');
    Route::post('/workspace/leads/bulk', [PlatformController::class, 'bulkLeads'])->name('admin.leads.bulk');
    Route::get('/reports/performance', [PlatformController::class, 'reportPage'])->name('admin.reports.performance');
    Route::get('/workspace/invoices/{invoice}/preview', [PlatformController::class, 'invoicePreview'])->name('admin.invoices.preview');
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/login', [AdminController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/leads', [AdminController::class, 'leads'])->name('admin.leads');
    Route::get('/leads/list', [AdminController::class, 'leadList'])->name('admin.leads.list');
    Route::post('/leads', [AdminController::class, 'saveLead'])->name('admin.leads.save');
    Route::patch('/leads/{lead}/stage', [AdminController::class, 'updateLeadStage'])->name('admin.leads.stage');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('admin.invoices');
    Route::get('/invoices/list', [AdminController::class, 'invoiceList'])->name('admin.invoices.list');
    Route::post('/invoices', [AdminController::class, 'saveInvoice'])->name('admin.invoices.save');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminController::class, 'saveUser'])->name('admin.users.save');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.reports.audit');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])->name('admin.reports.export');
    Route::get('/search', [AdminController::class, 'search'])->name('admin.search');
    Route::get('/enquiries', [AdminController::class, 'enquiries'])->name('admin.enquiries');
    Route::delete('/enquiries/{index}', [AdminController::class, 'deleteEnquiry'])->name('admin.enquiries.delete');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::get('/{collection}', [AdminController::class, 'collection'])->name('admin.collection');
    Route::post('/{collection}', [AdminController::class, 'saveCollection'])->name('admin.collection.save');
});
