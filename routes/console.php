<?php

use App\Models\PingLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monitors:ping')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::call(function () {
    PingLog::where('checked_at', '<', now()->subDays(14))->delete();
})->dailyAt('02:00');