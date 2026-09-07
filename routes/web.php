<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::patch('prontuarios/{prontuario}/diario', [\App\Http\Controllers\ProntuarioController::class, 'registrarDiario'])->name('prontuarios.diario');
    Route::patch('prontuarios/{prontuario}/selar', [\App\Http\Controllers\ProntuarioController::class, 'selar'])->name('prontuarios.selar');
    Route::resource('agendamentos', \App\Http\Controllers\AgendamentoController::class);
    Route::patch('agendamentos/{agendamento}/confirmar', [\App\Http\Controllers\AgendamentoController::class, 'confirmar'])->name('agendamentos.confirmar');
    Route::patch('agendamentos/{agendamento}/rejeitar', [\App\Http\Controllers\AgendamentoController::class, 'rejeitar'])->name('agendamentos.rejeitar');
    Route::patch('agendamentos/{agendamento}/cancelar', [\App\Http\Controllers\AgendamentoController::class, 'cancelar'])->name('agendamentos.cancelar');
    Route::patch('agendamentos/{agendamento}/realizar', [\App\Http\Controllers\AgendamentoController::class, 'realizar'])->name('agendamentos.realizar');
    Route::patch('faturas/{fatura}/pagamento', [\App\Http\Controllers\ReciboController::class, 'registrarPagamento'])->name('faturas.pagamento');
    Route::post('faturas/{fatura}/recibo', [\App\Http\Controllers\ReciboController::class, 'emitir'])->name('faturas.recibo.emitir');
    Route::get('recibos/{recibo}/pdf', [\App\Http\Controllers\ReciboController::class, 'pdf'])->name('recibos.pdf');
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'edit', 'update']);
    // Analyst slot management
    Route::resource('slots', \App\Http\Controllers\SlotController::class)->except(['show']);
});

// Public API for slots (calendar)
Route::get('/api/slots', [\App\Http\Controllers\SlotController::class, 'apiIndex'])->name('api.slots');

require __DIR__.'/auth.php';
