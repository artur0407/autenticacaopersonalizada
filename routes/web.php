<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// usuários não autenticados
Route::middleware('guest')->group(function() {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
});

// usuários logados
Route::middleware('auth')->group(function(){
    Route::get('/', function() {
        echo 'Olá Mundo!';
    });
});