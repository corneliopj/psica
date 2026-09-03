<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgendamentoController extends Controller
{
    public function index(): View
    {
        $agendamentos = Agendamento::with('paciente')->orderBy(Agendamento::startColumn())->paginate(20);
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
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $durationMinutes = (int) ($data['duration_minutes'] ?? 60);
        Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: (int) $data['paciente_id'],
            inicio: $scheduledAt,
            duracaoMinutos: $durationMinutes,
            status: $data['status'] ?? 'solicitado',
            observacoes: $data['notes'] ?? null,
        ));
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
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $durationMinutes = (int) ($data['duration_minutes'] ?? 60);
        $agendamento->update(Agendamento::makeSchedulingPayload(
            pacienteId: (int) $data['paciente_id'],
            inicio: $scheduledAt,
            duracaoMinutos: $durationMinutes,
            status: $data['status'] ?? 'solicitado',
            observacoes: $data['notes'] ?? null,
        ));
        return redirect()->route('agendamentos.show', $agendamento)->with('success', 'Agendamento atualizado.');
    }

    public function destroy(Agendamento $agendamento): RedirectResponse
    {
        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento removido.');
    }
}
