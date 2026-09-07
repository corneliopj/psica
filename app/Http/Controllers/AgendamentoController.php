<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoRequest;
use App\Contracts\AgendamentoServiceContract;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AgendamentoController extends Controller
{
    public function __construct(
        protected AgendamentoServiceContract $agendamentoService
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
        $pacientes = Paciente::orderBy(Schema::hasColumn('pacientes', 'nome') ? 'nome' : 'name')->get();
        $profissionais = Profissional::query()->orderBy('nome')->get();
        return view('agendamentos.create', compact('pacientes', 'profissionais'));
    }

    public function store(AgendamentoRequest $request): RedirectResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);
        $data = $request->validated();
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $durationMinutes = (int) ($data['duration_minutes'] ?? 60);

        $profissionalId = $this->resolverProfissionalId($request, $data);

        $this->agendamentoService->solicitarAgendamento(
            pacienteId: (int) $data['paciente_id'],
            profissionalId: $profissionalId,
            inicio: $scheduledAt->toDateTimeString(),
            fim: $scheduledAt->copy()->addMinutes($durationMinutes)->toDateTimeString(),
            observacoes: $data['notes'] ?? null,
            valorSessao: isset($data['valor_sessao']) ? (float) $data['valor_sessao'] : null,
        );

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
        $pacientes = Paciente::orderBy(Schema::hasColumn('pacientes', 'nome') ? 'nome' : 'name')->get();
        $profissionais = Profissional::query()->orderBy('nome')->get();
        return view('agendamentos.edit', compact('agendamento', 'pacientes', 'profissionais'));
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
            profissionalId: $this->resolverProfissionalId($request, $data),
            valorSessao: isset($data['valor_sessao']) ? (float) $data['valor_sessao'] : null,
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

        $data = $request->validate([
            'valor_sessao' => ['nullable', 'numeric', 'min:0'],
        ]);

        $agendamento = $this->agendamentoService->confirmarAgendamento(
            $agendamento->id,
            $request->user()->id,
            isset($data['valor_sessao']) ? (float) $data['valor_sessao'] : null,
        );

        return response()->json([
            'agendamento' => [
                'id' => $agendamento->id,
                'status' => $agendamento->status,
                'scheduled_at' => $agendamento->scheduled_at,
                'ends_at' => $agendamento->ends_at,
                'valor_sessao' => $agendamento->valor_sessao,
            ],
        ]);
    }

    public function rejeitar(Request $request, Agendamento $agendamento): JsonResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);

        $data = $request->validate([
            'observacoes_cancelamento' => ['required', 'string', 'min:3'],
        ]);

        $agendamento = $this->agendamentoService->rejeitarAgendamento(
            $agendamento->id,
            $request->user()->id,
            $data['observacoes_cancelamento'],
        );

        return response()->json([
            'agendamento' => [
                'id' => $agendamento->id,
                'status' => $agendamento->status,
            ],
        ]);
    }

    public function cancelar(Request $request, Agendamento $agendamento): JsonResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional', 'paciente']);

        $data = $request->validate([
            'observacoes_cancelamento' => ['required', 'string', 'min:3'],
        ]);

        $agendamento = $this->agendamentoService->cancelarAgendamento(
            $agendamento->id,
            $request->user()->id,
            $data['observacoes_cancelamento'],
        );

        return response()->json([
            'agendamento' => [
                'id' => $agendamento->id,
                'status' => $agendamento->status,
            ],
        ]);
    }

    public function realizar(Request $request, Agendamento $agendamento): JsonResponse
    {
        $this->garantirPerfilPermitido(['admin', 'profissional']);

        $agendamento = $this->agendamentoService->realizarAgendamento($agendamento->id, $request->user()->id);

        return response()->json([
            'agendamento' => [
                'id' => $agendamento->id,
                'status' => $agendamento->status,
                'scheduled_at' => $agendamento->scheduled_at,
                'ends_at' => $agendamento->ends_at,
                'valor_sessao' => $agendamento->valor_sessao,
            ],
        ]);
    }

    protected function resolverProfissionalId(Request $request, array $data): int
    {
        if ($request->user()?->perfil === 'profissional') {
            $profissionalId = Profissional::query()->where('usuario_id', $request->user()->id)->value('id');
            abort_if(! $profissionalId, 422, 'Profissional autenticado não encontrado.');
            return (int) $profissionalId;
        }

        $profissionalId = (int) ($data['profissional_id'] ?? 0);
        abort_if($profissionalId <= 0, 422, 'profissional_id é obrigatório para este perfil.');

        return $profissionalId;
    }
}
