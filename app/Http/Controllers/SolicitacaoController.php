<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class SolicitacaoController extends Controller
{
    protected function pacientesTable(): string
    {
        return (new Paciente())->getTable();
    }

    protected function resolvePacienteColumn(array $candidates): ?string
    {
        $table = $this->pacientesTable();

        foreach ($candidates as $candidate) {
            if (Schema::hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function pacienteNomeColumn(): string
    {
        return $this->resolvePacienteColumn(['nome', 'name']) ?? 'nome';
    }

    protected function pacienteTelefoneColumn(): string
    {
        return $this->resolvePacienteColumn(['telefone', 'phone']) ?? 'telefone';
    }

    protected function pacienteUsuarioColumn(): ?string
    {
        return $this->resolvePacienteColumn(['usuario_id']);
    }

    protected function pacienteEmailColumn(): ?string
    {
        return $this->resolvePacienteColumn(['email']);
    }

    protected function buildPacientePayload(string $nome, string $telefone, ?int $usuarioId = null): array
    {
        $payload = [
            $this->pacienteNomeColumn() => $nome,
            $this->pacienteTelefoneColumn() => $telefone,
        ];

        $usuarioColumn = $this->pacienteUsuarioColumn();
        if ($usuarioColumn !== null && $usuarioId !== null) {
            $payload[$usuarioColumn] = $usuarioId;
        }

        $emailColumn = $this->pacienteEmailColumn();
        if ($emailColumn !== null) {
            $payload[$emailColumn] = null;
        }

        if (Schema::hasColumn($this->pacientesTable(), 'status')) {
            $payload['status'] = 'ativo';
        }

        return $payload;
    }

    protected function resolvePacienteFromRequest(Request $request, array $data): Paciente
    {
        $nome = trim((string) $data['name']);
        $telefone = preg_replace('/[^0-9+]/', '', (string) $data['phone']);

        $usuario = $request->user();
        $usuarioId = $usuario?->perfil === 'paciente' ? $usuario->id : null;

        $payload = $this->buildPacientePayload($nome, $telefone, $usuarioId);

        $usuarioColumn = $this->pacienteUsuarioColumn();
        if ($usuarioColumn !== null && $usuarioId !== null) {
            return Paciente::firstOrCreate([
                $usuarioColumn => $usuarioId,
            ], $payload);
        }

        if ($this->pacienteTelefoneColumn() === 'phone') {
            return Paciente::firstOrCreate([
                'phone' => $telefone,
            ], $payload);
        }

        // Telefone criptografado nao permite busca deterministica por igualdade.
        return Paciente::create($payload);
    }

    protected function profissionalPadraoId(): ?int
    {
        return Profissional::query()->where('status', 'ativo')->value('id')
            ?? Profissional::query()->value('id');
    }

    protected function slotUsuarioColumn(): string
    {
        return Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';
    }

    protected function profissionalIdParaSlot(Slot $slot): ?int
    {
        $usuarioId = $slot->getAttribute($this->slotUsuarioColumn());

        if ($usuarioId !== null) {
            $profissionalId = Profissional::query()
                ->where('usuario_id', $usuarioId)
                ->where('status', 'ativo')
                ->value('id');

            if ($profissionalId !== null) {
                return (int) $profissionalId;
            }
        }

        return $this->profissionalPadraoId();
    }

    public function create()
    {
        return view('solicitar');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'scheduled_at' => 'required|date',
        ]);

        $paciente = $this->resolvePacienteFromRequest($request, $data);

        $scheduled = Carbon::parse($data['scheduled_at']);

        $availableSlot = Slot::where('status', 'free')
            ->where('start', '<=', $scheduled)
            ->where('end', '>=', $scheduled->copy()->addHour())
            ->orderBy('start')
            ->first();

        if (!$availableSlot instanceof Slot) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Selecione um horário disponível no calendário.']);
        }

        $profissionalId = $this->profissionalIdParaSlot($availableSlot);
        if ($profissionalId === null) {
            return back()->withInput()->withErrors(['scheduled_at' => 'No momento, não há profissional disponível para este horário.']);
        }

        // Check if slot is free (exact timestamp)
        $exists = Agendamento::query()
            ->when(
                Agendamento::usesLegacySchedule(),
                fn ($query) => $query->where(Agendamento::startColumn(), $scheduled->toDateTimeString()),
                fn ($query) => $query
                    ->where(Agendamento::startColumn(), '<', $scheduled->copy()->addHour()->toDateTimeString())
                    ->where(Agendamento::endColumn(), '>', $scheduled->toDateTimeString())
            )
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Horário já reservado. Por favor, escolha outro horário.']);
        }

        // Create agendamento
        $ag = Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: $paciente->id,
            inicio: $scheduled,
            duracaoMinutos: 60,
            status: 'solicitado',
            observacoes: 'Solicitação via formulário público',
            profissionalId: $profissionalId,
        ));

        return view('solicitar_success', ['agendamento' => $ag, 'paciente' => $paciente]);
    }

    // Return JSON events for calendar
    public function events(Request $request)
    {
        $from = $request->query('start');
        $to = $request->query('end');

        $usuario = $request->user();

        $query = Agendamento::with(['paciente', 'profissional'])
            ->whereBetween(Agendamento::startColumn(), [$from ?? now()->subMonth(), $to ?? now()->addMonth()])
            ->when($usuario?->perfil === 'profissional', function ($query) use ($usuario) {
                $profissionalId = Profissional::query()->where('usuario_id', $usuario->id)->value('id');
                return $query->where('profissional_id', $profissionalId ?? 0);
            })
            ->when($usuario?->perfil === 'paciente', function ($query) use ($usuario) {
                $pacienteId = Paciente::query()->where('usuario_id', $usuario->id)->value('id');
                return $query->where('paciente_id', $pacienteId ?? 0);
            })
            ->get();

        $events = $query->map(function($a){
            $status = $a->status;
            return [
                'id' => $a->id,
                'title' => $a->paciente?->name ?? 'Paciente',
                'start' => $a->scheduled_at,
                'end' => $a->ends_at,
                'backgroundColor' => $status === 'confirmado' ? '#2563eb' : '#facc15',
                'borderColor' => $status === 'confirmado' ? '#2563eb' : '#facc15',
                'textColor' => $status === 'confirmado' ? '#ffffff' : '#111827',
                'extendedProps' => [
                    'paciente_id' => $a->paciente_id,
                    'status' => $status,
                    'canConfirm' => $status === 'solicitado',
                ],
            ];
        });

        return response()->json($events);
    }

    // API store for calendar booking (AJAX)
    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $paciente = $this->resolvePacienteFromRequest($request, $data);

        $scheduled = Carbon::parse($data['scheduled_at']);

        $overlap = Agendamento::query()
            ->when(
                Agendamento::usesLegacySchedule(),
                fn ($query) => $query->where(Agendamento::startColumn(), $scheduled->toDateTimeString()),
                fn ($query) => $query
                    ->where(Agendamento::startColumn(), '<', $scheduled->copy()->addHour()->toDateTimeString())
                    ->where(Agendamento::endColumn(), '>', $scheduled->toDateTimeString())
            )
            ->exists();

        if($overlap){
            return response()->json(['error' => 'Horário ocupado'], 422);
        }

        $availableSlot = Slot::where('status', 'free')
            ->where('start', '<=', $scheduled)
            ->where('end', '>=', $scheduled->copy()->addHour())
            ->orderBy('start')
            ->first();

        if (!$availableSlot instanceof Slot) {
            return response()->json(['error' => 'Selecione um horário disponível no calendário.'], 422);
        }

        $profissionalId = $this->profissionalIdParaSlot($availableSlot);
        if ($profissionalId === null) {
            return response()->json(['error' => 'No momento, não há profissional disponível para este horário.'], 422);
        }

        $ag = Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: $paciente->id,
            inicio: $scheduled,
            duracaoMinutos: 60,
            status: 'solicitado',
            observacoes: 'Solicitação via calendar',
            profissionalId: $profissionalId,
        ));

        return response()->json(['success' => true, 'event' => $ag]);
    }
}
