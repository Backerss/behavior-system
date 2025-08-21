<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\BackfillUserPrefixes;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ensure our custom command class is loaded
if (class_exists(BackfillUserPrefixes::class)) {
    // no-op: presence ensures auto discovery in Laravel 11 routing-based commands
}
