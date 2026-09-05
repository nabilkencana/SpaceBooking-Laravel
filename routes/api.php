<?php

use App\Http\Controllers\Api\Admin\{
    DiskonAdminController,
    MemberController,
    ProfileController,
    ReportController,
    ReservasiAdminController,
    SpaceAdminController
};
use App\Http\Controllers\Api\{
    AuthController,
    DiskonController,
    ReservasiController,
    SpaceController,
    UploadController
};
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'status' => true,
    'statusCode' => 200,
    'message' => 'Berhasil memproses permintaan',
    'data' => [
        'name' => 'Smart Space Booking Backend API',
        'version' => '1.0.0',
        'status' => 'online',
        'description' => 'Backend REST API aplikasi reservasi coworking space (Smart Space Booking) - UKK RPL 2026/2027 Paket B',
        'documentation' => 'Import Postman collection dari folder postman/',
    ],
    'timestamp' => now()->toIso8601String(),
]));

Route::get('health', fn () => response()->json([
    'status' => true,
    'statusCode' => 200,
    'message' => 'Berhasil memproses permintaan',
    'data' => ['status' => 'ok'],
    'timestamp' => now()->toIso8601String(),
]));

Route::any('login', fn () => response()->json([
    'status' => false,
    'statusCode' => 401,
    'message' => 'Unauthenticated atau token tidak valid',
    'error' => 'Unauthorized',
    'timestamp' => now()->toIso8601String(),
], 401))->name('login');

// ==================== AUTHENTICATION ====================
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register/member', 'registerMember');
    Route::post('register/admin-space', 'registerAdminSpace');
    Route::post('login', 'login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', 'profile');
        Route::post('logout', 'logout');
    });
});

// ==================== SPACES (PUBLIC) ====================
Route::prefix('spaces')->controller(SpaceController::class)->group(function () {
    Route::get('types', 'types');
    Route::get('availability', 'availability');
    Route::get('/', 'index');
    Route::get('{id}', 'show');
});

// ==================== DISKON (PUBLIC) ====================
Route::prefix('diskon')->controller(DiskonController::class)->group(function () {
    Route::get('active', 'active');
    Route::post('check', 'check');
    Route::get('{id}', 'show');
});

// ==================== RESERVASI (MEMBER) ====================
Route::prefix('reservasi')->controller(ReservasiController::class)->group(function () {
    Route::middleware(['auth:sanctum', 'role:member'])->group(function () {
        Route::post('/', 'store');
        Route::get('my', 'my');
        Route::get('my/history', 'history');
        Route::patch('{id}/cancel', 'cancel');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('{id}/e-ticket', 'eTicket');
        Route::get('{id}', 'show');
    });
});

// ==================== ADMIN PANEL ====================
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin_space'])->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'show');
        Route::put('profile', 'update');
    });

    Route::apiResource('members', MemberController::class)->parameters(['members' => 'id']);
    Route::apiResource('spaces', SpaceAdminController::class)->parameters(['spaces' => 'id']);
    Route::apiResource('diskon', DiskonAdminController::class)->parameters(['diskon' => 'id']);

    Route::prefix('reservasi')->controller(ReservasiAdminController::class)->group(function () {
        Route::get('/', 'index');
        Route::patch('{id}/status', 'updateStatus');
        Route::post('{id}/check-in', 'checkIn');
        Route::post('{id}/check-out', 'checkOut');
    });

    Route::prefix('reports')->controller(ReportController::class)->group(function () {
        Route::get('monthly', 'monthly');
        Route::get('income', 'income');
    });
});

// ==================== UPLOAD ====================
Route::prefix('upload')->middleware('auth:sanctum')->controller(UploadController::class)->group(function () {
    Route::post('image', 'image');
    Route::post('members', 'members');
    Route::post('spaces', 'spaces')->middleware('role:admin_space');
});
