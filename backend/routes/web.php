<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\InsuranceProductController;
use App\Http\Controllers\Admin\HospitalController;
use App\Http\Controllers\Admin\ClinicController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;

// Admin Authentication Routes
Route::get('/admin/login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Members Management
    Route::resource('members', MemberController::class);
    Route::get('members/{member}/policies', [MemberController::class, 'policies'])->name('members.policies');
    Route::get('members/{member}/transactions', [MemberController::class, 'transactions'])->name('members.transactions');
    
    // Commissions Management
    Route::resource('commissions', CommissionController::class);
    Route::get('commissions/calculate', [CommissionController::class, 'showCalculateForm'])->name('commissions.calculate');
    Route::post('commissions/calculate', [CommissionController::class, 'calculateCommissions'])->name('commissions.calculate.post');
    Route::post('commissions/bulk-pay', [CommissionController::class, 'bulkPay'])->name('commissions.bulk-pay');
    
    // Test route for calculate commissions
    Route::get('test-calculate', function() {
        return 'Calculate route is working!';
    })->name('test.calculate');
    
    // Insurance Products Management
    Route::resource('products', InsuranceProductController::class);
    Route::post('products/{product}/toggle-status', [InsuranceProductController::class, 'toggleStatus'])->name('products.toggle-status');
    
    // Hospitals Management
    Route::resource('hospitals', HospitalController::class);
    
    // Clinics Management
    Route::resource('clinics', ClinicController::class);
    
    // Payments Management
    Route::resource('payments', PaymentController::class);
    Route::get('payments/pending', [PaymentController::class, 'pending'])->name('payments.pending');
    Route::post('payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    
    // Reports
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/commissions', [ReportController::class, 'commissions'])->name('reports.commissions');
    Route::get('reports/members', [ReportController::class, 'members'])->name('reports.members');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
});

// Fallback route
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});
