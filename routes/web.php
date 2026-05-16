<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function (): array {
    return [
        'name' => config('app.name'),
        'status' => 'ready',
    ];
});
