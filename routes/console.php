<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the event status update check to run every 5 minutes for real-time status updates
Schedule::command('events:update-statuses')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule the message processing to run every minute for real-time reminder and event processing
Schedule::command('messages:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
