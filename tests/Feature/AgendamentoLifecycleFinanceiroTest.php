<?php

namespace Tests\Feature;

use App\Contracts\AgendamentoServiceContract;
use App\Contracts\FaturaServiceContract;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Recibo;
use App\Models\Slot;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendamentoLifecycleFinanceiroTest extends TestCase
{
    use RefreshDatabase;

    private function criarCenarioBase(): array
    {
        $usuarioProfissional = Usuario::factory()->create([
            'perfil' => 'profissional',
            'status' => 'ativo',
        ]);

        $profissional = Profissional::create([
            'usuario_id' => $usuarioProfissional->id,
            'nome' => 'Doutor Financeiro',
            'status' => 'ativo',
            'cpf_cnpj' => '12345678000190',
        ]);

        $usuarioPaciente = Usuario::factory()->create([
            'perfil' => 'paciente',
            'status' => 'ativo',
        ]);

        $paciente = Paciente::create([
            'usuario_id' => $usuarioPaciente->id,
            'nome' => 'Paciente Financeiro',
            'telefone' => '11999998888',
            'cpf' => '12345678900',
            'nome_responsavel' => 'Responsavel Financeiro',
            'cpf_responsavel' => '99988877766',
            'status' => 'ativo',
        ]);

        Slot::create([
            'start' => '2026-09-08 14:00:00',
            'end' => '2026-09-08 15:00:00',
            'status' => 'free',
            'usuario_id' => $usuarioProfissional->id,
        ]);

        return [$usuarioProfissional, $profissional, $paciente];
    }

    public function test_confirma_realiza_e_gera_fatura_automaticamente(): void
    {
        [$usuarioProfissional, $profissional, $paciente] = $this->criarCenarioBase();

        /** @var AgendamentoServiceContract $service */
        $service = app(AgendamentoServiceContract::class);

        $agendamento = $service->solicitarAgendamento(
            pacienteId: $paciente->id,
            profissionalId: $profissional->id,
            inicio: '2026-09-08 14:00:00',
            fim: '2026-09-08 15:00:00',
            observacoes: 'Solicitado no teste',
            valorSessao: 250.00,
        );

        $confirmado = $service->confirmarAgendamento($agendamento->id, $usuarioProfissional->id, 300.00);

        $this->assertSame('confirmado', $confirmado->status);
        $this->assertSame('300.00', number_format((float) $confirmado->valor_sessao, 2, '.', ''));

        $this->assertDatabaseHas('faturas', [
            'agendamento_id' => $agendamento->id,
            'status' => 'pendente',
            'valor' => '300.00',
        ]);

        $this->assertDatabaseHas('slots', [
            'start' => '2026-09-08 14:00:00',
            'end' => '2026-09-08 15:00:00',
            'status' => 'occupied',
        ]);

        $realizado = $service->realizarAgendamento($agendamento->id, $usuarioProfissional->id);
        $this->assertSame('realizado', $realizado->status);
    }

    public function test_rejeicao_e_cancelamento_exigem_motivo(): void
    {
        [$usuarioProfissional, $profissional, $paciente] = $this->criarCenarioBase();

        /** @var AgendamentoServiceContract $service */
        $service = app(AgendamentoServiceContract::class);

        $agendamento = $service->solicitarAgendamento(
            pacienteId: $paciente->id,
            profissionalId: $profissional->id,
            inicio: '2026-09-08 14:00:00',
            fim: '2026-09-08 15:00:00',
            valorSessao: 200,
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->rejeitarAgendamento($agendamento->id, $usuarioProfissional->id, null);
    }

    public function test_pagamento_e_emissao_de_recibo(): void
    {
        [$usuarioProfissional, $profissional, $paciente] = $this->criarCenarioBase();

        /** @var AgendamentoServiceContract $agendamentoService */
        $agendamentoService = app(AgendamentoServiceContract::class);
        /** @var FaturaServiceContract $faturaService */
        $faturaService = app(FaturaServiceContract::class);

        $agendamento = $agendamentoService->solicitarAgendamento(
            pacienteId: $paciente->id,
            profissionalId: $profissional->id,
            inicio: '2026-09-08 14:00:00',
            fim: '2026-09-08 15:00:00',
            valorSessao: 180,
        );

        $agendamentoService->confirmarAgendamento($agendamento->id, $usuarioProfissional->id);

        $faturaId = Agendamento::findOrFail($agendamento->id)->fatura?->id;
        $this->assertNotNull($faturaId);

        $faturaService->registrarPagamento((int) $faturaId, 'pix', 'tx-123');

        $response = $this->actingAs($usuarioProfissional)->post(route('faturas.recibo.emitir', ['fatura' => $faturaId]));

        $response->assertOk();
        $this->assertDatabaseHas('recibos', [
            'fatura_id' => $faturaId,
        ]);

        $recibo = Recibo::query()->where('fatura_id', $faturaId)->firstOrFail();
        $this->assertStringStartsWith('REC-2026-', $recibo->numero);
        $this->assertNotEmpty($recibo->hash_autenticidade);
        $this->assertNotEmpty($recibo->texto_legal);
    }
}
