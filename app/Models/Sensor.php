<?php

/**
 * Class Sensor
 *
 * This class represents the Sensor model which interacts with the 'sensors' table in the database.
 * It extends the base Eloquent Model provided by Laravel.
 *
 * @package App\Models
 *
 * @property string $street_name The name of the street where the sensor is located.
 * @property string $date The date when the sensor data was recorded.
 * @property string $time The time when the sensor data was recorded.
 * @property float $water_level The water level recorded by the sensor.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Sensor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sensor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sensor query()
 */

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