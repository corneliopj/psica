<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissoesPorPerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_profissional_pode_criar_slot_e_confirmar_sessao(): void
    {
        $usuarioProfissional = Usuario::factory()->create([
            'perfil' => 'profissional',
        ]);

        $profissional = Profissional::create([
            'usuario_id' => $usuarioProfissional->id,
            'nome' => 'Doutor Teste',
            'status' => 'ativo',
        ]);

        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
        ]);

        $paciente = Paciente::create([
            'usuario_id' => $usuarioPaciente->id,
            'nome' => 'Paciente Teste',
            'status' => 'ativo',
        ]);

        $slotResponse = $this->actingAs($usuarioProfissional)->postJson('/slots', [
            'start' => '2026-09-03 14:00:00',
        ]);

        $slotResponse->assertOk();

        $agendamento = Agendamento::create([
            'profissional_id' => $profissional->id,
            'paciente_id' => $paciente->id,
            'data_hora_inicio' => '2026-09-03 14:00:00',
            'data_hora_fim' => '2026-09-03 15:00:00',
            'status' => 'solicitado',
        ]);

        $confirmacaoResponse = $this->actingAs($usuarioProfissional)
            ->patchJson('/agendamentos/' . $agendamento->id . '/confirmar');

        $confirmacaoResponse->assertOk();
        $confirmacaoResponse->assertJsonPath('agendamento.status', 'confirmado');

        $this->assertDatabaseHas('agendamentos', [
            'id' => $agendamento->id,
            'status' => 'confirmado',
        ]);
    }

    public function test_paciente_nao_pode_acessar_areas_internas(): void
    {
        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
        ]);

        $this->actingAs($usuarioPaciente)->get('/pacientes')->assertForbidden();
        $this->actingAs($usuarioPaciente)->get('/prontuarios')->assertForbidden();
        $this->actingAs($usuarioPaciente)->get('/agendamentos')->assertForbidden();
        $this->actingAs($usuarioPaciente)->get('/usuarios')->assertForbidden();
        $this->actingAs($usuarioPaciente)->postJson('/slots', [
            'start' => '2026-09-03 14:00:00',
        ])->assertForbidden();
    }
}