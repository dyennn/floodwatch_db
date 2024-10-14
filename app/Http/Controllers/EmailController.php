<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Auth;


class EmailController extends Controller
{
    public function sendVerificationCode(Request $request)
    {
        // Validate the request
        $request->validate([
            'verification_code' => 'required|string',
        ]);


        $user = Auth::user();
        
        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // Generate a 6-digit verification code
        $verificationCode = random_int(100000, 999999);

        // Save the code to the user's record
        $user->verification_code = $verificationCode;
        $user->save();

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($verificationCode));

        return response()->json(['message' => 'Verification code sent.']);
    }

    // Verify Email
    public function verifyEmail(Request $request)
    {
        $user = Auth::user();

        // Check if the verification code is correct
        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return response()->json(['message' => 'Email verified successfully.']);
        } else {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }
    }
}

