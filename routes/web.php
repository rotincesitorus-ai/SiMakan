<?php

use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});

// Route ke halaman SiMakan yang sudah kita buat
Route::get('/simakan', function () {
    return view('simakan');
});

