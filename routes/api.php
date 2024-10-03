<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SensorController;
use Illuminate\Support\Facades\Route;

// Routes for managing posts
Route::apiResource('posts', PostController::class); // Provides RESTful API routes for posts

// Authentication routes
Route::post('/register', [AuthController::class, 'register']); // User registration
Route::post('/login', [AuthController::class, 'login']); // User login

// Logout route, requires authentication
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // User logout

// Sensor data routes
Route::post('/post-water-level', [SensorController::class, 'postWaterLevel']); // Post water level data
Route::get('/get-all-sensors', [SensorController::class, 'getAllSensors']); // Get all sensor data