<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\HealthcareController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TacController;
use App\Http\Controllers\Api\MedicalInsuranceController;
use App\Http\Controllers\Api\MedicalInsurancePaymentController;
use App\Http\Controllers\Api\ClientsController;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $db = DB::getDatabaseName();
        return response()->json([
            'success' => true,
            'status' => 'ok',
            'database' => $db,
            'time' => now()->toISOString(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'status' => 'degraded',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-agent-code', [AuthController::class, 'verifyAgentCode']);
});

/*
|--------------------------------------------------------------------------
| TAC Verification Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('tac')->group(function () {
    Route::post('/send', [TacController::class, 'sendTac']);
    Route::post('/verify', [TacController::class, 'verifyTac']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth related routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Profile management routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/change-password', [ProfileController::class, 'changePassword']);
        Route::put('/change-phone', [ProfileController::class, 'changePhone']);
        Route::put('/bank-info', [ProfileController::class, 'updateBankInfo']);
    });

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities']);
    });

    // Member management routes
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);
        Route::get('/{member}', [MemberController::class, 'show']);
        Route::put('/{member}', [MemberController::class, 'update']);
        Route::delete('/{member}', [MemberController::class, 'destroy']);
        Route::get('/{member}/policies', [MemberController::class, 'getPolicies']);
        Route::post('/{member}/policies', [MemberController::class, 'createPolicy']);
    });

    // Commission routes
    Route::prefix('commissions')->group(function () {
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/my-commissions', [CommissionController::class, 'myCommissions']);
        Route::get('/calculate', [CommissionController::class, 'calculate']);
        Route::get('/summary', [CommissionController::class, 'getSummary']);
        Route::get('/history', [CommissionController::class, 'getHistory']);
    });

    // Referral routes
    Route::prefix('referrals')->group(function () {
        Route::get('/', [CommissionController::class, 'getReferrals']);
        Route::get('/tree', [CommissionController::class, 'getReferralTree']);
        Route::get('/downlines', [CommissionController::class, 'getDownlines']);
        Route::get('/uplines', [CommissionController::class, 'getUplines']);
    });

    // Insurance products routes
    Route::prefix('products')->group(function () {
        Route::get('/', function () {
            return \App\Models\InsuranceProduct::where('is_active', true)->get();
        });
        Route::get('/{product}', function (\App\Models\InsuranceProduct $product) {
            return $product->load('commissionRules');
        });
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/history', [PaymentController::class, 'getHistory']);
        Route::get('/mandates', [PaymentController::class, 'getMandates']);
        Route::post('/process', [PaymentController::class, 'processPayment']);
        Route::post('/setup-mandate', [PaymentController::class, 'setupMandate']);
        Route::put('/update-mandate', [PaymentController::class, 'updateMandate']);
    });

    // Records/Analytics routes
    Route::prefix('records')->group(function () {
        Route::get('/sharing', [DashboardController::class, 'getSharingRecords']);
        Route::get('/performance', [DashboardController::class, 'getPerformanceData']);
    });

    // Healthcare facilities routes
    Route::prefix('healthcare')->group(function () {
        Route::get('/', [HealthcareController::class, 'index']);
        Route::get('/hospitals', [HealthcareController::class, 'hospitals']);
        Route::get('/clinics', [HealthcareController::class, 'clinics']);
        Route::get('/search', [HealthcareController::class, 'search']);
        Route::get('/{id}', [HealthcareController::class, 'show']);
    });

    // Medical Insurance routes (authenticated)
    Route::prefix('medical-insurance')->group(function () {
        Route::post('/register', [MedicalInsuranceController::class, 'register']);
        Route::get('/registrations', [MedicalInsuranceController::class, 'getRegistrations']);
        Route::get('/registrations/{id}', [MedicalInsuranceController::class, 'getRegistrationStatus']);
        Route::get('/policies', [MedicalInsuranceController::class, 'getUserPolicies']);
        
        // Payment routes
        Route::post('/payment/create-order', [MedicalInsurancePaymentController::class, 'createPaymentOrder']);
        Route::post('/payment/create-order-all', [MedicalInsurancePaymentController::class, 'createPaymentOrderForAllCustomers']);
        Route::match(['POST','GET'], '/payment/verify', [MedicalInsurancePaymentController::class, 'verifyPayment']);
        Route::get('/payment/config', [MedicalInsurancePaymentController::class, 'getPaymentConfig']);
        Route::get('/payment/gateway-history', [MedicalInsurancePaymentController::class, 'getGatewayPayments']);
    });

    // Clients (agent's customers) routes
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientsController::class, 'index']);
        Route::get('/{id}', [ClientsController::class, 'show']);
        Route::get('/{id}/payments', [ClientsController::class, 'payments']);
        Route::get('/{id}/card', [ClientsController::class, 'downloadCard']);
    });
});

// Medical Insurance routes (public - outside auth middleware)
Route::prefix('medical-insurance')->group(function () {
    Route::get('/plans', [MedicalInsuranceController::class, 'getPlans']);
    Route::get('/plans/{id}', [MedicalInsuranceController::class, 'getPlan']);
});
