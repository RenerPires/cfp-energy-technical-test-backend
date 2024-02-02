<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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
