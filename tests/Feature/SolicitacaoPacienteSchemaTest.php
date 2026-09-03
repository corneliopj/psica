<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Notificacao;
use App\Models\Profissional;
use App\Models\Slot;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitacaoPacienteSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function criarProfissionalAtivo(): Profissional
    {
        $usuarioProfissional = Usuario::factory()->create([
            'perfil' => 'profissional',
            'status' => 'ativo',
        ]);

        return Profissional::create([
            'usuario_id' => $usuarioProfissional->id,
            'nome' => 'Doutor Teste',
            'especialidade' => 'Psicologia',
            'telefone' => '(11) 90000-0000',
            'status' => 'ativo',
        ]);
    }

    public function test_solicitacao_publica_funciona_com_colunas_em_portugues(): void
    {
        $profissional = $this->criarProfissionalAtivo();

        Slot::create([
            'start' => '2026-09-10 14:00:00',
            'end' => '2026-09-10 15:00:00',
            'status' => 'free',
            'usuario_id' => $profissional->usuario_id,
        ]);

        $response = $this->post('/solicitar', [
            'name' => 'Paciente Novo',
            'phone' => '(11) 98888-7777',
            'profissional_id' => $profissional->id,
            'scheduled_at' => '2026-09-10T14:00',
        ]);

        $response->assertOk();
        $response->assertSee('Solicitação recebida');

        $paciente = Paciente::firstOrFail();

        $this->assertSame('Paciente Novo', $paciente->getAttribute('nome'));
        $this->assertNotEmpty($paciente->getAttribute('telefone'));

        $this->assertDatabaseHas('agendamentos', [
            'paciente_id' => $paciente->id,
            'profissional_id' => $profissional->id,
            'status' => 'solicitado',
        ]);

        $this->assertDatabaseHas('notificacoes', [
            'usuario_id' => $profissional->usuario_id,
            'canal' => 'agendamento',
            'tipo' => 'email',
            'status' => 'pendente',
        ]);
    }

    public function test_paciente_logado_reaproveita_registro_vinculado_ao_usuario(): void
    {
        $profissional = $this->criarProfissionalAtivo();

        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
            'status' => 'ativo',
        ]);

        $paciente = Paciente::create([
            'usuario_id' => $usuarioPaciente->id,
            'nome' => $usuarioPaciente->nome,
            'telefone' => '11911112222',
            'status' => 'ativo',
        ]);

        Slot::create([
            'start' => '2026-09-11 14:00:00',
            'end' => '2026-09-11 15:00:00',
            'status' => 'free',
            'usuario_id' => $profissional->usuario_id,
        ]);

        $response = $this->actingAs($usuarioPaciente)->post('/solicitar', [
            'profissional_id' => $profissional->id,
            'scheduled_at' => '2026-09-11T14:00',
        ]);

        $response->assertOk();
        $response->assertSee('Solicitação recebida');

        $this->assertDatabaseCount('pacientes', 1);
        $this->assertDatabaseHas('agendamentos', [
            'paciente_id' => $paciente->id,
            'profissional_id' => $profissional->id,
            'status' => 'solicitado',
        ]);

        $this->assertDatabaseHas('notificacoes', [
            'usuario_id' => $profissional->usuario_id,
            'canal' => 'agendamento',
            'status' => 'pendente',
        ]);
    }

    public function test_solicitacao_retorna_erro_quando_horario_nao_pertence_ao_doutor_escolhido(): void
    {
        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
            'status' => 'ativo',
        ]);

        $profissional = $this->criarProfissionalAtivo();

        Slot::create([
            'start' => '2026-09-12 14:00:00',
            'end' => '2026-09-12 15:00:00',
            'status' => 'free',
            'usuario_id' => null,
        ]);

        $response = $this->actingAs($usuarioPaciente)
            ->from('/dashboard')
            ->post('/solicitar', [
                'profissional_id' => $profissional->id,
                'scheduled_at' => '2026-09-12T14:00',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors(['scheduled_at']);
        $this->assertDatabaseCount('agendamentos', 0);
    }

    public function test_dois_doutores_podem_ter_mesmo_horario_e_cada_um_recebe_sua_solicitacao(): void
    {
        $profissionalA = $this->criarProfissionalAtivo();

        $usuarioProfissionalB = Usuario::factory()->create([
            'perfil' => 'profissional',
            'status' => 'ativo',
        ]);

        $profissionalB = Profissional::create([
            'usuario_id' => $usuarioProfissionalB->id,
            'nome' => 'Doutora B',
            'especialidade' => 'TCC',
            'telefone' => '(11) 90000-1000',
            'status' => 'ativo',
        ]);

        Slot::create([
            'start' => '2026-09-13 14:00:00',
            'end' => '2026-09-13 15:00:00',
            'status' => 'free',
            'usuario_id' => $profissionalA->usuario_id,
        ]);

        Slot::create([
            'start' => '2026-09-13 14:00:00',
            'end' => '2026-09-13 15:00:00',
            'status' => 'free',
            'usuario_id' => $profissionalB->usuario_id,
        ]);

        $responseA = $this->post('/solicitar', [
            'name' => 'Paciente A',
            'phone' => '(11) 90000-1111',
            'profissional_id' => $profissionalA->id,
            'scheduled_at' => '2026-09-13T14:00',
        ]);

        $responseB = $this->post('/solicitar', [
            'name' => 'Paciente B',
            'phone' => '(11) 90000-2222',
            'profissional_id' => $profissionalB->id,
            'scheduled_at' => '2026-09-13T14:00',
        ]);

        $responseA->assertOk();
        $responseB->assertOk();

        $this->assertDatabaseCount('agendamentos', 2);
        $this->assertDatabaseHas('agendamentos', [
            'profissional_id' => $profissionalA->id,
            'status' => 'solicitado',
        ]);
        $this->assertDatabaseHas('agendamentos', [
            'profissional_id' => $profissionalB->id,
            'status' => 'solicitado',
        ]);

        $this->assertSame(2, Notificacao::query()->where('canal', 'agendamento')->count());
    }
}
