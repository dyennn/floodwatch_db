<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// AuthController class to handle user authentication
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    { 
        try {
            // Validate the request fields
            $fields = $request->validate([
                'name' => [
                    'required',
                    'max:25',
                    'min:6',
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users',
                    'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|aol\.com|phinmaed\.com|example\.com)$/'
                ],
                'password' => [
                    'required',
                    'confirmed', 
                    'min:8', 
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/' // must contain at least one special character
                ]
            ]);

            // Hash the password
            $fields['password'] = Hash::make($fields['password']);

            // Create new user record
            $user = User::create($fields);

            // Create a new user profile record
            UserProfile::create([
                'user_id' => $user->id,
                'name' => $fields['name']
            ]);

            // Return the user and success message
            return [
                'user' => $user,
                'message' => 'User registered successfully.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Provide hints for name validation errors
            if (isset($errors['name'])) {
                if (in_array("The name field is required.", $errors['name'])) {
                    $response['hint'] = 'Username field is required';
                }
                if (in_array("The name field must be at least 6 characters.", $errors['name'])) {
                    $response['hint'] = 'Username must be 6 characters minimum.';
                }
                if (in_array("The name field must not be greater than 25 characters.", $errors['name'])) {
                    $response['hint'] = 'Username must not have more than 25 characters.';
                }
            }

            // Provide hints for email validation errors
            if (isset($errors['email'])) {
                if (in_array('The email has already been taken.', $errors['email'])) {
                    $response['hint'] = 'The email address is already registered.';
                }
                if (in_array("The email field is required.", $errors['email'])) {
                    $response['hint'] = 'Email is required.';
                }
                if (in_array("The email field format is invalid.", $errors['email'])) {
                    $response['hint'] = 'The email must be a valid email.';
                }
            }

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

            // Return validation error response
            return response()->json($response, 422); 
        }
    }

    /**
     * Login a user.
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    { 
        try { 
            // Validate the request fields
            $request->validate([
                'email' => ['required', 'email', 'exists:users'], 
                'password' => ['required']
            ]);

            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Check if password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'hint' => 'The Password provided is incorrect.'
                ], 422);
            }

            // Create a new token for the user
            $token = $user->createToken($user->name);

            // Format the dates
            $formattedUser = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($user->updated_at)->format('Y-m-d H:i:s'),
            ];

            // Return the user and token
            return [
                'user' => $formattedUser,
                'token' => $token->plainTextToken
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Invalid credentials',
                'errors' => $errors
            ];

            // Provide hint if the email is incorrect or not registered
            if (isset($errors['email'])) {
                $response['hint'] = 'This Email is incorrect or not registered.';
            }

            // Return validation error response
            return response()->json($response, 422); 
        }
    }
    /**
     * Logout the authenticated user.
     *
     * @param Request $request
     * @return array
     */
    public function logout(Request $request)
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Authorization token not found.'
            ], 401);
        }

        // Delete all tokens for the authenticated user
        $request->user()->tokens()->delete();
        
        // Return logout message
        return response()->json([
            'message' => 'You are logged out.'
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return array
     */
    public function profile(Request $request)
    { 
        // Get the authenticated user
        $user = $request->user();

        // Get the user's profile
        $profile = UserProfile::where('user_id', $user->id)->first();

        // Return the user and profile
        return [
            'user' => $user,
            'profile' => $profile
        ];
    }

    /**
     * Show the authenticated user's information.
     *
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Get the user's profile
        $profile = UserProfile::where('user_id', $user->id)->first();

        // Return the user and profile
        return [
            'user' => $user,
            'profile' => $profile
        ];
    }
}