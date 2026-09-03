<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProntuarioRequest;
use App\Models\Prontuario;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProntuarioController extends Controller
{
    protected function garantirPerfilPermitido(): void
    {
        abort_unless(in_array(request()->user()?->perfil, ['admin', 'profissional'], true), 403);
    }

    public function index(): View
    {
        $this->garantirPerfilPermitido();
        $prontuarios = Prontuario::with('paciente')->latest()->paginate(20);
        return view('prontuarios.index', compact('prontuarios'));
    }

    public function create(): View
    {
        $this->garantirPerfilPermitido();
        $pacientes = Paciente::orderBy('name')->get();
        return view('prontuarios.create', compact('pacientes'));
    }

    public function store(ProntuarioRequest $request): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        Prontuario::create($request->validated());
        return redirect()->route('prontuarios.index')->with('success', 'Prontuário criado.');
    }

    public function show(Prontuario $prontuario): View
    {
        $perfil = request()->user()?->perfil;
        abort_unless(in_array($perfil, ['admin', 'profissional', 'paciente'], true), 403);
        $prontuario->load('paciente');
        return view('prontuarios.show', compact('prontuario'));
    }

    public function edit(Prontuario $prontuario): View
    {
        $this->garantirPerfilPermitido();
        $pacientes = Paciente::orderBy('name')->get();
        return view('prontuarios.edit', compact('prontuario', 'pacientes'));
    }

    public function update(ProntuarioRequest $request, Prontuario $prontuario): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        $prontuario->update($request->validated());
        return redirect()->route('prontuarios.show', $prontuario)->with('success', 'Prontuário atualizado.');
    }

    public function destroy(Prontuario $prontuario): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        $prontuario->delete();
        return redirect()->route('prontuarios.index')->with('success', 'Prontuário removido.');
    }
}
