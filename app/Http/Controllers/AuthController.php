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
                'name' => [
                    'required',
                    'max:25',
                    'min:6',
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users',
                    'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|aol\.com)$/'
                ],
                'password' => [
                    'required',
                    'confirmed', 
                    'min:8', 
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/' // must contain at least one special character
                ]
            ]);

            $fields['password'] = Hash::make($fields['password']); // Hash the password

            $user = User::create($fields); // Create new user record


            return [ // Return the user and token
                'user' => $user,
            ];
        } catch (\Illuminate\Validation\ValidationException $e) { // Catch validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];
            if (isset($errors['name'])) { // Shows a hint if the name validation fails
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

            if (isset($errors['email'])) { // Shows a hint if the email validation fails
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

            if (isset($errors['password'])) { // Shows a hint if the password validation fails
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
    }

    public function login(Request $request){ // Login function
        try{ 
            $request->validate([
                'email' => ['required', 'email','exists:users'], 
                'password' => ['required']
            ]);

            $user = User::where('email', $request->email)->first(); // Find the user by email

            if (!$user || !Hash::check($request->password, $user->password)){ // Check if password is correct
                return response()->json([
                    'hint' => 'The Password provided is incorrect.'
                ],422);
            }

            $token = $user->createToken($user->name);

            return [
                'user' => $user,
                'token' => $token->plainTextToken
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Invalid credentials',
                'errors' => $errors
            ];
            if (isset($errors['email'])) { // Shows hint if the email is incorrect or not registered
                $response['hint'] = 'This Email is incorrect or not registered.';
            }
            return response()->json($response, 422); 
        }
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        
        return [
            'message' => 'You are logged out.'
        ];
    }
}