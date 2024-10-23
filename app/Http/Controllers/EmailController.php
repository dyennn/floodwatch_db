<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use App\Models\UserVerification;

class EmailController extends Controller
{
    // Sends verification code to the user's email
    public function sendVerificationCode(Request $request)
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Generate a 6-digit verification code
        $verificationCode = random_int(100000, 999999);

        // Save the code to the user's verification record
        $verification = UserVerification::firstOrNew(['user_id' => $user->id]);
        $verification->verification_code = $verificationCode;
        $verification->save();

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($verificationCode));

        return response()->json(['message' => 'Verification code sent.']);
    }

    // Verify Email
    public function verifyEmail(Request $request)
    {
        $user = Auth::user();
        $verification = UserVerification::where('user_id', $user->id)->first();

        // Check if the verification code is correct
        if ($verification && $verification->verification_code == $request->verification_code) {
            $verification->email_verified_at = Carbon::now();
            $verification->save();
            return response()->json(['message' => 'Email verified successfully.']);
        } else {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }
    }

    // Show Verification Status
    public function showVerificationStatus(Request $request)
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        $verification = UserVerification::where('user_id', $user->id)->first();

        if ($verification && $verification->email_verified_at) {
            return response()->json(['message' => 'Email is verified.', 'verified' => true]);
        } else {
            return response()->json(['message' => 'Email is not verified.', 'verified' => false]);
        }
    }
}