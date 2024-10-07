<?php

namespace App\Http\Controllers;

use App\Models\Sensor; // Ensure that the Sensor model exists in the App\Models namespace
use Illuminate\Routing\Controller; // Import the Controller class
use Illuminate\Http\Request; // Import the Request class

class SensorController extends Controller
{

    public function post_water_level(Request $request)
    {
        
        // Validate the incoming request data
        $validatedData = $request->validate([
            'water_level' => 'required|numeric',
            'street_name' => 'required|string',
        ]);
    
        // Create a new WaterLevel instance and save it to the database
        Sensor::create([
            'water_level' => $validatedData['water_level'],
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'street_name' => $validatedData['street_name']
        ]);

        // Return a success response
        return response()->json(['message' => 'Water level data received and stored successfully']);


    }


    public function get_all_sensors()
    {
        // Fetch all sensor data
        $sensors = Sensor::all();

        // Return the sensor data as a JSON response
        return response()->json($sensors);
    }
}