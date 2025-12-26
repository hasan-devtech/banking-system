<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('interest:apply')
        ->daily() 
        ->runInBackground() 
        ->appendOutputTo(storage_path('logs/interest_calculation.log')); 