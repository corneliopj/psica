<?php

namespace Tests\Feature;

use App\Models\Paciente;
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
            'name' => 'Paciente Atualizado',
            'phone' => '(11) 97777-6666',
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
    }
}
