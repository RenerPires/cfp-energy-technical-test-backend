<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return response()->json([
        "service"=>"user-management-service",
        "status"=>"ok",
        "version"=>"1.0.0"
    ], 200);
});
