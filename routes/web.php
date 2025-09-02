<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    DB::connection()->getPdo();
    echo 'Home';
});

Route::view('/teste', 'teste')->middleware('auth');

Route::get('/login', function(){
    echo 'form de login';
})->name('login');

Route::middleware('guest')->group(function() {
    Route::get('/register', function(){
        echo 'form register';
    })->name('register');
});

Route::get('/register', function() {
    echo 'form register';
})->name('register')->middleware('guest');
