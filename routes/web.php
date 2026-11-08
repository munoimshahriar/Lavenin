<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/lang/{locale}', LocaleController::class)
    ->whereIn('locale', ['bn', 'en'])
    ->name('locale.switch');
