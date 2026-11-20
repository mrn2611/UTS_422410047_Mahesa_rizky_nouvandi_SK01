<?php

use App\Http\Controllers\AccessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/accessions');

// Dasbor statistik (controller invokable: satu aksi __invoke)
Route::get('dashboard', DashboardController::class)->name('dashboard');

// Satu baris ini membuat 7 route: index, create, store, show, edit, update, destroy
Route::resource('accessions', AccessionController::class);
