<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgendamentoController extends Controller
{
    public function index(): View
    {
        $agendamentos = Agendamento::with('paciente')->orderBy('scheduled_at')->paginate(20);
        return view('agendamentos.index', compact('agendamentos'));
    }

    public function create(): View
    {
        $pacientes = Paciente::orderBy('name')->get();
        return view('agendamentos.create', compact('pacientes'));
    }

    public function store(AgendamentoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['scheduled_at'] = date('Y-m-d H:i:s', strtotime($data['scheduled_at']));
        Agendamento::create($data);
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento criado.');
    }

    public function show(Agendamento $agendamento): View
    {
        $agendamento->load('paciente');
        return view('agendamentos.show', compact('agendamento'));
    }

    public function edit(Agendamento $agendamento): View
    {
        $pacientes = Paciente::orderBy('name')->get();
        return view('agendamentos.edit', compact('agendamento', 'pacientes'));
    }

    public function update(AgendamentoRequest $request, Agendamento $agendamento): RedirectResponse
    {
        $data = $request->validated();
        $data['scheduled_at'] = date('Y-m-d H:i:s', strtotime($data['scheduled_at']));
        $agendamento->update($data);
        return redirect()->route('agendamentos.show', $agendamento)->with('success', 'Agendamento atualizado.');
    }

    public function destroy(Agendamento $agendamento): RedirectResponse
    {
        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento removido.');
    }
}
