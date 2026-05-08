<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\WorkoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);

        Route::get('/devices', [DeviceController::class, 'index']);
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy']);

        Route::get('/dashboard', [DashboardController::class, 'show']);
        Route::get('/membership', [MembershipController::class, 'show']);
        Route::get('/workouts', [WorkoutController::class, 'index']);
        Route::post('/workouts', [WorkoutController::class, 'store']);
        Route::get('/workouts/{workout}', [WorkoutController::class, 'show']);
        Route::get('/schedule', [ScheduleController::class, 'index']);
        Route::post('/schedule', [ScheduleController::class, 'store']);
        Route::put('/schedule/{schedule}', [ScheduleController::class, 'update']);
        Route::post('/schedule/{schedule}/cancel', [ScheduleController::class, 'cancel']);
        Route::post('/schedule/{schedule}/complete', [ScheduleController::class, 'complete']);
        Route::get('/progress', [ProgressController::class, 'index']);
        Route::post('/progress/check-ins', [ProgressController::class, 'storeCheckIn']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/onboarding', [ProfileController::class, 'completeOnboarding']);
        Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar']);
        Route::get('/profile/notification-settings', [ProfileController::class, 'notificationSettings']);
        Route::put('/profile/notification-settings', [ProfileController::class, 'updateNotificationSettings']);
    });
});
