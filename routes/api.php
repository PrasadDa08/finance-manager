<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;



Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login']);
Route::post('/register/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource("/accounts", AccountController::class);
    Route::apiResource("/categories", CategoryController::class);
    Route::apiResource("/transactions", TransactionController::class);
    Route::get("/dashboard", [DashboardController::class, 'index']);
    Route::post('/feedback', [FeedbackController::class, 'store']);
});
