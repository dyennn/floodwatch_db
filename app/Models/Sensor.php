<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $table = 'sensors';
    public $timestamps = false;

    protected $fillable = [
        'street_name',
        'date',
        'time',
        'water_level'
    ];
}