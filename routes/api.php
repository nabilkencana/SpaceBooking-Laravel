<?php

use App\Http\Controllers\Api\Admin\DiskonAdminController;
use App\Http\Controllers\Api\Admin\MemberController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\ReservasiAdminController;
use App\Http\Controllers\Api\Admin\SpaceAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiskonController;
use App\Http\Controllers\Api\ReservasiController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
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
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => true,
        'statusCode' => 200,
        'message' => 'Berhasil memproses permintaan',
        'data' => [
            'status' => 'ok',
        ],
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ==================== AUTHENTICATION ====================
Route::prefix('auth')->group(function () {
    Route::post('register/member', [AuthController::class, 'registerMember']);
    Route::post('register/admin-space', [AuthController::class, 'registerAdminSpace']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// ==================== SPACES (PUBLIC) ====================
Route::prefix('spaces')->group(function () {
    Route::get('types', [SpaceController::class, 'types']);
    Route::get('availability', [SpaceController::class, 'availability']);
    Route::get('/', [SpaceController::class, 'index']);
    Route::get('{id}', [SpaceController::class, 'show']);
});

// ==================== DISKON (PUBLIC) ====================
Route::prefix('diskon')->group(function () {
    Route::get('active', [DiskonController::class, 'active']);
    Route::post('check', [DiskonController::class, 'check']);
    Route::get('{id}', [DiskonController::class, 'show']);
});

// ==================== RESERVASI (MEMBER) ====================
Route::prefix('reservasi')->group(function () {
    Route::middleware(['auth:sanctum', 'role:member'])->group(function () {
        Route::post('/', [ReservasiController::class, 'store']);
        Route::get('my', [ReservasiController::class, 'my']);
        Route::get('my/history', [ReservasiController::class, 'history']);
        Route::patch('{id}/cancel', [ReservasiController::class, 'cancel']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('{id}/e-ticket', [ReservasiController::class, 'eTicket']);
        Route::get('{id}', [ReservasiController::class, 'show']);
    });
});

// ==================== ADMIN PANEL ====================
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin_space'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::get('members', [MemberController::class, 'index']);
    Route::post('members', [MemberController::class, 'store']);
    Route::get('members/{id}', [MemberController::class, 'show']);
    Route::put('members/{id}', [MemberController::class, 'update']);
    Route::delete('members/{id}', [MemberController::class, 'destroy']);

    Route::get('spaces', [SpaceAdminController::class, 'index']);
    Route::post('spaces', [SpaceAdminController::class, 'store']);
    Route::get('spaces/{id}', [SpaceAdminController::class, 'show']);
    Route::put('spaces/{id}', [SpaceAdminController::class, 'update']);
    Route::delete('spaces/{id}', [SpaceAdminController::class, 'destroy']);

    Route::get('diskon', [DiskonAdminController::class, 'index']);
    Route::post('diskon', [DiskonAdminController::class, 'store']);
    Route::get('diskon/{id}', [DiskonAdminController::class, 'show']);
    Route::put('diskon/{id}', [DiskonAdminController::class, 'update']);
    Route::delete('diskon/{id}', [DiskonAdminController::class, 'destroy']);

    Route::prefix('reservasi')->group(function () {
        Route::get('/', [ReservasiAdminController::class, 'index']);
        Route::patch('{id}/status', [ReservasiAdminController::class, 'updateStatus']);
        Route::post('{id}/check-in', [ReservasiAdminController::class, 'checkIn']);
        Route::post('{id}/check-out', [ReservasiAdminController::class, 'checkOut']);
    });

    Route::prefix('reports')->group(function () {
        Route::get('monthly', [ReportController::class, 'monthly']);
        Route::get('income', [ReportController::class, 'income']);
    });
});

// ==================== UPLOAD ====================
Route::prefix('upload')->middleware('auth:sanctum')->group(function () {
    Route::post('image', [UploadController::class, 'image']);
    Route::post('members', [UploadController::class, 'members']);
    Route::post('spaces', [UploadController::class, 'spaces'])->middleware('role:admin_space');
});
