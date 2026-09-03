<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Fatura;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Prontuario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user();

        $dados = match ($usuario->perfil) {
            'admin' => $this->dadosAdministrador(),
            'profissional' => $this->dadosProfissional($usuario),
            default => $this->dadosPaciente($usuario),
        };

        return view('dashboard', [
            'usuario' => $usuario,
            'dashboard' => $dados,
        ]);
    }

    protected function dadosAdministrador(): array
    {
        return [
            'perfil' => 'admin',
            'metricas' => [
                'usuarios' => Usuario::count(),
                'pacientes' => Paciente::count(),
                'profissionais' => Profissional::count(),
                'solicitacoes_pendentes' => Agendamento::query()->where('status', 'solicitado')->count(),
            ],
            'usuarios' => Usuario::query()->orderBy('nome')->limit(12)->get(),
        ];
    }

    protected function dadosProfissional(Usuario $usuario): array
    {
        $profissional = Profissional::query()->where('usuario_id', $usuario->id)->first();

        $agendamentos = Agendamento::with('paciente')
            ->when($profissional, fn ($query) => $query->where('profissional_id', $profissional->id))
            ->where(Agendamento::startColumn(), '>=', now()->subDay())
            ->orderBy(Agendamento::startColumn())
            ->limit(10)
            ->get();

        return [
            'perfil' => 'profissional',
            'profissional' => $profissional,
            'agendamentos' => $agendamentos,
            'solicitacoes_pendentes' => $agendamentos->where('status', 'solicitado')->count(),
        ];
    }

    protected function dadosPaciente(Usuario $usuario): array
    {
        $paciente = Paciente::query()->where('usuario_id', $usuario->id)->first();

        $historico = Agendamento::with('paciente')
            ->when($paciente, fn ($query) => $query->where('paciente_id', $paciente->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc(Agendamento::startColumn())
            ->limit(12)
            ->get();

        $prontuarios = Prontuario::query()
            ->when($paciente, fn ($query) => $query->where('paciente_id', $paciente->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->limit(12)
            ->get();

        $faturas = Fatura::query()
            ->when($paciente, fn ($query) => $query->where('paciente_id', $paciente->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('emitida_em')
            ->limit(12)
            ->get();

        return [
            'perfil' => 'paciente',
            'paciente' => $paciente,
            'historico' => $historico,
            'prontuarios' => $prontuarios,
            'faturas' => $faturas,
        ];
    }
}