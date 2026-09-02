<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProntuarioRequest;
use App\Models\Prontuario;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProntuarioController extends Controller
{
    public function index(): View
    {
        $prontuarios = Prontuario::with('paciente')->latest()->paginate(20);
        return view('prontuarios.index', compact('prontuarios'));
    }

    public function create(): View
    {
        $pacientes = Paciente::orderBy('name')->get();
        return view('prontuarios.create', compact('pacientes'));
    }

    public function store(ProntuarioRequest $request): RedirectResponse
    {
        Prontuario::create($request->validated());
        return redirect()->route('prontuarios.index')->with('success', 'Prontuário criado.');
    }

    public function show(Prontuario $prontuario): View
    {
        $prontuario->load('paciente');
        return view('prontuarios.show', compact('prontuario'));
    }

    public function edit(Prontuario $prontuario): View
    {
        $pacientes = Paciente::orderBy('name')->get();
        return view('prontuarios.edit', compact('prontuario', 'pacientes'));
    }

    public function update(ProntuarioRequest $request, Prontuario $prontuario): RedirectResponse
    {
        $prontuario->update($request->validated());
        return redirect()->route('prontuarios.show', $prontuario)->with('success', 'Prontuário atualizado.');
    }

    public function destroy(Prontuario $prontuario): RedirectResponse
    {
        $prontuario->delete();
        return redirect()->route('prontuarios.index')->with('success', 'Prontuário removido.');
    }
}
