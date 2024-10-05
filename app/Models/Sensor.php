<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $table = 'sensors';
    public $timestamps = false;

    protected $fillable = [
        'water_level',
        'date',
        'time',
        'street_name'
    ];
}