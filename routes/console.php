<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-app', function () {
    $this->info('Local Laravel + LiveKit meeting MVP');
});
