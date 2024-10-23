<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        try {
            // Validate the input fields
            $fields = $request->validate([
                'name' => 'nullable|max:25|min:6|string',
                'phone_number' => 'nullable|numeric|digits:10|unique:user_profiles,phone_number,' . $user->profile->id,
                'address' => 'nullable|string|max:255',
                'gender' => 'nullable|string|in:Male,Female,Other',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Validate and update email if present in the request
            if ($request->has('email')) {
                $request->validate([
                    'email' => [
                        'required',
                        'email',
                        'unique:users,email,' . $user->id,
                        'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|aol\.com|phinmaed\.com|example.com)$/'
                    ]
                ]);

                // Update the user's email
                $user->update(['email' => $request->input('email')]);
            }

            // Validate and update password if present in the request
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
                $user->update(['password' => Hash::make($request->input('password'))]);
            }

            // Prepare data for updating the user profile
            $updateData = [];
            if ($request->filled('phone_number')) {
                $updateData['phone_number'] = $fields['phone_number'];
            }
            if ($request->filled('address')) {
                $updateData['address'] = $fields['address'];
            }
            if ($request->filled('gender')) {
                $updateData['gender'] = $fields['gender'];
            }
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $updateData['profile_image'] = $profileImagePath;
            }

            // Update the user profile record if there are any changes
            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                $user->profile->update($updateData);
            }

            // Update the user name if present in the request
            if ($request->filled('name')) {
                $user->update(['name' => $fields['name']]);
            }

            // Return the updated user profile
            return [
                'message' => 'Profile updated successfully.',
                'user' => $user->load('profile'),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->toArray();
            $response = [
                'message' => 'Validation failed',
                'errors' => $errors
            ];

            // Provide hints for specific validation errors
            if (isset($errors['name'])) {
                if (in_array("The name field must be at least 6 characters.", $errors['name'])) {
                    $response['hint'] = 'Username must be 6 characters minimum.';
                }
                if (in_array("The name field must not be greater than 25 characters.", $errors['name'])) {
                    $response['hint'] = 'Username must not have more than 25 characters.';
                }
            }

            if (isset($errors['phone_number'])) {
                if (in_array("The phone number field must be 10 digits.", $errors['phone_number'])) {
                    $response['hint'] = 'Phone number must be 10 digits.';
                }
                if (in_array("The phone number has already been taken.", $errors['phone_number'])) {
                    $response['hint'] = 'Phone number is already registered.';
                }
            }

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
    }

    /**
     * Upload the user's profile image.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadProfileImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();
        $profile = $user->profile;

        // Handle file upload
        if ($request->hasFile('profile_image')) {
            // Delete the old profile image if it exists
            if ($profile && $profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }

            // Store the new image in the public storage (or cloud storage if needed)
            $path = $request->file('profile_image')->store('profile_images', 'public');

            // Save the new image path to the user's profile in the database
            if (!$profile) {
                $profile = new UserProfile();
                $profile->user_id = $user->id;
            }
            $profile->profile_image = $path;
            $profile->save();

            return response()->json(['message' => 'Profile image uploaded successfully!', 'path' => $path], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    /**
     * Get the user's profile information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        // Get the authenticated user and their profile
        $user = Auth::user();
        $profile = $user->profile;
        $image_url = $profile && $profile->profile_image ? asset('storage/' . $profile->profile_image) : null;

        // Return the user's profile information
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'profile_image' => $image_url,  // Send the image URL
        ]);
    }
}
