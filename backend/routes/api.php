<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InsurancePlanController;
use App\Http\Controllers\Api\MemberPolicyController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MlmController;

/*
|--------------------------------------------------------------------------
| API Routes for KH Holdings Insurance MLM System
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('register-agent', [AuthController::class, 'registerAgent']); // New agent registration
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('resend-verification', [AuthController::class, 'resendVerification']);
    });

    // Public insurance plans (for browsing before registration)
    Route::prefix('plans')->group(function () {
        Route::get('/', [InsurancePlanController::class, 'index']);
        Route::get('/{id}', [InsurancePlanController::class, 'show']);
        Route::get('/{id}/commission-rates', [InsurancePlanController::class, 'getCommissionRates']);
    });

    // Public hospitals and clinics
    Route::prefix('hospitals')->group(function () {
        Route::get('/', [HospitalController::class, 'index']);
        Route::get('/search', [HospitalController::class, 'search']);
        Route::get('/{id}', [HospitalController::class, 'show']);
    });

    Route::prefix('clinics')->group(function () {
        Route::get('/', [ClinicController::class, 'index']);
        Route::get('/search', [ClinicController::class, 'search']);
        Route::get('/{id}', [ClinicController::class, 'show']);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['auth:api'])->group(function () {
    
    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Dashboard and overview
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/statistics', [DashboardController::class, 'statistics']);
        Route::get('/recent-activities', [DashboardController::class, 'recentActivities']);
    });

    // User profile management (handled by AuthController for now)
    Route::get('/profile', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/referrals', [MlmController::class, 'getNetwork']);
    Route::get('/downline', [MlmController::class, 'getNetwork']);

    // Insurance policies management
    Route::prefix('policies')->group(function () {
        Route::get('/', [MemberPolicyController::class, 'index']);
        Route::get('/{id}', [MemberPolicyController::class, 'show']);
        Route::post('/purchase', [MemberPolicyController::class, 'purchase']);
        Route::put('/{id}/renew', [MemberPolicyController::class, 'renew']);
        Route::put('/{id}/cancel', [MemberPolicyController::class, 'cancel']);
        Route::get('/{id}/documents', [MemberPolicyController::class, 'getDocuments']);
        Route::post('/{id}/documents', [MemberPolicyController::class, 'uploadDocuments']);
    });

    // Payment management
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/create', [PaymentController::class, 'createPayment']);
        Route::post('/create-bulk', [PaymentController::class, 'createBulkPayment']);
        Route::post('/verify', [PaymentController::class, 'verifyPayment']);
        Route::get('/receipts/{id}', [PaymentController::class, 'getReceipt']);
        Route::post('/test-complete/{id}', [PaymentController::class, 'testCompletePayment']); // Testing only
    });

    // Wallet and commission management
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::get('/balance', [WalletController::class, 'getBalance']);
        Route::get('/transactions', [WalletController::class, 'getTransactions']);
        Route::post('/withdraw', [WalletController::class, 'createWithdrawalRequest']);
        Route::get('/withdrawals', [WalletController::class, 'getWithdrawalRequests']);
        Route::get('/withdrawals/{id}', [WalletController::class, 'getWithdrawalRequest']);
    });

    // MLM system routes
    Route::prefix('mlm')->group(function () {
        Route::get('/network', [MlmController::class, 'getNetwork']);
        Route::get('/commission-history', [MlmController::class, 'getCommissionHistory']);
        Route::get('/level-summary', [MlmController::class, 'getLevelSummary']);
        Route::post('/register-client', [MlmController::class, 'registerClient']);
        Route::post('/register-bulk-clients', [MlmController::class, 'registerBulkClients']);
        Route::get('/team-performance', [MlmController::class, 'getTeamPerformance']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'delete']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
    });
});

// Public payment callback (no auth required)
Route::post('/v1/payments/callback', [PaymentController::class, 'handleCallback'])->name('api.payments.callback');

// Admin routes (for future admin panel - will use web.php for blade views)
Route::prefix('v1/admin')->middleware(['auth:admin'])->group(function () {
    // Admin routes will be implemented later for admin panel
    // This is kept for API consistency but admin will primarily use web routes
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'status' => 'error'
    ], 404);
});
