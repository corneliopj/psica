<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuariosSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_schema_uses_usuarios_and_not_users(): void
    {
        $this->assertTrue(Schema::hasTable('usuarios'));
        $this->assertFalse(Schema::hasTable('users'));
    }
}