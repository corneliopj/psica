<?php

namespace Tests\Feature;

use App\Models\Slot;
use App\Models\Profissional;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_usuario_can_create_free_slot(): void
    {
        $usuario = Usuario::factory()->create([
            'perfil' => 'profissional',
        ]);

        $response = $this->actingAs($usuario)->postJson('/slots', [
            'start' => '2026-09-03 14:00:00',
            'end' => '2026-09-03 15:00:00',
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'created');

        $this->assertDatabaseHas('slots', [
            'start' => '2026-09-03 14:00:00',
            'end' => '2026-09-03 15:00:00',
            'status' => 'free',
            'usuario_id' => $usuario->id,
        ]);

        $slot = Slot::firstOrFail();

        $this->assertSame($usuario->id, $slot->usuario_id);
    }

    public function test_public_api_lists_free_slots_for_request_form(): void
    {
        Slot::create([
            'start' => '2026-09-03 14:00:00',
            'end' => '2026-09-03 15:00:00',
            'status' => 'free',
        ]);

        $response = $this->getJson('/api/slots');

        $response->assertOk();
        $response->assertJsonFragment([
            'status' => 'free',
        ]);
    }

    public function test_public_api_can_filter_slots_by_profissional(): void
    {
        $usuarioA = Usuario::factory()->create(['perfil' => 'profissional']);
        $usuarioB = Usuario::factory()->create(['perfil' => 'profissional']);

        $profissionalA = Profissional::create([
            'usuario_id' => $usuarioA->id,
            'nome' => 'Doutor A',
            'status' => 'ativo',
        ]);

        $profissionalB = Profissional::create([
            'usuario_id' => $usuarioB->id,
            'nome' => 'Doutor B',
            'status' => 'ativo',
        ]);

        Slot::create([
            'start' => '2026-09-14 10:00:00',
            'end' => '2026-09-14 11:00:00',
            'status' => 'free',
            'usuario_id' => $usuarioA->id,
        ]);

        Slot::create([
            'start' => '2026-09-14 10:00:00',
            'end' => '2026-09-14 11:00:00',
            'status' => 'free',
            'usuario_id' => $usuarioB->id,
        ]);

        $response = $this->getJson('/api/slots?profissional_id='.$profissionalA->id);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'usuario_id' => $usuarioA->id,
        ]);
        $response->assertJsonMissing([
            'usuario_id' => $usuarioB->id,
        ]);

        $this->assertNotSame($profissionalA->id, $profissionalB->id);
    }
}