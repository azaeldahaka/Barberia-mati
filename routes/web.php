<?php

use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TurnoController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('services', ServiceController::class)->except(['create', 'show', 'edit']);
    Route::resource('turnos', TurnoController::class)->only(['index', 'create', 'store']);
});

use App\Http\Controllers\PublicTurnoController;

// Autoregistro de Cliente (HU-CLI-01)
Route::get('/registro-cliente', [ClientRegistrationController::class, 'create'])->name('client.register');
Route::post('/registro-cliente', [ClientRegistrationController::class, 'store']);

// Reserva Pública (HU-TUR-02)
Route::get('/reservar', [PublicTurnoController::class, 'create'])->name('public.turno.create');
Route::post('/reservar', [PublicTurnoController::class, 'store'])->name('public.turno.store');

require __DIR__.'/auth.php';
