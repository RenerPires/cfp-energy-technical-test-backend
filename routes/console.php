<?php

use App\Models\PasswordResetTokens;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('passwords:clear-resets', function () {
    PasswordResetTokens::whereExpiresAt('<=', now())->delete();
    $this->components->info('Expired reset tokens cleared successfully.');
})->purpose('Clear expired password reset tokens');

Artisan::command('users:clear-inactive-users', function () {
    User::whereInactivatedAt('<=', now()->subDays(15))->delete();
    $this->components->info('Expired reset tokens cleared successfully.');
})->purpose('Clear user that are inactive for 15 days');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
