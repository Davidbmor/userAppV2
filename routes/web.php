<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Agregar el middleware EnsureEmailIsVerified a la ruta del perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show')->middleware('verified');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('verified');

    Route::group(['middleware' => ['auth', 'verified']], function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});