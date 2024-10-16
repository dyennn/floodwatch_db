<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

// AuthController class to handle user authentication
class AuthController extends Controller
{
    // Register function
    public function register(Request $request){ 
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
                    'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|aol\.com
                    phinmaed.com|example.com)$/'
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

            return [ // Return the user
                'user' => $user,
                'message' => 'User registered successfully.'];
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

    // Login function
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

    // Logout function
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        
        return [
            'message' => 'You are logged out.'
        ];
    }

    // Get authenticated user
    public function profile(Request $request){ 
        return $request->user(); 
    }

    // Update user profile function
    public function update(Request $request){ 
        $user = Auth::user(); // Get the authenticated user

        // Validate name, phone_number, address, 
        $fields = $request->validate([
            'name' => 'nullable|max:25|min:6|string',            
            'phone_number' => 'nullable|numeric|digits:10|unique:users,phone_number,' . $request->user()->id,
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:Male,Female,Other',
        ]);

        // Validate email if present in the request
        if ($request->has('email')) {
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'unique:users,email,' . $user->id,
                    'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|aol\.com|phinmaed\.com|example.com)$/'
                ]
            ]);

            // Update the user email
            $user->update([
                'email' => $request->input('email')
            ]);
        }

        // Validate password if present in the request
        if ($request->has('current_password') && $request->has('password') && $request->has('password_confirmation')) {
            $request->validate([
                'current_password' => 'required|string|min:8',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/', // must contain at least one uppercase letter
                    'regex:/[!@#$%^&*(),.?":{}|<>-_]/' // must contain at least one special character
                ]
            ]);

            // Check if the current password matches
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 400);
            }

            // Update the user password
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);
        }

        /*Update the user record
        $user->update([
            'name' => $fields['name'],
            'phone_number' => $fields['phone_number'],
            'address' => $fields['address'],
            'gender' => $fields['gender'],
            'updated_at' => now()
        ]);*/

        //Update the user record for the provided fields only
        $updateData = [];
        if ($request->filled('name')) {
            $updateData['name'] = $fields['name'];
        }
        if ($request->filled('phone_number')) {
            $updateData['phone_number'] = $fields['phone_number'];
        }
        if ($request->filled('address')) {
            $updateData['address'] = $fields['address'];
        }
        if ($request->filled('gender')) {
            $updateData['gender'] = $fields['gender'];
        }
    
        if (!empty($updateData)) {
            $updateData['updated_at'] = now();
            $user->update($updateData);
        }

        return [ // Return the updated user
            'message' => 'Profile updated successfully.',
            'user' => $user,

        ];
    }

    // Delete user account
    public function delete(Request $request){ 
        $user = $request->user(); // Get the authenticated user
        $user->delete(); // Delete the user record

        return [
            'message' => 'User account deleted successfully.'
        ];
    }

    // Shows user information
    public function show(Request $request){
        $user = $request->user(); // Get the authenticated user

        return $user;
    }
}