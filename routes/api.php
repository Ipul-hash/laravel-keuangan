<?php


Route::get('/debug-time', function () {
    return [
        'server_time' => now()->toDateTimeString(),
        'today' => now()->toDateString(),
        'timezone' => config('app.timezone'),
    ];
});