<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $host = request()->getHost();

    if (str_starts_with($host, 'motivations.')) {
        return view('motivations');
    }

    if (str_starts_with($host, 'storage.')) {
        return view('storage-landing');
    }

    return view('welcome');
});

Route::get('/motivations', fn () => view('motivations'));
Route::get('/storage', fn () => view('storage-landing'));
