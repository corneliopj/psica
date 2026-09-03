<?php

namespace Tests\Feature;

use App\Models\Usuario;
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
}