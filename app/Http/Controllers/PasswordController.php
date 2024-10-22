<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    // Send reset password code
    public function sendResetPasswordCode(Request $request)
    {
        // Validate user input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // If user not found, return error response
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate a 6-digit reset password code
        $resetPasswordCode = random_int(100000, 999999);
        $user->reset_password_code = $resetPasswordCode;
        $user->reset_password_expires_at = Carbon::now()->addMinutes(60);
        $user->save();

        // Send email
        Mail::to($user->email)->send(new ResetPasswordMail($resetPasswordCode));

        // Return success response if reset password code is sent
        return [
            'message' => 'Reset password code sent to your email.',
            'email' => $user->email

        ];
    }

    // Verify reset password code
    public function verifyResetPasswordCode(Request $request)
    {
        // Validate user input
        $request->validate([
            'email' => 'required|email',
            'reset_password_code' => 'required|integer',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // If user not found, return error response
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the reset password code is correct
        if ($user->reset_password_code != $request->reset_password_code) {
            return response()->json(['message' => 'Invalid reset password code.'], 400);
        }

        // Check if the reset password code has expired
        if (Carbon::now()->greaterThan($user->reset_password_expires_at)) {
            return response()->json(['message' => 'Reset password code has expired.'], 400);
        }

        // Generate a token
        $token = Str::random(60);

        // Hash the token before storing it
        $hashedToken = Hash::make($token);

        // Store the hashed token in the database with an expiry time (e.g., 15 minutes)
        $user->reset_password_token = $hashedToken;
        $user->reset_password_token_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        // Return success response with the plain token
        return response()->json(['message' => 'Reset password code verified.', 'token' => $token]);
    }

    // Proceed to Reset password
    public function resetPassword(Request $request)
    {
        // Try method to catch validation errors
        try {
            // Validate user input
            $request->validate([
                'token' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:8',
                    'regex:/[a-z]/', // must contain at least one lowercase letter
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[0-9]/', // must contain at least one digit
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/' // must contain at least one special character
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) { // Catch validation errors

            // Get validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [ // Create response
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Check if password field has errors
            if (isset($errors['password'])) { 
                if (in_array("The password field format is invalid.", $errors['password'])) { // Check if password format is invalid
                    $response['hint'] = 'Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.';
                }
                if (in_array("The password field confirmation does not match.", $errors['password'])) { // Check if password confirmation does not match
                    $response['hint'] = 'Passwords do not match.'; 
                }
                if (in_array("The password field is required.", $errors['password'])) { // Check if password field is provided
                    $response['hint'] = 'Password is required.';
                }
            }
             // 422 is the status code for validation errors
            return response()->json($response, 422);
        }


        // Find user by hashed token and expiry date
        $user = User::where('reset_password_token_expires_at', '>', Carbon::now())
            ->get() 
            ->first(function ($user) use ($request) { 
                return Hash::check($request->token, $user->reset_password_token);
            });

        // If token is invalid or expired, return error response
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        // Update user password and reset password fields
        $user->password = Hash::make($request->password);
        $user->reset_password_token = null;
        $user->reset_password_token_expires_at = null;
        $user->reset_password_code = null;
        $user->reset_password_expires_at = null;
        $user->save();

        // Return success response if password reset successfully
        return response()->json(['message' => 'Password reset successfully.']);
    }
    

    // Change current password
    public function changePassword(Request $request)
    {
        // Try method to catch validation errors
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:8',
                    'regex:/[a-z]/', // must contain at least one lowercase letter
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[0-9]/', // must contain at least one digit
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/' // must contain at least one special character
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Check if password field has errors
            if (isset($errors['password'])) {
                if (in_array("The password field format is invalid.", $errors['password'])) {
                    $response['hint'] = 'Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.';
                }
                if (in_array("The password field confirmation does not match.", $errors['password'])) {
                    $response['hint'] = 'Passwords do not match.';
                }
                if (in_array("The password field is required.", $errors['password'])) {
                    $response['hint'] = 'Password is required.';
                }
            }

            // 422 is the status code for validation errors
            return response()->json($response, 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // Update the user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Return success response
        return response()->json(['message' => 'Password changed successfully.']);
    }
}