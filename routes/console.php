<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tickets:check-sla')->everyFifteenMinutes();
Schedule::command('tickets:recompute-queue')->everyFiveMinutes();
Schedule::command('tickets:detect-overload')->everyFiveMinutes();
Schedule::command('backup:run')->dailyAt('01:30');
Schedule::command('pinjaman:mark-overdue')->dailyAt('14:00');
