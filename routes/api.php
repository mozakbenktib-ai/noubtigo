<?php
use App\Modules\Auth\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Noubtigo API V1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Public Auth Routes
    Route::post('/auth/register', [RegisterController::class, 'register']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('company');
        });

        // Coupons REST API
        Route::get('/coupons', [\App\Modules\Coupons\Controllers\CouponAPIController::class, 'index']);
        Route::post('/coupons/validate', [\App\Modules\Coupons\Controllers\CouponAPIController::class, 'validateCoupon']);
        Route::post('/coupons/apply', [\App\Modules\Coupons\Controllers\CouponAPIController::class, 'applyCoupon']);
        Route::get('/coupons/{code}/history', [\App\Modules\Coupons\Controllers\CouponAPIController::class, 'history']);
        Route::get('/coupons/statistics', [\App\Modules\Coupons\Controllers\CouponAPIController::class, 'statistics']);
    });
});
