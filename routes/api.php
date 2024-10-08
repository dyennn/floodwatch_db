<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SensorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

// Routes for managing posts
Route::apiResource('posts', PostController::class); // Provides RESTful API routes for posts

// Authentication routes
Route::post('/register', [AuthController::class, 'register']); // User registration
Route::post('/login', [AuthController::class, 'login']); // User login
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // User logout

// Sensor data routes
Route::post('/post-water-level', [SensorController::class, 'post_water_level']); // Post water level data
Route::get('/get-all-sensors', [SensorController::class, 'get_all_sensors']); // Get all sensor data

// Profile routes
Route::middleware('auth:sanctum')->group(function () {
  Route::put('/profile', [AuthController::class, 'update']); // Update user profile
  Route::delete('/profile', [AuthController::class, 'delete']); // Delete user profile
  Route::get('/profile', [AuthController::class, 'show']); // Show user profile
});

// Email verification routes
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/email/verify', function () {
    return response()->json(['message' => 'Please verify your email address.']);
  })->name('verification.notice');

  Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.']);
  })->middleware('signed')->name('verification.verify');

  Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
  })->middleware('throttle:6,1')->name('verification.send');
});

// Password reset routes
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/forgot-password', function (Request $request) {
    return response()->json(['message' => 'Password reset link sent to your email.']);
  })->name('password.request');

  Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink($request->only('email'));
    return $status === Password::RESET_LINK_SENT
      ? response()->json(['message' => 'Password reset link sent to your email.'])
      : response()->json(['message' => 'Unable to send password reset link.'], 500);
  })->name('password.email');

  Route::get('/reset-password/{token}', function (string $token) {
    return response()->json(['message' => 'Password reset token is valid.']);
  })->name('password.reset');

  Route::post('/reset-password', function (Request $request) {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill(['password' => Hash::make($password)])->save();
      }
    );

    return $status === Password::PASSWORD_RESET
      ? response()->json(['message' => 'Password reset successfully.'])
      : response()->json(['message' => 'Unable to reset password.'], 500);
  });
});
