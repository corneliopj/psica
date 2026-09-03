<?php

namespace Tests\Feature;

use App\Models\Slot;
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
}