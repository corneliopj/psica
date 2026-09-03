<?php

namespace Tests\Unit;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AutenticacaoUsuariosConfigTest extends TestCase
{
    public function test_guard_web_usa_provider_usuarios(): void
    {
        $this->assertSame('usuarios', config('auth.defaults.passwords'));
        $this->assertSame('usuarios', config('auth.guards.web.provider'));
        $this->assertSame(Usuario::class, config('auth.providers.usuarios.model'));
        $this->assertSame('usuarios', config('auth.passwords.usuarios.provider'));
    }

    public function test_guard_web_resolve_modelo_usuario(): void
    {
        $provider = Auth::guard('web')->getProvider();

        $this->assertSame(Usuario::class, $provider->getModel());
        $this->assertSame('usuarios', (new Usuario())->getTable());
    }
}