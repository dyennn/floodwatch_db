<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $table = 'sensors';
    protected $primaryKey = 'water_level_id';
    public $timestamps = false;

    protected $fillable = [
        'water_level_id',
        'water_level',
        'date_recorded',
        'time_recorded',
        'location'
    ];
}