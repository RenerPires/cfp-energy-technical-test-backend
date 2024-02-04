<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//Public Routes
Route::group(['prefix' => '/auth'], static function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [UserController::class, 'createUser']);
    Route::post('/forgot-password', [PasswordController::class, 'forgotPassword']);
    Route::post('/reset-password/{token}', [PasswordController::class, 'resetPassword']);
});

// Protected Routes
Route::group(['middleware' => 'auth:api'], static function () {
    Route::group(['prefix' => '/auth'], static function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [PasswordController::class, 'changeUserPassword']);
    });

    Route::group(['prefix' => '/users'], static function () {
        Route::get('/', [UserController::class, 'getUsers']);
        Route::get('/{userId}', [UserController::class, 'getUserById']);
        Route::post('/', [UserController::class, 'createUser']);
        Route::put('/{userId}', [UserController::class, 'updateUser']);
        Route::delete('/{userId}', [UserController::class, 'deleteUser']);
    });
});

Route::get('/', static function () {
    return response()->json([
        "service"=>"user-management-service",
        "status"=>"ok",
        "version"=>"1.0.0"
    ], 200);
});
