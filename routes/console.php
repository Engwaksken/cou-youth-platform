<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:reconcile-pending --limit=100 --hours=168')
    ->everyFiveMinutes()
    ->withoutOverlapping(4)
    ->onOneServer();

Schedule::command('cou:send-notifications')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->onOneServer();
