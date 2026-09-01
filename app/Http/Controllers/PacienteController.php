<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteRequest;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PacienteController extends Controller
{
    public function index(): View
    {
        $pacientes = Paciente::orderBy('name')->paginate(20);
        return view('pacientes.index', compact('pacientes'));
    }

    public function create(): View
    {
        return view('pacientes.create');
    }

    public function store(PacienteRequest $request): RedirectResponse
    {
        Paciente::create($request->validated());
        return redirect()->route('pacientes.index')->with('success', 'Paciente criado.');
    }

    public function show(Paciente $paciente): View
    {
        return view('pacientes.show', compact('paciente'));
    }

    public function edit(Paciente $paciente): View
    {
        return view('pacientes.edit', compact('paciente'));
    }

    public function update(PacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $paciente->update($request->validated());
        return redirect()->route('pacientes.show', $paciente)->with('success', 'Paciente atualizado.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $paciente->delete();
        return redirect()->route('pacientes.index')->with('success', 'Paciente removido.');
    }
}
