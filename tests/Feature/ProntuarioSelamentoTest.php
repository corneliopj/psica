<?php

namespace Tests\Feature;

use App\Contracts\ProntuarioServiceContract;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProntuarioSelamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_selar_prontuario_impede_alteracoes_posteriores(): void
    {
        $usuarioProfissional = Usuario::factory()->create([
            'perfil' => 'profissional',
            'status' => 'ativo',
        ]);

        $profissional = Profissional::create([
            'usuario_id' => $usuarioProfissional->id,
            'nome' => 'Doutor Selamento',
            'status' => 'ativo',
        ]);

        $paciente = Paciente::create([
            'nome' => 'Paciente Selamento',
            'telefone' => '11911112222',
            'status' => 'ativo',
        ]);

        $agendamento = Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: $paciente->id,
            inicio: Carbon::parse('2026-09-09 14:00:00'),
            duracaoMinutos: 60,
            status: 'confirmado',
            observacoes: 'Sessão selável',
            profissionalId: $profissional->id,
            valorSessao: 200,
        ));

        /** @var ProntuarioServiceContract $service */
        $service = app(ProntuarioServiceContract::class);

        $prontuario = $service->criarProntuario(
            agendamentoId: $agendamento->id,
            pacienteId: $paciente->id,
            profissionalId: $profissional->id,
            anotacoes: 'Anotação inicial',
            historicoClinico: 'Histórico inicial',
        );

        $prontuario = $service->registrarDiarioSessao(
            prontuarioId: $prontuario->id,
            usuarioId: $usuarioProfissional->id,
            anotacoes: 'Diário clínico final',
            historicoClinico: 'Evolução clínica registrada',
        );

        $selado = $service->selarRegistroSessao($prontuario->id, $usuarioProfissional->id);

        $this->assertTrue((bool) $selado->selado);
        $this->assertNotNull($selado->data_selamento);
        $this->assertNotEmpty($selado->hash_integridade);

        $this->expectException(\LogicException::class);
        $service->atualizarProntuario($prontuario->id, $usuarioProfissional->id, [
            'anotacoes' => 'Tentativa de alteração após selamento',
        ]);
    }
}
