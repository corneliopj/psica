<?php

namespace App\Http\Controllers;

use App\Http\Requests\PacienteRequest;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PacienteController extends Controller
{
    protected function garantirPerfilPermitido(): void
    {
        abort_unless(in_array(request()->user()?->perfil, ['admin', 'profissional'], true), 403);
    }

    public function index(): View
    {
        $this->garantirPerfilPermitido();
        $pacientesTable = (new Paciente())->getTable();
        $nomeColumn = Schema::hasColumn($pacientesTable, 'nome') ? 'nome' : 'name';
        $pacientes = Paciente::orderBy($nomeColumn)->paginate(20);
        return view('pacientes.index', compact('pacientes'));
    }

    public function create(): View
    {
        $this->garantirPerfilPermitido();
        return view('pacientes.create');
    }

    public function store(PacienteRequest $request): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        Paciente::create($request->validated());
        return redirect()->route('pacientes.index')->with('success', 'Paciente criado.');
    }

    public function show(Paciente $paciente): View
    {
        $this->garantirPerfilPermitido();
        return view('pacientes.show', compact('paciente'));
    }

    public function edit(Paciente $paciente): View
    {
        $this->garantirPerfilPermitido();
        return view('pacientes.edit', compact('paciente'));
    }

    public function update(PacienteRequest $request, Paciente $paciente): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        $paciente->update($request->validated());
        return redirect()->route('pacientes.show', $paciente)->with('success', 'Paciente atualizado.');
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        $paciente->delete();
        return redirect()->route('pacientes.index')->with('success', 'Paciente removido.');
    }
}
