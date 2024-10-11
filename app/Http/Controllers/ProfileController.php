<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller{

    public function uploadProfileImage(Request $request)
    {
        // Validate the request
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Get the currently authenticated user
        $user = Auth::user();
    
        // Handle file upload
        if ($request->hasFile('profile_image')) {
            // Delete the old profile image if it exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
    
            // Store the new image in the public storage (or cloud storage if needed)
            $path = $request->file('profile_image')->store('profile_images', 'public');
    
            // Save the new image path to the user's profile in the database
            $user->profile_image = $path;
            $user->save();
    
            return response()->json(['message' => 'Profile image uploaded successfully!', 'path' => $path], 200);
        }
    
        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function getProfile(){
        $user = Auth::user();
        $image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'profile_image' => $image_url,  // Send the image URL
        ]);
    }
}
