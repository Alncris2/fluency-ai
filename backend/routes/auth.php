<?php

use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\OnboardingProgressController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'store']);
Route::post('/reset-password', [ResetPasswordController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [LogoutController::class, 'destroy']);

    Route::prefix('onboarding')->group(function (): void {
        Route::get('progress', [OnboardingProgressController::class, 'show']);
        Route::put('progress', [OnboardingProgressController::class, 'update']);
        Route::delete('progress', [OnboardingProgressController::class, 'destroy']);
    });
});
