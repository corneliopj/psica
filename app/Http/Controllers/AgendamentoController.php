<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Services\AgendamentoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendamentoController extends Controller
{
    public function __construct(
        protected AgendamentoService $agendamentoService
    ) {}

    protected function garantirPerfilPermitido(array $perfisPermitidos): void
    {
        abort_unless(in_array(request()->user()?->perfil, $perfisPermitidos, true), 403);
    }

    public function index(): View
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $agendamentos = Agendamento::with('paciente')->orderBy(Agendamento::startColumn())->paginate(20);
        return view('agendamentos.index', compact('agendamentos'));
    }

    public function create(): View
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $pacientes = Paciente::orderBy('name')->get();
        return view('agendamentos.create', compact('pacientes'));
    }

    public function store(AgendamentoRequest $request): RedirectResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
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
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $agendamento->load('paciente');
        return view('agendamentos.show', compact('agendamento'));
    }

    public function edit(Agendamento $agendamento): View
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $pacientes = Paciente::orderBy('name')->get();
        return view('agendamentos.edit', compact('agendamento', 'pacientes'));
    }

    public function update(AgendamentoRequest $request, Agendamento $agendamento): RedirectResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
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
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento removido.');
    }

    public function confirmar(Request $request, Agendamento $agendamento): JsonResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);

        $agendamento = $this->agendamentoService->confirmarAgendamento($agendamento->id, $request->user()->id);

        return response()->json([
            'agendamento' => [
                'id' => $agendamento->id,
                'status' => $agendamento->status,
                'scheduled_at' => $agendamento->scheduled_at,
                'ends_at' => $agendamento->ends_at,
            ],
        ]);
    }
}
