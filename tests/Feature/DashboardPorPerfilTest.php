<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Notificacao;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPorPerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_ve_area_de_gestao_de_usuarios(): void
    {
        $usuario = Usuario::factory()->create([
            'perfil' => 'admin',
        ]);

        $response = $this->actingAs($usuario)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Gerenciar usuários');
    }

    public function test_profissional_ve_agenda_do_doutor(): void
    {
        $usuario = Usuario::factory()->create([
            'perfil' => 'profissional',
        ]);

        $response = $this->actingAs($usuario)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Agenda do doutor');
    }

    public function test_paciente_ve_historico_e_recibos(): void
    {
        $usuario = Usuario::factory()->create([
            'perfil' => 'paciente',
        ]);

        $response = $this->actingAs($usuario)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Histórico de sessões');
        $response->assertSee('Recibos');
    }

    public function test_profissional_ve_solicitacao_pendente_e_notificacao(): void
    {
        $usuarioProfissional = Usuario::factory()->create([
            'perfil' => 'profissional',
            'status' => 'ativo',
        ]);

        $profissional = Profissional::create([
            'usuario_id' => $usuarioProfissional->id,
            'nome' => 'Doutor Painel',
            'status' => 'ativo',
        ]);

        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
            'status' => 'ativo',
        ]);

        $paciente = Paciente::create([
            'usuario_id' => $usuarioPaciente->id,
            'nome' => 'Paciente Notificado',
            'telefone' => '11999999999',
            'status' => 'ativo',
        ]);

        Agendamento::create(Agendamento::makeSchedulingPayload(
            pacienteId: $paciente->id,
            inicio: Carbon::parse('2026-10-10 14:00:00'),
            duracaoMinutos: 60,
            status: 'solicitado',
            observacoes: 'Teste dashboard profissional',
            profissionalId: $profissional->id,
        ));

        Notificacao::create([
            'usuario_id' => $usuarioProfissional->id,
            'tipo' => 'email',
            'canal' => 'agendamento',
            'assunto' => 'Nova solicitação de sessão',
            'mensagem' => 'Paciente Notificado solicitou sessão para 10/10/2026 14:00.',
            'status' => 'pendente',
        ]);

        $response = $this->actingAs($usuarioProfissional)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Solicitações para confirmar');
        $response->assertSee('Paciente Notificado');
        $response->assertSee('Notificações pendentes');
        $response->assertSee('Nova solicitação de sessão');
    }
}