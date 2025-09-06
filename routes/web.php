<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// usuários não autenticados
Route::middleware('guest')->group(function() {

    // login routes
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

    // registration routes
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeUser'])->name('storeUser');

    // new user confirmation
    Route::get('/new_user_confirmation/{token}', [AuthController::class, 'newUserConfirmation'])->name('newUserConfirmation');
});

// usuários logados
Route::middleware('auth')->group(function() {
    Route::get('/', function() {
        echo 'Olá Mundo!';
    })->name('home');

    Route::get('/logout',[AuthController::class, 'logout'])->name('logout');
});