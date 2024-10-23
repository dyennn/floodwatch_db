<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PasswordReset;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Str;

/**
 * PasswordController handles password-related operations such as sending reset password codes,
 * verifying reset password codes, resetting passwords, and changing passwords.
 */
class PasswordController extends Controller
{
    /**
     * Sends a reset password code to the user's email.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function sendResetPasswordCode(Request $request)
    {
        // Validate the email data
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // If user not found, return a 404 response
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate a random reset password code and hash it
        $resetPasswordCode = random_int(100000, 999999);
        $token = Hash::make($resetPasswordCode);

        // Create a password reset record
        PasswordReset::create([
            'email' => $user->email,
            'token' => $token,
            'reset_password_code' => $resetPasswordCode,
            'reset_password_code_expires_at' => Carbon::now()->addMinutes(60),
        ]);

        // Send the reset password code to the user's email
        Mail::to($user->email)->send(new ResetPasswordMail($resetPasswordCode));

        // Return a success response
        return [
            'message' => 'Reset password code sent to your email.',
            'email' => $user->email,
        ];
    }

    /**
     * Verifies the reset password code sent to the user's email.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResetPasswordCode(Request $request)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email',
            'reset_password_code' => 'required|integer',
        ]);

        // Find the password reset record by email
        $reset = PasswordReset::where('email', $request->email)->first();

        // Check if the reset password code is valid
        if (!$reset || $reset->reset_password_code != $request->reset_password_code) {
            return response()->json(['message' => 'Invalid reset password code.'], 400);
        }

        // Check if the reset password code has expired
        if (Carbon::now()->greaterThan($reset->reset_password_code_expires_at)) {
            return response()->json(['message' => 'Reset password code has expired.'], 400);
        }

        // Generate a new token and save it
        $token = Str::random(60);
        $hashedToken = Hash::make($token);
        $reset->token = $hashedToken;
        $reset->reset_password_token_expires_at = Carbon::now()->addMinutes(15);
        $reset->save();

        // Return a success response with the new token
        return response()->json(['message' => 'Reset password code verified.', 'token' => $token]);
    }

    /**
     * Resets the user's password using the provided token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // Validate the request data
        try {
            $request->validate([
                'token' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/'
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Provide hints for password validation errors
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

            return response()->json($response, 422);
        }

        // Find the password reset record by token
        $reset = PasswordReset::where('reset_password_token_expires_at', '>', Carbon::now())
            ->get()
            ->first(function ($reset) use ($request) {
                return Hash::check($request->token, $reset->token);
            });

        // Check if the token is valid
        if (!$reset) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        // Find the user by email and update the password
        $user = User::where('email', $reset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the password reset record
        PasswordReset::where('email', $reset->email)->delete();

        // Return a success response
        return response()->json(['message' => 'Password reset successfully.']);
    }

    /**
     * Changes the user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        // Validate the request data
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/'
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Provide hints for password validation errors
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

            return response()->json($response, 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Return a success response
        return response()->json(['message' => 'Password changed successfully.']);
    }
}
