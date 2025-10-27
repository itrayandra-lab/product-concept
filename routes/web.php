<?php

use App\Http\Controllers\SimulatorPageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/simulator');

Route::get('/simulator', [SimulatorPageController::class, 'form'])->name('simulator');
Route::get('/simulations/{simulation}/results', [SimulatorPageController::class, 'results'])->name('simulations.results');
Route::get('/simulations/history', [SimulatorPageController::class, 'history'])->name('simulations.history');
Route::get('/docs', [SimulatorPageController::class, 'docs'])->name('docs.index');
Route::get('/ingredients', [SimulatorPageController::class, 'ingredients'])->name('ingredients.index');

Route::view('/login', 'pages.auth.login')->name('login');
Route::view('/register', 'pages.auth.register')->name('register');
Route::view('/forgot-password', 'pages.auth.password-reset')->name('password.request');
