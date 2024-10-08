<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
 
// Schedule automatic deletion of expired password reset tokens
Schedule::command('auth:clear-resets')->everyFifteenMinutes();

// Schedule a command to run every hour
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
