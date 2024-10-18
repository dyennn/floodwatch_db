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
            'waterLevel' => 'required|numeric',
            'street_name' => 'required|string',
        ]);
    
        // Create a new WaterLevel instance and save it to the database
        Sensor::create([
            'street_name' => $validatedData['street_name'],
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'water_level' => $validatedData['waterLevel']
        ]);

        // Return a success response
        return response()->json(['message' => 'Water level data received and stored successfully']);


    }


    public function get_all_water_level()
    {
        // Fetch all sensor data
        $sensors = Sensor::all();

        // Check if there is any data
        if ($sensors->isEmpty()) {
            // Return a message if no data is found
            return response()->json(['message' => 'No water level data found']);
        }

        // Return the sensor data as a JSON response
        return response()->json($sensors);
    }
}