<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SensorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Routes for managing posts
Route::apiResource('posts', PostController::class); // Provides RESTful API routes for posts

// Authentication routes
Route::post('/register', [AuthController::class, 'register']); // User registration
Route::post('/login', [AuthController::class, 'login']); // User login

// Logout route, requires authentication
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // User logout

// Sensor data routes
Route::post('/post-water-level', [SensorController::class, 'post_water_level']); // Post water level data
Route::get('/get-all-sensors', [SensorController::class, 'get_alls_ensors']); // Get all sensor data

// Profile route
Route::get('/profile', function () {
  // Users may access this route even if they are not verified...
})->middleware('auth');

// Update profile route
Route::put('/profile',[AuthController::class, 'update'])->middleware('auth:sanctum'); // Update user profile

// Delete profile route
Route::delete('/profile',[AuthController::class, 'delete'])->middleware('auth:sanctum'); // Delete user profile

// Show profile route
Route::get('/profile',[AuthController::class, 'show'])->middleware('auth:sanctum'); // Show user profile

// Email verification notice route
Route::get('/email/verify', function () {
  return response()->json(['message' => 'Please verify your email address.']);
})->middleware('auth:sanctum')->name('verification.notice');

// Email verification handler route
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
  $request->fulfill();

  return response()->json(['message' => 'Email verified successfully.']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// Resend email verification link route
Route::post('/email/verification-notification', function (Request $request) {
  $request->user()->sendEmailVerificationNotification();

  return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

