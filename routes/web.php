<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // CRUD routes for psica domain (nomes em Português)
    Route::resource('pacientes', \App\Http\Controllers\PacienteController::class);
    Route::resource('prontuarios', \App\Http\Controllers\ProntuarioController::class);
    Route::resource('agendamentos', \App\Http\Controllers\AgendamentoController::class);
});

require __DIR__.'/auth.php';
