<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => '/auth'], static function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/register', [UserController::class, 'createUser']);
});

Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/users/{userId}', [UserController::class, 'getUserById']);
Route::post('/users', [UserController::class, 'createUser']);
Route::put('/users/{userId}', [UserController::class, 'updateUser']);
Route::delete('/users/{userId}', [UserController::class, 'deleteUser']);

Route::get('/', static function () {
    return response()->json([
        "service"=>"user-management-service",
        "status"=>"ok",
        "version"=>"1.0.0"
    ], 200);
});
