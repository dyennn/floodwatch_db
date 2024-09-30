<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $table = 'sensors';
    protected $primaryKey = 'sensor_id';
    public $timestamps = false;

    protected $fillable = [
        'sensor_id',
        'water_level',
        'date_recorded',
        'time_recorded',
    ];
}