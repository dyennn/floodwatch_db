<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function register(Request $request){ // Function to register a new user
        try {
            $fields = $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => [
                    'required',
                    'confirmed',
                    'min:8', 
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[!@#$%^&*(),.?":{}|<>]/' // must contain at least one special character
                ]
            ]);

            $fields['password'] = Hash::make($fields['password']); // Hash the password

            $user = User::create($fields); // Create new user record

            $token = $user->createToken($request->name); // Create a token for the user

            return [ // Return the user and token
                'user' => $user,
                'token' => $token->plainTextToken
            ];
        } catch (\Illuminate\Validation\ValidationException $e) { // Catch validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            if (isset($errors['password'])) { // Shows a hint if the password validation fails
                $response['hint'] = 'Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.';
            }

            return response()->json($response, 422); 
        }
    }


    
    public function login(Request $request){ // Login function
        $request->validate([
            'email' => 'required|email|exists:users', 
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first(); // Find the user by email

        if (!$user || !Hash::check($request->password, $user->password)){ 
            return [
                'message' => 'The Provided credentials are incorrect.'
            ];
        }

        $token = $user->createToken($user->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        
        return [
            'message' => 'You are logged out.'
        ];
    }
}
