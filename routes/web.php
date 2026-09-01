<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public routes for patient session requests (no auth required)
Route::get('/solicitar', [\App\Http\Controllers\SolicitacaoController::class, 'create'])->name('solicitar.create');
Route::post('/solicitar', [\App\Http\Controllers\SolicitacaoController::class, 'store'])->name('solicitar.store');
// JSON endpoints used by calendar UI
Route::get('/api/agendamentos', [\App\Http\Controllers\SolicitacaoController::class, 'events'])->name('api.agendamentos');
Route::post('/api/solicitar', [\App\Http\Controllers\SolicitacaoController::class, 'apiStore'])->name('api.solicitar');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // CRUD routes for psica domain (nomes em Português)
    Route::resource('pacientes', \App\Http\Controllers\PacienteController::class);
    Route::resource('prontuarios', \App\Http\Controllers\ProntuarioController::class);
    Route::resource('agendamentos', \App\Http\Controllers\AgendamentoController::class);
    // Analyst slot management
    Route::resource('slots', \App\Http\Controllers\SlotController::class)->except(['show']);
});

// Public API for slots (calendar)
Route::get('/api/slots', [\App\Http\Controllers\SlotController::class, 'apiIndex'])->name('api.slots');

require __DIR__.'/auth.php';
