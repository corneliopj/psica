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

    protected function buildPacientePayload(string $nome, ?string $telefone, ?int $usuarioId = null): array
    {
        $payload = [
            $this->pacienteNomeColumn() => $nome,
        ];

        if ($telefone !== null && $telefone !== '') {
            $payload[$this->pacienteTelefoneColumn()] = $telefone;
        }

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
        $usuario = $request->user();
        $pacienteLogado = $usuario?->perfil === 'paciente';
        $usuarioId = $pacienteLogado ? $usuario->id : null;

        $nomeInformado = trim((string) ($data['name'] ?? ''));
        $telefoneInformado = trim((string) ($data['phone'] ?? ''));

        $nome = $nomeInformado !== ''
            ? $nomeInformado
            : trim((string) ($usuario?->nome ?? $usuario?->name ?? 'Paciente'));

        $telefone = $telefoneInformado !== ''
            ? preg_replace('/[^0-9+]/', '', $telefoneInformado)
            : null;

        $payload = $this->buildPacientePayload($nome, $telefone, $usuarioId);

        $usuarioColumn = $this->pacienteUsuarioColumn();
        if ($usuarioColumn !== null && $usuarioId !== null) {
            return Paciente::firstOrCreate([
                $usuarioColumn => $usuarioId,
            ], $payload);
        }

        if ($this->pacienteTelefoneColumn() === 'phone' && $telefone !== null) {
            return Paciente::firstOrCreate([
                'phone' => $telefone,
            ], $payload);
        }

        // Telefone criptografado nao permite busca deterministica por igualdade.
        return Paciente::create($payload);
    }

    protected function usuarioEhPaciente(Request $request): bool
    {
        return $request->user()?->perfil === 'paciente';
    }

    protected function validateSolicitacao(Request $request): array
    {
        $rules = [
            'scheduled_at' => 'required|date',
            'profissional_id' => 'required|integer|exists:profissionais,id',
        ];

        if ($this->usuarioEhPaciente($request)) {
            $rules['name'] = 'nullable|string|max:255';
            $rules['phone'] = 'nullable|string|max:50';
        } else {
            $rules['name'] = 'required|string|max:255';
            $rules['phone'] = 'required|string|max:50';
        }

        return $request->validate($rules);
    }

    protected function slotUsuarioColumn(): string
    {
        return Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';
    }

    public function create()
    {
        return view('solicitar');
    }

    public function store(Request $request)
    {
        $data = $this->validateSolicitacao($request);

        $paciente = $this->resolvePacienteFromRequest($request, $data);

        $scheduled = Carbon::parse($data['scheduled_at']);
        $profissional = Profissional::query()->where('status', 'ativo')->find($data['profissional_id']);

        if (!$profissional instanceof Profissional) {
            return back()->withInput()->withErrors(['profissional_id' => 'Selecione um doutor ativo.']);
        }

        $availableSlot = Slot::where('status', 'free')
            ->where('start', '<=', $scheduled)
            ->where('end', '>=', $scheduled->copy()->addHour())
            ->where($this->slotUsuarioColumn(), $profissional->usuario_id)
            ->orderBy('start')
            ->first();

        if (!$availableSlot instanceof Slot) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Selecione um horário disponível no calendário.']);
        }

        // Check if slot is free (exact timestamp)
        $exists = Agendamento::query()
            ->where('profissional_id', $profissional->id)
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
            profissionalId: $profissional->id,
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
        $data = $this->validateSolicitacao($request);

        $paciente = $this->resolvePacienteFromRequest($request, $data);

        $scheduled = Carbon::parse($data['scheduled_at']);
        $profissional = Profissional::query()->where('status', 'ativo')->find($data['profissional_id']);

        if (!$profissional instanceof Profissional) {
            return response()->json(['error' => 'Selecione um doutor ativo.'], 422);
        }

        $overlap = Agendamento::query()
            ->where('profissional_id', $profissional->id)
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
            ->where($this->slotUsuarioColumn(), $profissional->usuario_id)
            ->orderBy('start')
            ->first();

        if (!$availableSlot instanceof Slot) {
            return response()->json(['error' => 'Selecione um horário disponível no calendário.'], 422);
        }

        $ag = Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: $paciente->id,
            inicio: $scheduled,
            duracaoMinutos: 60,
            status: 'solicitado',
            observacoes: 'Solicitação via calendar',
            profissionalId: $profissional->id,
        ));

        return response()->json(['success' => true, 'event' => $ag]);
    }
}
