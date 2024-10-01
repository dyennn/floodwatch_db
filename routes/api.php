<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

Route::apiResource('posts', PostController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/post-water-level', [SensorController::class, 'postWaterLevel']);
Route::get('/get-all-sensors', [SensorController::class, 'getAllSensors']);